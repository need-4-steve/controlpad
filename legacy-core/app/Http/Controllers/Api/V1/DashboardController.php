<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests;
use Response;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\PageViewRepository;
use App\Repositories\Eloquent\DashboardRepository;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        AuthRepository $authRepo,
        OrderRepository $orderRepo,
        PageViewRepository $pageViewRepo,
        DashboardRepository $dashboardRepo
    ) {
        $this->authRepo = $authRepo;
        $this->orderRepo = $orderRepo;
        $this->pageViewRepo = $pageViewRepo;
        $this->dashboardRepo = $dashboardRepo;
    }

    public function salesVolume()
    {
        // get owner
        $storeOwnerId = $this->authRepo->getOwnerId();

        // get values for graph
        $response = $this->dashboardRepo->salesVolumeGraphData();

        // get values for tickers at top
        $response['newOrders'] = $this->orderRepo->newOrderCount($storeOwnerId);
        $response['monthlyOrderVolume'] = $this->orderRepo->orderVolume($storeOwnerId);
        $response['monthlySalesVolume'] = $this->orderRepo->salesVolume($storeOwnerId);
        $response['pageViews'] = $this->pageViewRepo->uniqueVisitors($storeOwnerId);

        return response()->json($response, 200);
    }
}
