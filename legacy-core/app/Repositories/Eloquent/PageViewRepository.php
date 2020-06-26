<?php

namespace App\Repositories\Eloquent;

use App\Models\PageView;
use Carbon\Carbon;
use DB;
use App\Models\UserSetting;

class PageViewRepository
{
    public function uniqueVisitors($storeOwnerId)
    {
        return [
            'today' => 0,
            'month' => 0
        ];

        $timeZone = $this->getUserTimeZone($storeOwnerId);
        $time = Carbon::now()->setTimezone($timeZone);
        $dailyTotal = PageView::where('store_owner_id', $storeOwnerId)
            ->where('created_at', '>=', $time->startOfDay()->setTimezone('UTC'))
            ->select('ip_address')
            ->distinct()
            ->get()
            ->count();

        $monthlyTotal = PageView::where('store_owner_id', $storeOwnerId)
            ->where('created_at', '>=', $time->startOfMonth()->setTimezone('UTC'))
            ->select('ip_address')
            ->distinct()
            ->get()
            ->count();

        return [
            'today' => $dailyTotal,
            'month' => $monthlyTotal
        ];
    }

    private function getUserTimeZone($userId)
    {
        $timeZone = UserSetting::where('user_id', $userId)->first();
        if ($timeZone == null) {
            $timeZone = 'UTC';
        } else {
            $timeZone = $timeZone->timezone;
        }

        return $timeZone;
    }
}
