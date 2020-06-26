<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\LiveVideoRequest;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Eloquent\LiveVideoRepository;
use App\Services\LiveVideo\FacebookLiveVideo;
use App\Services\Oauth\FacebookOauthService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LiveVideoController extends Controller
{
    /* @var \App\Services\LiveVideo\LiveVideoContract */
    protected $liveVideoService;

    /* @var \App\Services\Oauth\OauthServiceInferface */
    protected $oauthService;

    /* @var \App\Repositories\Eloquent\InventoryRepository */
    protected $inventoryRepo;

    public function __construct(
        FacebookLiveVideo $fbLiveVideoService,
        FacebookOauthService $facebookOauthService,
        InventoryRepository $inventoryRepo,
        LiveVideoRepository $liveVideoRepo
    ) {
        $this->liveVideoService = $fbLiveVideoService;
        $this->oauthService = $facebookOauthService;
        $this->inventoryRepo = $inventoryRepo;
        $this->liveVideoRepo = $liveVideoRepo;
    }

    /**
     * Show all past live broadcasts that we have record of.
     *
     * @method index
     * @param  string $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(string $service)
    {
        $this->resolveService($service);
        if (is_null($this->liveVideoService)) {
            return response()->json(['error' => true, 'payload' => 'We do not support that live video service.'], HTTP_BAD_REQUEST);
        }
        $videos = $this->liveVideoService->getAllVideos();
        return response()->json($videos);
    }

    /**
     * Show the record for a prior live broadcast.
     *
     * @method show
     * @param  string $service
     * @param  int    $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $service, int $id)
    {
        $this->resolveService($service);
        if (is_null($this->liveVideoService)) {
            return response()->json(['error' => true, 'payload' => 'We do not support that live video service.'], HTTP_BAD_REQUEST);
        }

        $video = $this->liveVideoService->getVideo($id, $service);
        return response()->json($video);
    }

    /**
     * Create a record in our system of the live video broadcast.
     *
     * @method store
     * @param  LiveVideoRequest $request
     * @param  string           $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(LiveVideoRequest $request, string $service)
    {
        // forget the live video session data to prevent bleeding/crossover
        session()->forget('live-stream');

        $this->resolveService($service);
        if (is_null($this->liveVideoService)) {
            return response()->json(['error' => true, 'payload' => 'We do not support that live video service.'], HTTP_BAD_REQUEST);
        }

        $inputs = $request->all();
        if ($service === 'youtube') {
            $liveVideo = $this->liveVideoService->createYoutube($inputs);
        } else {
            $liveVideo = $this->liveVideoService->create($inputs);
        }
        if (! $liveVideo) {
            return response()->json(['error' => true, 'message' => "We were unable to create the video stream."], HTTP_SERVER_ERROR);
        }
        $payload = [
            'stream' => $inputs,
            'liveVideo' => $liveVideo,
        ];
        // put stuff in session so on rep's live page it can be accessed
        session('live-stream', $payload);
        cache()->forever('is_live_streaming.'.auth()->user()->public_id, ['stream' => $inputs, 'record' => $liveVideo]);

        return response()->json(['error' => false, 'data' => $payload]);
    }

    /**
     * Delete a record of a prior live broadcast from our database.
     * Note: This will not delete it from the external service.
     *
     * @method delete
     * @param  string $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $service, $id)
    {
        $this->resolveService($service);
        if (is_null($this->liveVideoService)) {
            return response()->json(['error' => true, 'payload' => 'We do not support that live video service.'], HTTP_BAD_REQUEST);
        }
        $request = request()->all();
        $deleted = $this->liveVideoService->deleteVideo($id);
        if (! $deleted) {
            return response()->json(['error' => true, 'payload' => 'We were unable to delete the live video record.'], HTTP_SERVER_ERROR);
        }
        return $deleted;
    }

    /**
     * Check with the external service to see if there are any currrent
     * live videos being broadcast right now.
     *
     * @method checkVideoFeed
     * @param  string         $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkVideoFeed(string $service)
    {
        $this->resolveService($service);
        if (is_null($this->liveVideoService)) {
            return response()->json(['error' => true, 'payload' => 'We do not support that live video service.'], HTTP_BAD_REQUEST);
        }

        if ($service === 'youtube') {
            $liveVideos = $this->liveVideoRepo->getYoutubeVideos();
        } else {
                $liveVideos = $this->liveVideoService->getLiveVideos();
        }
        return response()->json($liveVideos);
    }

    /**
     * Commit inventory to a sale.
     *
     * @method commitInventory
     * @param  string          $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function commitInventory(string $service)
    {
        $this->resolveService($service);
        $requestVideo = request()->get('video');
        $requestInventory = request()->get('inventory');
        if (is_null($this->liveVideoService)) {
            return response()->json(['error' => true, 'payload' => 'We do not support that live video service.'], HTTP_BAD_REQUEST);
        }

        if ($service === 'youtube') {
            $video = $this->liveVideoService->createYoutube($requestVideo);
        } else {
            $video = $this->liveVideoService->create($requestVideo);
        }

        $liveInventory = $this->liveVideoService->commitSaleInventory($video->id, $requestInventory, $service);
        if ($liveInventory === false) {
            return response()->json(['error' => true, 'message' => 'You can not mix Internal and External products.'], HTTP_BAD_REQUEST);
        }
        if (isset($requestVideo['time']) && (int)$requestVideo['time'] !== 0) {
            $hours = (int)$requestVideo['time'];
        } else {
            $hours = 4;
        }
        $cacheKey = 'live_videos.'.auth()->user()->public_id;
        $expiresAt = Carbon::now()->addHours($hours);
        cache()->put($cacheKey, ['video' => $video, 'liveInventory' => $liveInventory], $expiresAt);

        return response()->json($liveInventory[0]);
    }

    public function endSale()
    {
        // check for a live video by the logged in user.
        // check for it in session and in cache
    }

    public function addItemToCart()
    {
        // When adding an item from a sale to the cart,
        // ensure that this item has a discount amount
        // and a quantity on it so that the total
        // discount can be calculated
    }

    public function removeItemFromCart()
    {
        // When removing from the cart, run a refresh on
        // the cart's total discount so that it doesn't
        // get messed up later and give more or less
        // of a discount than it should
    }

    public function endLiveSession()
    {
        $cacheKeys = [
            'live_videos.'.auth()->user()->public_id,
            'is_live_streaming.'.auth()->user()->public_id,
        ];

        foreach ($cacheKeys as $cacheKey) {
            if (cache()->has($cacheKey)) {
                cache()->forget($cacheKey);
            }
        }

        return 'true';
    }


    /**
     *This is to get the users product from external source
     *
     * @return persoanl Products
     */
    public function personalProducts()
    {
        $request = request()->all();
        $products = $this->liveVideoRepo->personalProducts($request);

        return $products;
    }

    public function showPersonalProduct($id)
    {
        return $this->liveVideoRepo->findPersonalProduct($id);
    }

    public function createProduct(Request $request)
    {
        $rules = [
            'name' => 'required',
            'price' => 'required',
            'product_url' => 'required',
        ];
        $validate = $this->validate($request, $rules);
        $product = $this->liveVideoRepo->createProduct($request);
        return response()->json($product);
    }

    public function removePersonalProduct($id)
    {
        return $this->liveVideoRepo->deletePersonalProduct($id);
    }

    public function getAllVideoInvetoryByUser()
    {
        $request = request()->all();
        $videos = $this->liveVideoRepo->getByUser(auth()->id(), ['liveVideoInventory.inventory.item.product', 'liveVideoProduct'], $request);

        return $videos;
    }

    /**
     * Based on a string passed in, determine which external service
     * to load in for the generic reference.
     *
     * @method resolveService
     * @param  string         $service
     * @return void
     */
    private function resolveService(string $service)
    {
        switch (strtolower($service)) {
            case 'facebook':
                break;
            case 'youtube':
                break;
            default:
                $this->liveVideoService = null;
                $this->oauthService = null;
                break;
        }
    }
}
