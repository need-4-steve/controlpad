<?php namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class MonitoringController extends Controller
{
    /**
     * A simple ping
     */
    public function simplePing()
    {
        return response()->json(array(
            "message" => "success",
            "timestamp" => date('c'),
            "tenant" => env("COMPANY_NAME")
        ));
    }
}
