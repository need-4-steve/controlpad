<?php namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ReturnModel;
use App\Repositories\Eloquent\ReturnModelRepository;
use App\Http\Requests\ReturnedRequest;

class ReturnController extends Controller
{
    /* @var \App\Repositories\Eloquent\ReturnModelRepository */
    protected $returnRepo;

    public function __construct(ReturnModelRepository $returnRepo)
    {
        $this->returnRepo = $returnRepo;
    }

    /**
     * Show all of the Return requests.
     *
     * @method getIndex
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $request = request()->all();
        $relationships = [
            'order.customer.role',
            'status'
        ];
        if (auth()->user()->hasRole(['Superadmin'])) {
            $returnRequests = $this->returnRepo->allReturns($request, $relationships);
        } else {
            $returnRequests = $this->returnRepo->returnRequestsByUser(auth()->id(), $request, $relationships);
        }
        return response()->json($returnRequests);
    }

    /**
     * Create a new Return.
     *
     * @method postRequest
     * @param  ReturnedRequest $returnRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function postRequest(ReturnedRequest $returnRequest)
    {
        $requests = $this->returnRepo->create(1, $returnRequest->input('return_items'));
        if ($requests === false) {
            return response()-> json($requests, HTTP_NOT_ACCEPTABLE);
        }
        return response()->json($requests);
    }

    /**
     * [orderReturned description]
     *
     * @method orderReturned
     * @param  integer $order_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderReturned($order_id)
    {
        $requests = $this->returnRepo->showOrderReturned($order_id);
        return response()->json($requests);
    }

    /**
     * [patchUpdate description]
     *
     * @method patchUpdate
     * @param  integer $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function patchUpdate($id)
    {
        $originalModel = $this->returnRepo->find($id);

        if (! $originalModel) {
            return response()->json(['error' => true, 'message' => 'Return record not found'], HTTP_BAD_REQUEST);
        }

        $returned = $this->returnRepo->update($originalModel, request()->all());
        return response()->json($returned);
    }

    /**
     * [submitRefund description]
     *
     * @method submitRefund
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitRefund()
    {
        $this->validate(request(), [
            'lines' => 'required',
            'lines.*.inventoryQuantity' => 'required',
            'lines.*.returnAmount' => 'required'
        ], [
            'lines.*.returnAmount.required' => 'Must enter a return amount for all return items.',
            'lines.*.inventoryQuantity.required' => 'Must enter a quantity for all return items.'
        ]);
        $input = request()->all();
        $amount = $this->returnRepo->refund($input);

        return response()->json($amount);
    }
    /**
     * [reasons description]
     *
     * @method reasons
     * @return \Illuminate\Http\JsonResponse
     */
    public function reasons()
    {
        $reasons = $this->returnRepo->allReasons();
        return response()->json($reasons);
    }

    public function getHistory()
    {
        $id = auth()->id();
        $history = $this->returnHistoryRepo->getHistoryByUser($id);

        return response()->json($history);
    }

    public function returnQuantity()
    {
        $request = request()->all();
        $orderline_id = $request['id'];
        $orderline_quantity = $request['quantity'];
        $retunedQuantity = $this->returnRepo->quantityReturned($orderline_id, $orderline_quantity);
        return response()->json($retunedQuantity, HTTP_SUCCESS);
    }

    public function showReturnedById($id)
    {
        return $this->returnRepo->returnedById($id);
    }
}
