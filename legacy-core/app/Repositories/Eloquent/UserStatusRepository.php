<?php

namespace App\Repositories\Eloquent;

use App\Models\UserStatus;
use App\Models\User;
use App\Models\SubscriptionUser;
use App\Repositories\Contracts\UserStatusRepositoryContract;

class UserStatusRepository implements UserStatusRepositoryContract
{
    public static function getUserStatus()
    {
        return cache()->rememberForever('user-status', function () {
            return UserStatus::select(
                'id',
                'name',
                'default',
                'position',
                'visible',
                'login',
                'buy',
                'sell',
                'renew_subscription',
                'rep_locator'
            )
            ->orderBy('position')
            ->get()
            ->keyBy('name');
        });
    }

    public function index()
    {
        return self::getUserStatus();
    }

    public function find($id)
    {
        return UserStatus::find($id);
    }

    public function create($request)
    {
        $status = UserStatus::create($request);
        cache()->forget('user-status');
        return $status;
    }

    public function update($request, $id)
    {
        $status = UserStatus::find($id);
        // this keeps the exact state of the old status otherwise updating $status will also update $oldStatus
        $oldStatus = json_decode(json_encode($status));
        $status->update($request);
        // update current users for the updated status when there are changes to the renew subscription or rep locator
        if ($oldStatus->renew_subscription != $status->renew_subscription ||
            $oldStatus->rep_locator != $status->rep_locator) {
            $userIds = User::where('status', $status->name)->pluck('id');
            $this->updateUserStatus($userIds, $status->name);
        }
        cache()->forget('user-status');
        return $status;
    }

    public function delete($id)
    {
        $status = UserStatus::destroy($id);
        cache()->forget('user-status');
        return $status;
    }

    public function userStatusCheck($status)
    {
        return User::where('status', $status)->first();
    }

    public function updateUserStatus($userIds, $statusName)
    {
        $userStatus = UserStatus::where('name', $statusName)->first();
        $users = User::whereIn('id', $userIds)->update(['status' => $statusName]);
        SubscriptionUser::whereIn('user_id', $userIds)->update(['auto_renew' => $userStatus->renew_subscription]);
        return $users;
    }
}
