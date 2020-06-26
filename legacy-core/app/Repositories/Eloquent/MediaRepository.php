<?php namespace App\Repositories\Eloquent;

use DB;
use App\Models\Media;
use App\Models\User;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use App\Repositories\Eloquent\AuthRepository;
use App\Services\Upload\UploadAmazonS3;

class MediaRepository
{
    use CommonCrudTrait;

    /**
     * MediaRepository constructor.
     *
     * @param AuthRepository $authRepo
     */
    public function __construct(AuthRepository $authRepo)
    {
        $this->authRepo = $authRepo;
    }

    /**
     * Uploads a file to AmazonS3.
     *
     * @param App\Models\Media $file
     * @return App\Models\Media $file
     */
    public function process($fileToUpload, $title = '', $description = '', $is_public = false)
    {
        $awsS3 = new UploadAmazonS3;
        $file = $awsS3->upload($fileToUpload);
        $file['user_id'] = $this->authRepo->getOwnerId();
        $file['title'] = $title;
        $file['description'] = $description;
        $file['is_public'] = $is_public;
        return Media::create($file);
    }

    /**
     * Upload a file to AmazonS3 and associate it to a user's profile image.
     *
     * @param App\Models\Media $file
     * @return App\Models\Media $media
     */
    public function createUserImage($file)
    {
        $media = $this->process($file);
        $media->title = "Profile Image";
        $media->save();
        if ($this->authRepo->isOwnerAdmin()) {
            $media['user_id'] = auth()->user()->id;
        }
        $user = User::where('id', $media['user_id'])->first();
        $user->profileImage()->sync([$media->id]);
        return $media;
    }

    /**
     * Returns an index of media that the auth
     * user has uploaded and media that
     * has been set for public use
     * by corporate.
     *
     * @param array $request
     * @param int $userId
     * @return App\Models\Media $media
     */
    public function index(array $request, $userId)
    {
        if (!isset($request['status'])) {
            $request['status'] = 'All';
        }
        if ($request['status'] == 'All') {
            $media = Media::where('media.title', 'LIKE', '%'.$request['search_term'].'%')
                ->where('media.user_id', $userId)
                ->orWhere('media.description', 'LIKE', '%'.$request['search_term'].'%')
                ->where('media.user_id', $userId);
            if ($userId !== config('site.apex_user_id')) {
                $media->orWhere('media.title', 'LIKE', '%'.$request['search_term'].'%')
                    ->where('is_public', true)
                    ->orWhere('media.description', 'LIKE', '%'.$request['search_term'].'%')
                    ->where('is_public', true);
            }
        } else {
            $media = Media::where('media.title', 'LIKE', '%'.$request['search_term'].'%')
                ->where('media.user_id', $userId)
                ->where('media.type', $request['status'])
                ->orWhere('media.description', 'LIKE', '%'.$request['search_term'].'%')
                ->where('media.user_id', $userId)
                ->where('media.type', $request['status']);

            if ($userId !== config('site.apex_user_id')) {
                $media->orWhere('media.title', 'LIKE', '%'.$request['search_term'].'%')
                    ->where('is_public', true)
                    ->where('media.type', $request['status'])
                    ->orWhere('media.description', 'LIKE', '%'.$request['search_term'].'%')
                    ->where('is_public', true)
                    ->where('media.type', $request['status']);
            }
        }
        return $media->orderBy('type', 'DESC')->paginate($request['per_page']);
    }

    /**
    * Adds filters to api for media library
    * All Media Files (both corporate shared and user’s own)
    * Your Media Files (Just the auth user’s own images)
    * Corporate Media Files (files shared with reps from corporate)
    *
    * @param array $request searchTerm, limit, type
    * @param int $userId
    * @return App\Models\Media $media
    */
    public function indexFilter(array $request, $userId)
    {
        $media = Media::search($request['searchTerm'])->where('type', 'Image');
        if ($request['type'] === 'user') {
            $media->where('user_id', $userId);
        } elseif ($request['type'] === 'shared') {
            $media->where('is_public', true)
                ->where('user_id', config('site.apex_user_id'));
        } else {
            $media->where('user_id', $userId)
                ->orWhere('is_public', true)
                ->where('user_id', config('site.apex_user_id'));
        }
        return $media->orderBy('created_at', 'DESC')->paginate($request['limit']);
    }

    /**
     * Returns an index of product images that is
     * currently in the auth user's store.
     *
     * @param array $request
     * @param int $userId
     * @return App\Models\Media $media
     */
    public function userProductImages(array $request, $userId)
    {
        $media = Media::whereHas('products', function ($query) use ($request, $userId) {
            $query->whereHas('inventory', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('quantity_available', '>', 0);
            })->where('name', 'LIKE', '%'.$request['searchTerm'].'%');
        });
        return $media->paginate($request['limit']);
    }

    /**
     * Returns an index of product images that is
     * currently in the corporate store.
     *
     * @param array $request
     * @return App\Models\Media $media
     */
    public function corporateProductImages(array $request)
    {
        $media = Media::whereHas('products', function ($query) use ($request) {
            $query->whereHas('inventory', function ($query) {
                $query->where('user_id', config('site.apex_user_id'))
                    ->where('quantity_available', '>', 0);
            })->whereHas('roles', function ($query) {
                $query->where('name', 'Customer')
                    ->orWhere('name', 'Rep');
            })->where('name', 'LIKE', '%'.$request['searchTerm'].'%');
        });
        return $media->paginate($request['limit']);
    }

    /**
     * Update an media record
     *
     * @param int $id
     * @param array $inputs
     * @return App\Models\Media $media
     */
    public function update($id, array $inputs = [])
    {
        $media = Media::find($id);

        if (!$this->authRepo->isAdmin()
            && $media->user_id !== $this->authRepo->getOwnerId()
        ) {
            return ['error' => 'You are not authorized to update this file.'];
        }

        $fields = [
            'title',
            'description',
            'is_public'
        ];

        foreach ($fields as $field) {
            $media->$field = array_get($inputs, $field);
        }
        $media->save();
        return $media;
    }
    /**
     * Deletes a media file, but and prevents users from
     * deleting other user's media files
     *
     * @param int $id
     * @param array $inputs
     * @return array
     */
    public function deleteMedia($id)
    {
        $media = Media::find($id);

        if (!$this->authRepo->isAdmin()
            && $media->user_id !== $this->authRepo->getOwnerId()
        ) {
            return ['success' => false, 'message' => 'You are not authorized to delete this file.'];
        }
        if (!isset($media)) {
            return ['success' => false, 'message' => 'No media exists with the id of ' + $id];
        }
        $media->delete($media);
        return ['success' => true, 'message' => 'Successfully deleted the file.'];
    }

    /**
     * Enables one or multiple files.
     *
     * @param array $mediaIds
     * @return App\Models\Media $medias
     */
    public function enable(array $mediaIds)
    {
        $medias = Media::whereIn('id', $mediaIds)->get();
        DB::beginTransaction();
        foreach ($medias as $media) {
            $media->disabled_at = null;
            $media->save();
        }
        DB::commit();
        return $medias;
    }

    /**
     * Disables one or multiple files.
     *
     * @param array $mediaIds
     * @return App\Models\Media $medias
     */
    public function disable(array $mediaIds)
    {
        $medias = Media::whereIn('id', $mediaIds)->get();
        DB::beginTransaction();
        foreach ($medias as $media) {
            $media->disabled_at = date("Y-m-d H:i:s");
            $media->save();
        }
        DB::commit();
        return $medias;
    }

    public function getImageUrlMapForProducts($ids)
    {
        // Pull the first Image url per Product then map to the mediable_id for easy access
        return Media::selectRaw('mediables.mediable_id, coalesce(media.url) as url')
            ->join('mediables', 'mediables.media_id', '=', 'media.id')
            ->where('mediables.mediable_type', 'App\Models\Product')
            ->where('media.type', '=', 'Image')
            ->whereIn('mediables.mediable_id', $ids)
            ->groupBy('mediables.mediable_id')
            ->get()->keyBy('mediable_id');
    }

    public function getImageUrlMapForVariants($ids)
    {
        // Pull the first Image url per Variant and map to variant id for easy access
        return Media::selectRaw('media_variant.variant_id, coalesce(media.url) as url')
            ->join('media_variant', 'media_variant.media_id', '=', 'media.id')
            ->where('media.type', '=', 'Image')
            ->whereIn('media_variant.variant_id', $ids)
            ->groupBy('media_variant.variant_id')
            ->get()->keyBy('variant_id');
    }

    public function getMediaForItems($items)
    {
        $productIds = [];
        $variantIds = [];
        foreach ($items as $key => $item) {
            $productIds[] = $item['product_id'];
            if (isset($item['variant_id'])) {
                $variantIds[] = $item['variant_id'];
            }
        }
        return [
            'product_media' => $this->getImageUrlMapForProducts($productIds),
            'variant_media' => $this->getImageUrlMapForVariants($variantIds),
        ];
    }

    public function mediaCount(array $request, $user_id)
    {
        $medias = $this->index($request, $user_id);
        $totalFileSize = "";
        $size =0;
        $count = [
            'all' => 0,
            'image' => 0,
            'document' => 0,
            'sheet' => 0,
            'presentation' =>0,
            'pdf' => 0,
            'video' => 0,
            'fileSize' => 0
        ];
        foreach ($medias as $media) {
            $count['all'] = $count['all']+1;
            switch ($media->type) {
                case 'Image':
                    $count['image'] = $count['image']+1;
                    break;
                case 'Document':
                    $count['document'] = $count['document']+1;
                    break;
                case 'Spreadsheet':
                    $count['sheet'] = $count['sheet']+1;
                    break;
                case 'Presentation':
                    $count['presentation'] = $count['presentation']+1;
                    break;
                case 'PDF':
                    $count['pdf'] = $count['pdf']+1;
                    break;
                case 'Video':
                    $count['video'] = $count['video']+1;
                    break;
            }
            if ($media->is_public == false && $media->user_id != 1) {
                $size = $media->size + $size;
            } elseif ($user_id === 1) {
                $size = $media->size + $size;
            }
        }
        $precision = 2;
        $base = log($size, 1024);
        $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
        $count['fileSize'] = round(pow(1024, $base - floor($base))/1024, $precision);
        if ($count['fileSize'] >= 0) {
            return $count;
        } else {
            $count['fileSize'] = 0;
            return $count;
        }
    }
}
