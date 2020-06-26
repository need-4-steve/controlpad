<?php

namespace App\Http\Controllers;

use DB;

class MonitoringController extends Controller
{
    public function ping()
    {
        try {
            app('db')->connection()->getPdo();
            $status = app('cache')->get('PING', function () {
                  app('cache')->forever('PING', 'Ping Successful');
                  return 'Ping Successful';
            });
            $response = response()->json($status);
        } catch (\Exception $e) {
            $response = response()->json('Ping Failed');
        }
        return $response->header('Cache-Control', 'private, no-cache');
    }
}
