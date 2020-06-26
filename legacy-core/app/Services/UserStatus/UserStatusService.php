<?php

namespace App\Services\UserStatus;

use App\Repositories\Eloquent\UserStatusRepository;

class UserStatusService
{
    public function __construct()
    {
        $this->userStatusRepo = new UserStatusRepository;
        $this->settings = app('globalSettings');
    }

    public function checkPermission($user, $permission)
    {
        $statuses = $this->userStatusRepo->index();
        if (isset($user->status) && isset($statuses[$user->status][$permission]) && $statuses[$user->status][$permission]) {
            return true;
        }
        return false;
    }

    public function checkUserIdPermission($userId, $permission)
    {
        return $this->checkPermission(\App\Models\User::select('status')->where('id', $userId)->first(), $permission);
    }

    public function getSellRedirectUrl()
    {
        return $this->settings->getGlobal('user_status_sell_url', 'value');
    }
}
