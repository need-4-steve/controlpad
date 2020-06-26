<?php

namespace App\Services\LiveVideo;

use App\Data\FacebookPersistantDataInterface;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Eloquent\LiveVideoRepository;
use App\Repositories\Eloquent\OauthTokenRepository;
use App\Repositories\Eloquent\AuthRepository;

use Facebook\Facebook;
use Facebook\FacebookRequest;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

use GuzzleHttp;
use ReflectionCLass;

class FacebookLiveVideo implements LiveVideoContract
{
    /* @var \App\Repositories\Eloquent\InventoryRepository */
    protected $inventoryRepo;

    /* @var \App\Repositories\Eloquent\LiveVideoRepository */
    protected $liveVideoRepo;

    /* @var \Facebook\Facebook */
    protected $facebook;

    /* @var App\Repositories\Eloquent\AuthRepository */
    protected $authRepo;

    public function __construct(InventoryRepository $inventoryRepo, LiveVideoRepository $liveVideoRepo, AuthRepository $authRepo)
    {
        $this->inventoryRepo = $inventoryRepo;
        $this->liveVideoRepo = $liveVideoRepo;
        $this->authRepo = $authRepo;

        $this->facebook = new Facebook([
            'app_id'                  => env('FACEBOOK_CLIENT_ID'),
            'app_secret'              => env('FACEBOOK_CLIENT_SECRET'),
            'default_graph_version'   => env('FACEBOOK_API_VERSION'),
            'persistent_data_handler' => new FacebookPersistantDataInterface()
        ]);
    }

    /**
     * Get all records of past live videos that we have in our system.
     *
     * @method getAllVideos
     * @return mixed
     */
    public function getAllVideos()
    {
        return $this->liveVideoRepo->getByDriverAndUser(1, auth()->id());
    }

    /**
     * Get a single record of a past live video we have in our system.
     *
     * @method getVideo
     * @param  int      $videoId
     * @return mixed
     */
    public function getVideo(int $videoId, string $service)
    {

        return $this->liveVideoRepo->findById($videoId, $service, auth()->id(), ['liveVideoInventory.inventory.item.product']);
    }

    /**
     * Get all videos that are currently live on an external service.
     *
     * @method getLiveVideo
     * @return mixed
     */
    public function getLiveVideos()
    {
        $token = session('oauth.access_token');
        $video = null;

        try {
            $fields = '&fields=id,embed_html,status,description,broadcast_start_time,creation_time';
            $video = $this->facebook->get('/'.session('oauth.id').'/live_videos?access_token='.$token.$fields);
        } catch (FacebookResponseException $e) {
            // When Graph returns an error
            return $e->getMessage();
        } catch (FacebookSDKException $e) {
            // When validation fails or other local issues
            return $e->getMessage();
        }
        return $video->getDecodedBody();
    }

    public function create(array $inputs)
    {
        $payload = [
            'user_id'         => auth()->id(),
            'video_id'        => $inputs['id'],
            'description'     => $inputs['description'],
            'oauth_driver_id' => 1,
        ];
        return $this->liveVideoRepo->create($payload);
    }
    public function createYoutube(array $inputs)
    {
        $payload = [
            'user_id'         => auth()->id(),
            'video_id'        => $inputs['embed_html'],
            'description'     => $inputs['description'],
            'oauth_driver_id' => 4,
        ];
        return $this->liveVideoRepo->create($payload);
    }

    public function getLiveInventory(int $videoId)
    {
        return $this->liveVideoRepo->find($videoId, ['liveVideoInventory'])->inventory;
    }

    public function commitSaleInventory(int $videoId, array $items, string $service)
    {
        $liveInventory = [];

        $video = \App\Models\LiveVideo::where('id', $videoId)->with(['liveVideoInventory', 'LiveVideoProduct'])->first();
        if (count($video->liveVideoInventory) > 0 && isset($items[0]['personal']) && $items[0]['personal'] === true) {
            return false;
        }
        if (count($video->liveVideoInventory) > 0 && isset($items[0]['product_url'])) {
            return false;
        }
        if (count($video->LiveVideoProduct) > 0 && isset($items[0]['personal']) && $items[0]['personal'] === false) {
            return false;
        }
        if (count($video->LiveVideoProduct) > 0 && isset($items[0]['product_id'])) {
            return false;
        }
        foreach ($items as $item) {
            if (isset($item['product_url']) && $item['product_url'] || isset($item['personal']) && $item['personal'] === true) {
                $product = $this->liveVideoRepo->productCreateOrUpdate($item, $videoId);
                $liveInventory[] = $product;
            } else {
                $inventory = \App\Models\Inventory::where('id', $item['id'])->first();
                $liveInventory[] = \App\Models\LiveVideoInventory::updateOrCreate([
                    'live_video_id' => $videoId,
                    'inventory_id'  => (int) $item['id'],
                    'user_id' => $this->authRepo->getOwnerId(),
                    'item_id' => $inventory->item_id,
                ], [
                    'discount_amount' => isset($item['amount']) ? $item['amount'] : 0,
                    'sale_quantity'   => isset($item['quantity']) ? (int) $item['quantity'] : 0, // TODO: this isn't currently in use and needs cleaning up
                    'discount_is_percent' => false
                ]);
            }
        }
        return $liveInventory;
    }

    public function deleteVideo($id)
    {
        return $this->liveVideoRepo->delete($id);
    }
}
