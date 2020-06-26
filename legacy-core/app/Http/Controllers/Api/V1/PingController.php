<?php namespace App\Http\Controllers\Api\V1;

use Response;
use Input;
use App\Http\Controllers\Controller;

class PingController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        logger('reached ping controller');
        return response()->json([
            'message' => 'pong: ' . date('Y.m.d H:i:s e')
        ]);
        //return response('pong: ' . date('Y.m.d H:i:s e'))->header('Content-Type', 'text/plain');
    }
}
