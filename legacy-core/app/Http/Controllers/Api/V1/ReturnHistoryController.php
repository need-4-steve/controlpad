<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\ReturnHistoryRepository;

class ReturnHistoryController extends Controller
{
    /* @var \App\Repositories\Eloquent\ReturnHistoryRepository */
    protected $returnHistoryRepo;

    /**
     * ReturnHistoryController __constructor
     *
     * @method __construct
     * @param  ReturnHistoryRepository $returnHistoryRepo
     * @return void
     */
    public function __construct(ReturnHistoryRepository $returnHistoryRepo)
    {
        $this->returnHistoryRepo = $returnHistoryRepo;
    }

    /**
     * Show the Return History for an Order
     *
     * @method show
     * @param  int   $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $orderId)
    {
        $userId = auth()->id();

        $returnHistory = $this->returnHistoryRepo->getHistoryByUser($orderId, $userId);

        return response()->json($returnHistory);
    }
}
