<?php namespace App\Repositories\Eloquent;

use DB;
use Carbon\Carbon;
use App\Jobs\CancelOrder;
use App\Models\ReturnModel;
use App\Models\Returnline;
use App\Models\ReturnHistory;
use App\Models\ReturnReason;
use App\Models\ReturnStatus;
use App\Models\ReturnTransaction;
use App\Models\Order;
use App\Models\Orderline;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Eloquent\ReturnHistoryRepository;
use App\Services\PayMan\EwalletService;

class ReturnModelRepository
{
    use CommonCrudTrait;

    public function __construct(
        EwalletService $ewallet,
        InventoryRepository $inventoryRepo,
        ReturnHistoryRepository $returnHistoryRepo
    ) {
        $this->ewalletService = $ewallet;
        $this->inventoryRepo = $inventoryRepo;
        $this->returnHistoryRepo = $returnHistoryRepo;
    }

    /**
     * Create a new instances of ReturnModelRepository
     *
     * @param int $returnStatusId
     * @param array $orderline
     * @return bool|ReturnModelRepository
     */
    public function create(int $status, array $returnItems)
    {
        $order =  Order::where('id', $returnItems[0]['order_id'])->first();
        $newReturn = ReturnModel::create([
            'order_id'          => $returnItems[0]['order_id'],
            'initiator_user_id' => auth()->id(),
            'return_status_id'  => $status,
            'user_id' => $order->customer_id,
        ]);

        foreach ($returnItems as $item) {
            $orderline = Orderline::where('id', $item['orderline_id'])->first();
            if (!isset($item['comments'])) {
                $item['comments'] = ' ';
            }
            $returnline = Returnline::create([
                'return_id'        => $newReturn->id,
                'item_id'          => $orderline->item_id,
                'orderline_id'     => $orderline->id,
                'type'             => $orderline->type,
                'name'             => $orderline->name,
                'price'            => $orderline->price,
                'return_reason_id' => $item['reason_id'],
                'comments'         => $item['comments'],
                'quantity'         => $item['return_quantity'],
                'custom_sku'       => $orderline->custom_sku,
                'manufactured_sku' => $orderline->manufactured_sku,
            ]);
        }

        return $newReturn;
    }

    /**
     * Update a ReturnModel
     *
     * @param int $returnId
     * @param array $data
     * @return bool|ReturnModelRepository
     */
    public function update(ReturnModel $originalModel, array $data = [])
    {
        if (!isset($data['notes'])) {
            $data['notes'] = ' ';
        }
        $returnHistory = ReturnHistory::create([
            'return_id'     => $originalModel->id,
            'user_id'       => auth()->id(),
            'old_status_id' => $originalModel->return_status_id,
            'new_status_id' => $data['return_status_id'],
            'comments'      => $data['notes'],
        ]);
        return $originalModel->update($data);
    }

    /**
     * get all return requests by user
     *    Kept function for endpoint; below function can now also search with
     *    user_id.
     *
     * @param int $user_id
     * @param array $request
     * @param array $relationships
     * @return LengthAwarePaginator
     */
    public function returnRequestsByUser($user_id, $request, $relationships = [])
    {
        $start_date = Carbon::parse($request['start_date'])->startOfDay()->format('Y-m-d H:i:s');
        $end_date = Carbon::parse($request['end_date'])->endOfDay()->format('Y-m-d H:i:s');
        if (isset($request['status']) && $request['status'] !== 'all') {
            $status_id = ReturnStatus::where('name', $request['status'])->first()->id;
        } else {
            $status_id = '';
        }
        $returns = ReturnModel::with($relationships)
        ->where('initiator_user_id', $user_id)
        ->where('user_id', '!=', $user_id)
        ->join('orderlines', 'returns.order_id', '=', 'orderlines.order_id')
        ->join('orders', 'returns.order_id', '=', 'orders.id')
        ->join('return_lines', 'returns.id', '=', 'return_lines.return_id')
        ->join('users', 'returns.user_id', '=', 'users.id')
        ->whereBetween('returns.created_at', [$start_date, $end_date]);
        if ($status_id !== '') {
            $returns->where('returns.return_status_id', $status_id);
        }
        $returns->select(
            'returns.*',
            'orders.receipt_id',
            'orderlines.quantity',
            'return_lines.quantity as requested_quantity',
            'users.first_name',
            'users.last_name'
        )
        ->groupBy('returns.id');

        if (!empty($request['search_term'])) {
            $returns = $returns
                        ->where('returns.order_id', 'LIKE', "%" . $request['search_term'] . "%")
                        ->orWhere('orders.receipt_id', 'LIKE', "%" . $request['search_term'] . "%")
                        ->orWhere('users.last_name', 'LIKE', "%" . $request['search_term'] . "%")
                        ->orWhere('users.first_name', 'LIKE', "%" . $request['search_term'] . "%")
                        ->orWhere('returns.return_status_id', 'LIKE', "%" . $request['search_term'] . "%")
                        ->select('*', 'returns.id as id');
        }
        // add order for sorting when not set, if request includes 'column' but not 'order'
        if (empty($request['order'])) {
            $request['order'] = 'DESC';
        }
        // column check for sorting
        if (!empty($request['column'])) {
            $returns = $returns->orderBy($request['column'], $request['order']);
        }
        // paginate if the request calls for pagination
        if (!empty($request['per_page']) and is_numeric($request['per_page'])) {
            return $returns->paginate($request['per_page']);
        }

        return $returns->get();
    }

    public function allReturns($request, $relationships = [])
    {
        $start_date = Carbon::parse($request['start_date'])->startOfDay()->format('Y-m-d H:i:s');
        $end_date = Carbon::parse($request['end_date'])->endOfDay()->format('Y-m-d H:i:s');
        if (isset($request['status']) && $request['status'] !== 'all') {
            $status_id = ReturnStatus::where('name', $request['status'])->first()->id;
        }
        $returns = ReturnModel::with($relationships)
        ->join('orderlines', 'returns.order_id', '=', 'orderlines.order_id')
        ->join('orders', 'returns.order_id', '=', 'orders.id')
        ->join('return_lines', 'returns.id', '=', 'return_lines.return_id')
        ->join('users', 'returns.user_id', '=', 'users.id')
        ->whereBetween('returns.created_at', [$start_date, $end_date]);
        if (isset($request['status']) && $request['status'] !== 'all') {
            $returns->where('returns.return_status_id', $status_id);
        }
        $returns->select(
            'returns.*',
            'orders.receipt_id',
            'orderlines.quantity',
            'return_lines.quantity as requested_quantity',
            'users.first_name',
            'users.last_name'
        )
        ->groupBy('returns.id');

        if (!empty($request['search_term'])) {
            $returns->where(function ($query) use ($request) {
                $query->where('returns.order_id', 'LIKE', "%" . $request['search_term'] . "%")
                ->orWhere('orders.receipt_id', 'LIKE', "%" . $request['search_term'] . "%")
                ->orWhere('users.last_name', 'LIKE', "%" . $request['search_term'] . "%")
                ->orWhere('users.first_name', 'LIKE', "%" . $request['search_term'] . "%")
                ->orWhere('returns.return_status_id', 'LIKE', "%" . $request['search_term'] . "%")
                ->select('*', 'returns.id as id');
            });
        }
        // add order for sorting when not set, if request includes 'column' but not 'order'
        if (empty($request['order'])) {
            $request['order'] = 'DESC';
        }
        // column check for sorting
        if (!empty($request['column'])) {
            $returns = $returns->orderBy($request['column'], $request['order']);
        }
        // paginate if the request calls for pagination
        if (!empty($request['per_page']) and is_numeric($request['per_page'])) {
            return $returns->paginate($request['per_page']);
        }

        return $returns->get();
    }

    /**
     * Get all of the previously returned item for an order
     *
     * @param int $order_id
     * @return Array of orderline id with amout returned
     */
    public function showOrderReturned($order_id)
    {
        $orderReturns = ReturnModel::with(['lines','status'])->where('order_id', $order_id)->get();
        return $orderReturns;
    }

    /**
     * Get the price of an item or items for return and the transaction id
     *
     * @param Array contians order id, orderline id, and quantity being returned
     * @return Array the amount to be refund and the transaction id
     */
    public function itemPrice($input)
    {
        $item = [];
        $order = Order::with('lines')->find($input['order_id']);
        $returned = ReturnModel::find($input['returned_id']);
        if ($returned['transaction_id'] !== null) {
            return false;
        }
        foreach ($order->lines as $line) {
            if ($line->id === $input['orderline_id']) {
                $item = $line;
            }
        }
        $price = $item->price;
        if ($input['quantity'] > 1) {
            $price = $price*$input['quantity'];
        }
        $requestRefund = [
            'authUser' => auth()->user()->id,
            'amount' => $price,
            'id' => $order['transaction_id']
        ];
        $request = $this->ewalletService->payReturn($requestRefund);
        if (!isset($request['success'])) {
            return $request;
        }
        if ($request['success'] === true) {
            $returned['amount_returned'] = $request['refund']['amount'];
            $returned['auth_user_id'] = $request['refund']['authUserId'];
            $returned['transaction_id'] = $request['refund']['transactionId'];
            $returned['type'] = $request['refund']['type'];
            $returned->update();
        }
        return $request;
    }

    public function allReasons()
    {
        return ReturnReason::all();
    }

    public function quantityReturned($orderline_id)
    {
        $lines = Returnline::where('orderline_id', $orderline_id)->get();
        $lineQuantity = 0;
        foreach ($lines as $line) {
            $lineQuantity = $lineQuantity + $line->quantity;
        }
        return $lineQuantity;
    }

    public function returnedById($id)
    {
        return ReturnModel::with(['lines.reason', 'order.storeOwner'])
        ->join('users', 'returns.user_id', 'users.id')
        ->select('returns.*', 'users.first_name', 'users.last_name', 'users.email', 'returns.id as id', 'returns.created_at as created_at')
        ->where('returns.id', $id)
        ->first();
    }

    public function refund($input)
    {
        $returnAmount = 0;
        foreach ($input['lines'] as $line) {
            if ($line['inventoryQuantity'] > $line['quantity']) {
                return ['error' => true, 'message' => 'Your quantity is too high'];
            }
            if ($line['returnAmount'] > $line['price'] * $line['quantity']) {
                return ['error' => true, 'message' => 'Your return amount is too high'];
            }
        }
        $order = Order::where('id', $input['order_id'])->with('lines')->first();
        // check to make sure that they are not returning more then purchased.
        foreach ($order->lines as $line) {
            $orderlineQuantity = $line->quantity;
            $returnedQuantity = $this->quantityReturned($line->id);
            if ($returnedQuantity > $orderlineQuantity) {
                return ['error' => true, 'message' => 'Your quantity is too high'];
            }
            //Do a check to see how much money has been returned.
        }
        // add back items to the inventory they came from.
        $inventory = $this->inventoryRepo->addReturnedInventory($input['lines'], $order->store_owner_user_id);

        //remove items that are returnd from a rep store.
        $this->inventoryRepo->removeReturnedInventory($input['lines'], $order->customer_id);

        foreach ($input['lines'] as $line) {
            $returnAmount += $line['returnAmount'];
            $this->returnHistoryRepo->create(3, $line);
            $returnLine = Returnline::where('id', $line['id'])->first();
            $returnLine->return_amount = $line['returnAmount'];
            $returnLine->update();
        }
        // Amount to be returned.
        $returnTransaction = returnTransaction::create([
            'return_id' => $input['id'],
            'auth_user_id' => auth()->id(),
            'amount' => $returnAmount,
            'transaction_id' =>  '',
            'type' => 'refund'
        ]);

        $job = (new CancelOrder($order, $input))->delay(Carbon::now()->addSeconds(3));
        dispatch($job);
        $returns = ReturnModel::where('id', $input['id'])->first();
        $returns->update(['return_status_id' => 3]);
        // Create order to get tax about
        // Call job for refund.
        return $returnAmount;
    }
}
