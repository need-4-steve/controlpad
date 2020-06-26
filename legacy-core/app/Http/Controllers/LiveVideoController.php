<?php

namespace App\Http\Controllers;

use App\Models\OauthToken;
use App\Repositories\Eloquent\LiveVideoRepository;
use App\Repositories\Eloquent\AuthRepository;
use App\Services\LiveVideo\FacebookLiveVideo;
use App\Services\Oauth\FacebookOauthService;
use Illuminate\Http\Request;

class LiveVideoController extends Controller
{
    /* @var \App\Repositories\Eloquent\LiveVideoRepository */
    protected $liveVideoRepo;

    /* @var \App\Services\LiveVideo\LiveVideoContract */
    protected $liveVideoService;

    /* @var \App\Services\Oauth\OauthServiceInferface */
    protected $oauthService;

    /* @var \App\Repositories\Eloquent\AuthRepository */
    protected $authRepo;

    public function __construct(
        LiveVideoRepository $liveVideoRepo,
        AuthRepository $authRepo,
        FacebookLiveVideo $fbLiveVideoService,
        FacebookOauthService $facebookOauthService
    ) {
        $this->authRepo = $authRepo;
        $this->liveVideoRepo = $liveVideoRepo;
        $this->liveVideoService = $fbLiveVideoService;
        $this->oauthService = $facebookOauthService;
        $this->settingsService = app('globalSettings');
    }

    /**
    * Return an index of live videos.
    *
    * @method index
    * @param  string $service facebook, instagram, etc.
    * @return mixed
    */
    public function index()
    {
        if (auth()->user()->hasSellerType(['Reseller']) and
            !$this->settingsService->getGlobal('reseller_facebook_live', 'show') and
            !$this->settingsService->getGlobal('reseller_youtube', 'show') or
            auth()->user()->hasSellerType(['Affiliate']) and
            !$this->settingsService->getGlobal('affiliate_facebook_live', 'show') and
            !$this->settingsService->getGlobal('affiliate_youtube', 'show')) {
            return redirect('/dashboard');
        }

        $currentLiveVideo = false;
        if (cache()->has('is_live_streaming.'. auth()->user()->public_id)) {
            $currentLiveVideo = true;
        }
        if (auth()->user()->hasSellerType(['Affiliate'])) {
            $sellerType = 'Affiliate';
        } elseif (auth()->user()->hasSellerType(['Reseller'])) {
            $sellerType = 'Reseller';
        } else {
            $sellerType = null;
        }

        $videos = $this->liveVideoRepo->getByUser(auth()->id(), ['liveVideoInventory.inventory.item.product', 'liveVideoProduct']);
        return view('live-video.index')->with(['videos' => $videos, 'currentLiveVideo' => $currentLiveVideo, 'sellerType' => $sellerType]);
    }

    /**
     * Show a single live video's record.
     *
     * @method show
     * @param  string $service facebook, instagram, etc.
     * @param  int    $id
     * @return mixed
     */
    public function show(string $service, int $id)
    {
        $this->resolveService($service);
        if (is_null($this->liveVideoService)) {
            return redirect('/dashboard')->with(['message_error' => "We do not support that live video service."]);
        }
        $video = $this->liveVideoService->getVideo($id, $service);
        return view('live-video.show')->with(['video' => $video, 'id' => $id]);
    }

    /**
     * Create a new live video stream.
     *
     * @method create
     * @param  string $service facebook, instagram, etc.
     * @return mixed
     */
    public function create(string $service = 'facebook')
    {
        $sellerTypeId = null;
        $this->resolveService($service);
        if (is_null($this->liveVideoService)) {
            return redirect('/dashboard')->with(['message_error' => "We do not support that live video service."]);
        }
        if ($service === 'facebook') {
            $oauthUser = $this->oauthService->getUser();

            if (! session()->has('oauth') and ! $oauthUser) {
                session()->put('oauth_redirect_url', '/'.request()->path());
                return redirect()->away($this->oauthService->generateLoginUrl(true));
            }
        }

        if (isset($oauthUser->error) and $oauthUser->error) {
            return back()->with(['error' => $oauthUser->data['description']]);
        }

        $id = auth()->id();
        if (auth()->user()->seller_type_id) {
            $sellerTypeId = auth()->user()->seller_type_id;
        }
        if (auth()->user()->hasRole('Superadmin')) {
            $sellerTypeId = 1;
        }
        if ($service === 'facebook') {
            $user = collect(session('oauth'));
        } else {
            $user = null;
        }
        $currentLiveVideo = [
            'currentVideo' => null
        ];
        if (cache()->has('is_live_streaming.'. auth()->user()->public_id)) {
            $currentLiveVideo = cache('is_live_streaming.'.auth()->user()->public_id, null);
            $currentLiveVideo = collect($currentLiveVideo);
        }
        $currentLiveVideo = collect($currentLiveVideo);
        if ($service === 'youtube') {
            $service = collect(['keyname' => 'youtube', 'proper_name' => 'YouTube']);
            return view('live-video.create-youtube')->with([
                'currentLiveVideo' => $currentLiveVideo,
                'service' => $service,
                'id' => $id,
                'sellerTypeId' => $sellerTypeId]);
        } else {
            $service = collect(['keyname' => 'facebook', 'proper_name' => 'Facebook']);
            return view('live-video.create')->with([
                'oauth' => $user,
                'service' => $service,
                'id' => $id,
                'sellerTypeId' => $sellerTypeId]);
        }
    }

    /**
     * Determine which 3rd-party service we are using and if
     * it is supported by our system.
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
