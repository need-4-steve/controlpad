<?php

namespace App\Utilities\V0;
use CPCommon\Pid\Pid;

class ApiKeyUtilities {
    public static function generateKey($app_name, $tenant_id) {
        // generate app key and secret key hashes
        $app_id = Pid::create();
        $secret = substr(rtrim(strtr(base64_encode(str_shuffle($app_id.env('JWT_SECRET', ''))), '+/', '-_'), '='), 0, 64);

        return ['app_id' => $app_id, 'secret' => $secret];
    }

    public static function refreshKey($app_id) {
        $secret = substr(rtrim(strtr(base64_encode(str_shuffle($app_id.env('JWT_SECRET', ''))), '+/', '-_'), '='), 0, 64);

        return ['secret' => $secret];
    }
}
