<?php

namespace App\Services\User;

use DB;
use Log;

class UserService
{
    public function getPidForId($userId)
    {
        $user = DB::table('users')->select('pid')->where('id', '=', $userId)->first();
        return ($user == null ? null : $user->pid);
    }
}
