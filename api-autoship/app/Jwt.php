<?php

namespace App;

use CPCommon\Jwt\Jwt as CPJwt;

class Jwt
{
    public static function create($role, $tenant, $user = null)
    {
        $claims = [
            'exp' => time() + 600, // 10 minutes
            'iat' => time(),
            'iss' => 'api.controlpad.com',
            'aud' => 'api.controlpad.com',
            'sub' => isset($user->id) ? $user->id : null,
            'userPid' => isset($user->pid) ? $user->pid : null,
            'name' => isset($user->first_name) ? $user->first_name : '',
            'fullName' => (isset($user->first_name) && isset($user->last_name)) ? $user->first_name.' '.$user->last_name : '',
            'repSubdomain' => null,
            'role' => $role,
            'sellerType' => null,
            'perm' => [
                'core:buy' => true,
                'core:sell' => true
            ],
            'acceptedTerms' => true,
            'activeSubscription' => null,
            'orgId' => $tenant->org_id,
            'tenant_id' => $tenant->id // deprecated
        ];
        $token = CPJwt::sign($claims, env('JWT_SECRET'));
        return $token;
    }
}
