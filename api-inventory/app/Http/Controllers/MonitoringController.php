<?php

namespace App\Http\Controllers;

use DB;

class MonitoringController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function ping()
    {
        try {
            app('db')->connection()->getPdo();
            app('cache')->put('foo', 'Ping Successful', 10);
            $response = response()->json(app('cache')->get('foo'));
        } catch (\Exception $e) {
            $response = response()->json('Ping Failed');
        }
        return $response->header('Cache-Control', 'private, no-cache');
    }
}
