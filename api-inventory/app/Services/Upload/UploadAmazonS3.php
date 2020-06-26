<?php

namespace App\Services\Upload;

use App\Models\Media;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Intervention\Image\ImageManagerStatic as Image;
use Carbon\Carbon;
use Auth;

class UploadAmazonS3
{
    protected $storage;

    protected $client;

    public function __construct()
    {
        $this->storage = Storage::disk('s3');

        $this->client = new S3Client([
            'region'  => env('S3_REGION'),
            'version' => 'latest',
            'credentials' => [
                'key'    => env('S3_KEY'),
                'secret' => env('S3_SECRET'),
            ]
        ]);
    }

    /**
     * Take an instance of Laravel's Request class to get a file, and upload
     * it to Amazon's S3 storage platform. This will check if the file
     * already exists before uploading.
     *
     * @param UploadedFile $file      An instance of Laravel's Request class's file method
     */
    public function upload(UploadedFile $file)
    {
        $prefix = 1;
        $directory = 'cp_'.md5($prefix);
        $basename = md5($prefix . $file->getClientOriginalName() . Carbon::now()->format('Y-m-d H-i-s'));
        $extension = $file->getClientOriginalExtension();
        $filename = $basename.'.'.$extension;
        $fullFilePath = $directory . '/' . $filename;

        $mediaType = $this->determineFileType($file, $prefix);
        $results = [];

        try {
            $upload = $this->client->putObject([
                'Bucket'        => env('S3_BUCKET'),
                'Body'          => file_get_contents($file),
                'Key'           => $fullFilePath,
                'ContentLength' => $file->getSize(),
                // have to instantiate a new file or it fails SHA256 comparison
                'ContentSHA256' => hash('sha256', file_get_contents($file)),
                'ContentType'   => $file->getMimeType(),
            ]);
        } catch (S3Exception $e) {
            $upload = $e->getCode();
            $results = $e->getResponse();
        }
        if (! is_array($upload) and $upload === 0) {
            return $results->getBody();
        }

        if (isset($upload['ObjectURL']) and trim(strlen($upload['ObjectURL'])) > 0) {
            $results['url'] = $upload['ObjectURL'];
        } else {
            $results['url'] = $this->storage->url($fullFilePath);
        }
        $results['type']      = $mediaType;
        $results['filename']  = $filename;
        $results['size']      =  $file->getSize();
        $results['extension'] = $extension;


        // if ($mediaType === 'Image') {
            $original = Image::make($file);

            $results['height']    = $original->height();
            $results['width']     = $original->width();
            $results['size']      = $original->filesize();

            $original->destroy();

            $results = array_merge($results, $this->createImageThumbnails($file, $basename, $extension, $directory));
        // } elseif ($mediaType === 'Video') {
        //     $results = array_merge($results, $this->createVideoPoster($file));
        // } else {
        //     $results = array_merge($results, $this->createDocument($file, $basename, $extension, $directory));
        // }
        return $results;
    }

    public function delete(Media $media)
    {
        $prefix = 1;
        $directory = 'cp_'.md5($prefix);
        $fullFilePath = $directory . '/' . $media->filename;

        $filesToDelete = [$fullFilePath];

        $mainFileName = explode('.', $media->filename);
        $basename = $mainFileName[0];
        $extension = $mainFileName[1];

        $sizes = Media::getImageSizes();
        foreach ($sizes as $size) {
            $filesToDelete[] = $directory . '/' . $basename . '-url' . $size . $extension;
        }

        return $this->storage->delete($filesToDelete);
    }

    /**
     * Determine which category to put the uploaded file in
     *
     * @param UploadedFile $file      An instance of Laravel's Request class's file method
     * @return string|null
     */
    private function determineFileType($file)
    {
        $extension = $file->getClientOriginalExtension();
        $extension = strtolower($extension);
        $imageExt = ['png', 'jpg', 'jpeg', 'gif', 'tiff', 'bmp', 'ico'];
        $videoExt = ['mp4', 'swf', 'flv', 'mkv', '3gp', 'mpg', 'm4v', 'avi', 'wmv'];
        $pdfExt = ['pdf'];
        $documentExt = ['doc', 'docx', 'odt', 'rtf', 'txt', 'epub'];
        $sheetExt = ['xlsx', 'ods', 'csv', 'tsv'];
        $presentationExt = ['pptx', 'odp', 'svg'];

        $type = null;

        if (in_array($extension, $imageExt)) {
            $type = 'Image';
        } elseif (in_array($extension, $videoExt)) {
            $type = 'Video';
        } elseif (in_array($extension, $pdfExt)) {
            $type = 'PDF';
        } elseif (in_array($extension, $documentExt)) {
            $type = 'Document';
        } elseif (in_array($extension, $sheetExt)) {
            $type = 'Spreadsheet';
        } elseif (in_array($extension, $presentationExt)) {
            $type = 'Presentation';
        }

        return $type;
    }

    /**
     * Create the thumbnails of an uploaded image
     *
     * @param UploadedFile $file    The file to create thumbnails from
     * @param string $basename      The base filename to use
     * @param string $extension     The file extension to use for the thumbnails
     * @param string $directory     The upload directory to use
     * @return array
     */
    private function createImageThumbnails($file, $basename, $extension, $directory = '')
    {
        $data = [];

        $sizes = [
            'url_xl'  => Image::make($file)->widen(1200),
            'url_lg'  => Image::make($file)->widen(600),
            'url_md'  => Image::make($file)->widen(400),
            'url_sm'  => Image::make($file)->widen(200),
            'url_xs'  => Image::make($file)->widen(100),
            'url_xxs' => Image::make($file)->widen(50),
        ];

        foreach ($sizes as $key => $image) {
            $image->name = $basename.'-'.$key.'.'.$extension;
            $image->save($image->dirname.'/'.$image->name);

            $fullFilePath = $directory . '/' . $image->name;
            if (file_exists($image->dirname .'/'. $image->name)) {
                $upload = $this->storage->put($fullFilePath, file_get_contents($image->dirname.'/'.$image->name));

                if (isset($upload['ObjectURL']) and trim(strlen($upload['ObjectURL'])) > 0) {
                    $data["$key"] = $upload['ObjectURL'];
                } else {
                    $data["$key"] = $this->storage->url($fullFilePath);
                }

                $image->destroy();
                unlink($image->dirname.'/'.$image->name);
            }
        }

        return $data;
    }

    // /**
    //  * Create an image poster from an uploaded video
    //  *
    //  * @param UploadedFile $file      An instance of Laravel's Request class's file method
    //  * @return array
    //  */
    // private function createVideoPoster($file)
    // {
    //     $data = [];

    //     //

    //     return $data;
    // }

    // private function createDocument($file, $basename, $extension, $directory)
    // {
    //     $data = [];
    //     return $data;
    // }
}
