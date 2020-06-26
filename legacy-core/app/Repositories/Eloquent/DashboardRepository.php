<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Contracts\DashboardRepositoryContract;
use Carbon\Carbon;
use DB;

class DashboardRepository implements DashboardRepositoryContract
{
    public function __construct(AuthRepository $authRepo)
    {
        $this->authRepo = $authRepo;
    }

    // Grab data for generating a sales graph
    public function salesVolumeGraphData()
    {
        $owner_id = $this->authRepo->getOwnerId();
        $response = [];

        // get the sales volume
        $salesVolume = Order::where('store_owner_user_id', $owner_id)
                    ->where('created_at', '>=', Carbon::now()->subMonths(1))
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as volume'))
                    ->groupBy('date')
                    ->get();
        $response['salesVolume'] = $salesVolume;

        // set dates, will be same on both queries
        $response['minDate'] = $salesVolume->min('date');
        $response['maxDate'] = $salesVolume->max('date');
        $minSalesVolume = $salesVolume->min('volume');
        $maxSalesVolume = $salesVolume->max('volume');

        // get the order volume
        $orderVolume = Order::where('customer_id', $owner_id)
                    ->where('created_at', '>=', Carbon::now()->subMonths(1))
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as volume'))
                    ->groupBy('date')
                    ->get();
        $response['orderVolume'] = $orderVolume;
        $minOrderVolume = $orderVolume->min('volume');
        $maxOrderVolume = $orderVolume->max('volume');

        // ternary volume setter for efficiency
        $response['minVolume'] = ($minSalesVolume < $minOrderVolume) ? $minSalesVolume : $minOrderVolume;
        $response['maxVolume'] = ($maxSalesVolume > $maxOrderVolume) ? $maxSalesVolume : $maxOrderVolume;

        return $response;
    }
}
