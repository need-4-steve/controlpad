<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp;

class ReleaseNotesController extends Controller
{
    public function getMerges()
    {
        $client = new GuzzleHttp\Client(['headers' => ['Authorization' => 'token d248b877c3850757b355a5d6fd5a7c32c833022f']]);
        $url = 'https://api.github.com/repos/ControlpadLLC/legacy-core/pulls?state=closed';
        $res = $client->get($url);
        return json_decode($res->getBody(), 1);
    }
}
