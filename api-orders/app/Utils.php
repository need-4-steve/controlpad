<?php

namespace App;

use Illuminate\Http\Request;
use CPCommon\Jwt\Jwt;

class Utils
{
    private $apiJwt = null;
    private $orgId = null;

    public function __construct(Request $request)
    {
        $this->orgId = $request->user->orgId;
    }

    public function getJWTAuthHeader()
    {
        if (!isset($this->apiJwt)) {
            $claims = [
                'exp' => time() + 300,
                'iat' => time(),
                'iss' => 'api.controlpad.com',
                'aud' => 'api.controlpad.com',
                'sub' => 1,
                'role' => 'Admin',
                'orgId' => $this->orgId,
                'userPid' => null
            ];
            $this->apiJwt = Jwt::sign($claims, env('JWT_SECRET'));
        }
        return 'Bearer ' . $this->apiJwt;
    }
}
