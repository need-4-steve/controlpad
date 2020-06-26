<?php namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\MediaRepository;
use App\Repositories\Eloquent\AuthRepository;
use App\Models\Media;
use App\Http\Requests\MediaRequest;
use Illuminate\Http\Request;
use App\Models\Setting;

class MediaController extends Controller
{
    /**
     * MediaController constructor.
     *
     * @param App\Repositories\Eloquent\MediaRepository $mediaRepo
     * @param App\Repositories\Eloquent\AuthRepository $authRepo
     */
    public function __construct(MediaRepository $mediaRepo, AuthRepository $authRepo)
    {
        $this->mediaRepo = $mediaRepo;
        $this->authRepo = $authRepo;
    }

    /**
     * Uploads a file to AmazonS3.
     *
     * @return \Illuminate\Http\Response
     */
    public function process()
    {
        $inputs = request()->all();
        if (auth()->user()->hasRole(['Admin', 'Superadmin'])
            and isset($inputs['is_public'])
            and $inputs['is_public'] === "true"
        ) {
            $inputs['is_public'] = 1;
        } else {
            $inputs['is_public'] = 0;
        }
        $file = request()->file('file');

        $media = $this->mediaRepo->process(
            $file,
            array_get($inputs, 'title', ''),
            array_get($inputs, 'description', ''),
            array_get($inputs, 'is_public', '')
        );

        /* this is needed for custom wrappers for specific uploads that need
         * to change things in the system outside of the media table, ie
         * the settings table
         */
        if (isset($inputs['image_type'])) {
            $image_type = $inputs['image_type'];
            if (in_array($inputs['image_type'], array('loading_icon','favicon','company_logo'))) {
                if (Setting::where('key', $image_type)->count() === 0) {
                    $setting = Setting::insert([
                        'user_id' => config('site.apex_user_id'),
                        'key' => $image_type,
                        'value' => json_encode(['value' => $media->url_sm]),
                        'category' => 'system_image'
                    ]);
                } else {
                    $setting = Setting::where('key', $image_type)->first();
                    $setting->update(
                        ['value' => json_encode(['value' => $media->url_sm])]
                    );
                }
            }
        }

        return response()->json($media, 200);
    }


    /**
     * Upload a file to AmazonS3 and associate it to a user's profile image.
     *
     * @return \Illuminate\Http\Response
     */
    public function createUserImage()
    {
        $file = request()->file('file');
        $media = $this->mediaRepo->createUserImage($file);
        return response()->json($media, 200);
    }

    /**
     * Returns an index of media that the auth
     * user has uploaded and media that
     * has been set for public use
     * by corporate.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $request = request()->all();
        $media = $this->mediaRepo->index($request, $this->authRepo->getOwnerId());
        return response()->json($media, 200);
    }

    /**
    * This returns the index with filter
    * All Media Files for Corparate and user's
    * the auth user's images
    * files shared with reps from corporate
    */
    public function indexFilter()
    {
        $request = request()->all();
        $media = $this->mediaRepo->indexFilter($request, $this->authRepo->getOwnerId());
        return response()->json($media, 200);
    }

    /**
     * Returns an index of product images that is
     * currently in the auth user's store.
     *
     * @return \Illuminate\Http\Response
     */
    public function userProductImages()
    {
        $request = request()->all();
        $media = $this->mediaRepo->userProductImages($request, $this->authRepo->getOwnerId());
        return response()->json($media, 200);
    }

    /**
     * Returns an index of product images that is
     * currently in the corporate store.
     *
     * @return \Illuminate\Http\Response
     */
    public function corporateProductImages()
    {
        $request = request()->all();
        $media = $this->mediaRepo->corporateProductImages($request);
        return response()->json($media, 200);
    }

    /**
     * Update an existing file.
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $request = request()->all();
        if (!auth()->user()->hasRole(['Admin', 'Superadmin']) and isset($request['is_public'])) {
            unset($request['is_public']);
        }

        $media = $this->mediaRepo->update($id, $request);

        if (isset($media['error'])) {
            return response()->json([$media['error']], 403);
        }

        return response()->json($media, 200);
    }

    /**
     * Delete a file.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $deletedMedia = $this->mediaRepo->deleteMedia($id);

        if ($deletedMedia['success']) {
            return response()->json([$deletedMedia['message']], 200);
        }
        return response()->json([$deletedMedia['message']], 403);
    }

    /**
     * Enables one or multiple files.
     *
     * @return \Illuminate\Http\Response
     */
    public function enable()
    {
        $request = request()->all();
        $media = $this->mediaRepo->enable($request['images']);
        return response()->json($media, 200);
    }

    /**
     * Disables one or multiple files.
     *
     * @return \Illuminate\Http\Response
     */
    public function disable()
    {
        $request = request()->all();
        $media = $this->mediaRepo->disable($request['images']);
        return response()->json($media, 200);
    }

    public function count()
    {
        $request = request()->all();
        $count = $this->mediaRepo->mediaCount($request, $this->authRepo->getOwnerId());
        return response()->json($count, 200);
    }
}
