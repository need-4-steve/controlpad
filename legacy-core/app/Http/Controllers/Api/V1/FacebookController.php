<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\LiveVideo\FacebookLiveVideo;

class FacebookController extends Controller
{
    protected $facebookService;

    public function __construct(FacebookLiveVideo $facebookService)
    {
        $this->facebookService = $facebookService;
    }

    /**
    * Get a Facebook user's info
    *
    * @return Response
    */
    public function user($user_id = null)
    {
        return $this->facebookService->getUser($user_id);
    }

    /**
    * Get a user's live video
    *
    * @param  int  $id
    * @return Response
    */
    public function liveVideos($user_id = null)
    {
        if (!$user_id) {
            $user_id = auth()->user()->id;
        }
        return $this->facebookService->getLiveVideos($user_id);
    }

    /**
    * Get a user's live video
    *
    * @param  int  $id
    * @return Response
    */
    public function liveVideo($id = null)
    {
        return $this->facebookService->getSingleLiveVideo($id);
    }

    /**
    * Create live video
    *
    * @param  int  $id
    * @return Response
    */
    public function createLiveVideo()
    {
        return $this->facebookService->createLiveVideo(auth()->user()->id);
    }

    /**
    * Get a Facebook user's friend list
    *
    * @param  int  $id
    * @return Response
    */
    public function friends($user_id = null)
    {
        if (!$user_id) {
            $user_id = auth()->user()->id;
        }
        return $this->facebookService->getFriends($user_id);
    }
}
