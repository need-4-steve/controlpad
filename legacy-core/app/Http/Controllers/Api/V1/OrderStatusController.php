<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OrderStatus;
use App\Repositories\Eloquent\OrderStatusRepository;
use App\Repositories\Eloquent\OrderRepository;
use Validator;

class OrderStatusController extends Controller
{
    protected $orderStatusRepo;
    
    public function __construct(
        OrderStatusRepository $orderStatusRepo
    ) {
        $this->orderStatusRepo = $orderStatusRepo;
    }

    public function index()
    {
        $orderStatus = $this->orderStatusRepo->index();
        return response()->json($orderStatus, HTTP_SUCCESS);
    }

    public function create()
    {
        $request = request()->all();
        $request['name'] = isset($request['name']) && is_string($request['name']) ? strtolower($request['name']) : null;
        $validator = Validator::make($request, OrderStatus::$rules);
        if ($validator->fails()) {
            return response()->json($validator->messages(), HTTP_BAD_REQUEST);
        }
        $orderStatus = $this->orderStatusRepo->create($request);
        return response()->json($orderStatus, HTTP_SUCCESS);
    }

    public function update($id)
    {
        $request = request()->all();
        $request['name'] = isset($request['name']) && is_string($request['name']) ? strtolower($request['name']) : null;
        $rules = OrderStatus::$rules;
        $rules['name'] .= ','.$id;
        $validator = Validator::make($request, $rules);
        if ($validator->fails()) {
            return response()->json($validator->messages(), HTTP_BAD_REQUEST);
        }
        $status = $this->orderStatusRepo->find($id);
        if ($request['name'] !== $status['name'] && $status['default'] != false) {
            return response()->json('Cannot change name on default statuses', HTTP_BAD_REQUEST);
        }
        $orderStatus = $this->orderStatusRepo->update($request, $id);
        return response()->json($orderStatus, HTTP_SUCCESS);
    }

    public function delete($id)
    {
        $status = $this->orderStatusRepo->find($id);
        if ($status['default'] != false) {
            return response()->json('Cannot delete default statuses', HTTP_BAD_REQUEST);
        }
        $orders = $this->orderStatusRepo->orderStatusCheck($status['name']);
        if (count($orders) > 0) {
            return response()->json('Cannot delete a status that is in use', HTTP_BAD_REQUEST);
        }
        $this->orderStatusRepo->delete($id);
        return response()->json('Successfully deleted', HTTP_SUCCESS);
    }
}
