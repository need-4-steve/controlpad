<?php

namespace App\Http\Controllers\Controlpad\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\ReturnModelRepository;

class ReturnController extends Controller
{
    public function __construct(ReturnModelRepository $returnRepo)
    {
        $this->returnRepo = $returnRepo;
    }

    /**
     * Get returns based on parameters submitted
     *
     * Post request:
     * @param search_term (searches for order_id, or null for all)
     * @param column (column to sort by)
     * @param order (order to sort, defaults to descending)
     * @param per_page (per page, if pagination is required, or null)
     * @param user_id (if we want returns for a specific user, use this user_id)
     * @param order_type_id (null for all, or type id:
     *           1- corp to rep
     *           2- corp to customer
     *           3- rep to customer
     *           4- rep to rep
     *           5- corp to admin),
     *
     * @return ReturnModel
     */
    public function index()
    {
        try {
            $request = request()->all();
            $returns = $this->returnRepo->allReturnRequests($request, $relationships);
            return $returns;
        } catch (Exception $e) {
            return "Error: " . $e;
        }
    }
}
