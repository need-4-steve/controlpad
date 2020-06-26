<?php namespace Test;

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

        $userPid = $request->header('UserPid');
        if (!$userPid) {
            $userPid = '1';
        }
        $userId = $request->header('UserId');
        if (!$userId) {
            $userId = 1;
        }

        $request->user = new TestAuthUser($userId, $userPid, $APIkey);

        return $next($request);
    }
}
