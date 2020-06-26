<?php

namespace App\Services\Media;

use App\Events\AttachImageUrl;
use App\Models\Media;
use App\Services\Upload\UploadAmazonS3;
use Illuminate\Http\UploadedFile;
use Log;

class MediaService
{
    public function uploadUrl($imageUrl, $userId)
    {
        $filename = uniqid().basename($imageUrl);
        $image = file_get_contents($imageUrl);
        $destinationPath = base_path() . '/storage/temp/'.$filename;
        file_put_contents($destinationPath, $image);
        $uploadedFile = new UploadedFile($destinationPath, $filename);
        $awsS3 = new UploadAmazonS3;
        $file = $awsS3->upload($uploadedFile);
        $file['user_id'] = $userId;
        $media = Media::create($file);
        unlink($destinationPath);
        return $media;
    }

    public function attachImages($model, $images, $userId)
    {
        $model->images()->sync([]);
        foreach ($images as $image) {
            if (isset($image['id'])) {
                $model->images()->attach($image['id']);
            } /* elseif (isset($image['url'])) {
                $media = $this->uploadUrl($image['url'], $userId);
                $model->images()->attach($media->id);
            } */
            // Needed for when S3 credentials are figured out within the api.
            // required in composer.json to completly work:
            // "intervention/image": "2.4.1"
            // "league/flysystem-aws-s3-v3": "1.0.18"
        }
        return $model;
    }
}
