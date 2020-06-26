<?php

namespace App\Repositories\Eloquent;

use App\Models\LiveVideo;
use App\Models\YoutubeVideo;
use App\Models\LiveVideoProduct;
use App\Models\LiveVideoInventory;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\AuthRepository;

class LiveVideoRepository
{
    use CommonCrudTrait;

    public function getByDriverAndUser(int $driverId, int $userId, array $eagerLoad = [])
    {
        return LiveVideo::whereDriver($driverId)
                        ->where(['user_id' => $userId])
                        ->with($eagerLoad)->get();
    }

    /**
     * Find a video by it's ID.
     *
     * @method findById
     * @param  int      $videoId
     * @param  int      $userId
     * @param  int      $driverId
     * @param  array    $eagerLoad
     * @return Collection
     */
    public function findById(int $videoId, string $service, int $userId, array $eagerLoad = [])
    {
        if ($service === 'youtube') {
            $driverId = 4;
            return LiveVideo::with($eagerLoad)
                            ->whereDriver($driverId)
                            ->where(['id' => $videoId, 'user_id' => $userId])
                            ->first();
        } else {
            $driverId = 1;
            return LiveVideo::with($eagerLoad)
                            ->whereDriver($driverId)
                            ->where(['id' => $videoId, 'user_id' => $userId])
                            ->first();
        }
    }

    /**
     * Get live videos by a user
     *
     * @param int   $userId
     * @param array $eagerLoad
     * @return Collection
     */
    public function getByUser(int $userId, array $eagerLoad = [], $request = null)
    {
        if ($request === null) {
            $request['per_page'] = 15;
        }
        $liveVideo = LiveVideo::withTrashed()
                    ->with($eagerLoad)
                    ->with('driver')
                    ->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->paginate($request['per_page']);

        return $liveVideo;
    }

    /**
     * Create a live video record
     *
     * @param array $data
     * @return LiveVideo
     */
    public function create(array $data)
    {
        $inputs = [];

        $fields = [
            'user_id',
            'oauth_driver_id',
            'video_id',
            'thumbnail',
            'service_save_later',
            'description'
        ];
        $authRepo = new AuthRepository;
        $user_id = $authRepo->getOwnerId();

        foreach ($fields as $field) {
            $inputs[$field] = array_get($data, $field, '');
        }

        $inputs['user_id'] = $user_id;
        $video = LiveVideo::updateOrCreate([
                'user_id'         => $user_id,
                'oauth_driver_id' => $data['oauth_driver_id'],
                'video_id'        => $data['video_id'],
                'description'     => $data['description']
            ], $inputs);
        return $video;
    }

    public function getYoutubeVideos()
    {
        return LiveVideo::where(['user_id' => auth()->id(), 'oauth_driver_id' => 4])->with('liveVideoInventory.inventory.item.product', 'liveVideoProduct')->get();
    }

    /**
     * Patch a live video record
     *
     * @param int $id
     * @param string $key
     * @param string $value
     * @return LiveVideo
     */
    public function patch(int $id, $params, array $eager = [])
    {
        LiveVideo::find($id)->update([
            $params['key'] => $params['value']
        ]);
    }

    public function delete($id)
    {
        $video = LiveVideo::find($id);
        if ($video) {
            LiveVideo::destroy($id);
            return ['error' => false];
        } else {
            return ['error' => true];
        }
    }

    public function personalProducts($input)
    {
        $product = LiveVideoProduct::with('media')
            ->where('user_id', auth()->user()->id)
            ->paginate($input['per_page']);

        return $product;
    }

    public function findPersonalProduct($id)
    {
        $product = LiveVideoProduct::where('id', $id)->with('media')->first();
        return $product;
    }

    public function productCreateOrUpdate($item, $videoId)
    {

        $product = LiveVideoProduct::where('id', $item['id'])->with('media')->first();
        $product->liveVideos()->syncWithoutDetaching([$videoId]);
        $product->live_video_id = $videoId;

        return $product;
    }

    public function createProduct($input)
    {
        if (isset($input['id'])) {
            $product = $this->findPersonalProduct($input['id']);
            $product->update(
                ['name' => $input['name'],
                'price' => $input['price'],
                'product_url' => $input['product_url'],
                'user_id' => auth()->user()->id
                ]
            );
            $product->save();
        } else {
            $product = LiveVideoProduct::create([
                'name' => $input['name'],
                'price' => $input['price'],
                'product_url' => $input['product_url'],
                'user_id' => auth()->user()->id
            ]);
        }
        if ($input['images']) {
            $product->media()->sync($input['images']);
        }

        return $product;
    }

    public function deletePersonalProduct($id)
    {
        $product = LiveVideoProduct::find($id);
        if ($product) {
            LiveVideoProduct::destroy($id);
            return ['error' => false];
        } else {
            return ['error' => true];
        }
    }
}
