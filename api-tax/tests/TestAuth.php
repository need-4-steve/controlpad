<?php

use CPCommon\UserAuthorization;

class TestAuth
{
    public function handle($request, $next, $guard = null)
    {
        // api key can just select the role you wish to use for testing
        $APIkey = $request->header('APIKey');
        if (!isset($APIkey) || !in_array($APIkey, ['Customer', 'Rep', 'Admin', 'Superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = $request->header('UserId');
        if (!$userId) {
            $userId = 1;
        }

        $request->user = new UserAuthorization(true, ['sub' => $userId, 'userPid' => (string)$userId, 'exp' => null, 'role' => $APIkey, 'orgId' => 'fake-id']);

        return $next($request);
    }
}
