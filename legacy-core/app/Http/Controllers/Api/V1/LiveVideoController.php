<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\LiveVideo\FacebookLiveVideo;

use Illuminate\Http\Request;

class LiveVideoController extends Controller
{
    /* @var \App\Services\LiveVideo\FacebookLiveVideo */
    protected $facebookLVService;

    public function __construct(FacebookLiveVideo $facebookLVService)
    {
        $this->facebookLVService = $facebookLVService;
    }

    /**
    * Return a list of a user's live videos
    *
    * @param  int $user_id
    * @return \Illuminate\Http\Response
    */
    public function byUser(int $user_id = 0)
    {
        return request()->all();
    }

    /**
    * Return a live video
    *
    * @param  int $id
    * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        return request()->all();
    }

    /**
    * Return a public live video
    *
    * @param  int $id
    * @return \Illuminate\Http\Response
    */
    public function public($id)
    {
        return request()->all();
    }

    /**
    * Check for live video
    *
    * @param  int $id
    * @return \Illuminate\Http\Response
    */
    public function checkForLiveVideo($id)
    {
        return request()->all();
    }

    /**
    * Attach a product to a live video
    *
    * @return \Illuminate\Http\Response
    */
    public function attachProduct()
    {
        return request()->all();
    }

    /**
    * Detach a product from a live video
    *
    * @return \Illuminate\Http\Response
    */
    public function detachProduct()
    {
        return request()->all();
    }

    /**
    * Update a product's property that's attached to a live video
    *
    * @return \Illuminate\Http\Response
    */
    public function updateProductProperty()
    {
        return request()->all();
    }
}
