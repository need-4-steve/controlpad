<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\InventoryServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Order;
use Carbon\Carbon;

class OrdersController extends Controller
{
    private $orderRepo;
    private $inventoryService;

    private $sortableColumns = [
        'receipt_id',
        'store_owner_user_id',
        'buyer_first_name',
        'buyer_last_name',
        'total_price',
        'total_tax',
        'created_at',
        'updated_at',
        'status',
        'customer_id',
        'total_discount',
        'paid_at',
    ];

    public function __construct(
        OrderRepositoryInterface $orderRepo,
        InventoryServiceInterface $inventoryService
    ) {
        $this->orderRepo = $orderRepo;
        $this->inventoryService = $inventoryService;
    }

    private function getCommonRules() : array
    {
        return [
            'per_page' => 'sometimes|numeric|min:1|max:500',
            'order' => 'sometimes|string',
            'page' => 'sometimes|numeric',
            'orderlines' => 'sometimes|boolean',
            'start_date' => 'required_with:end_date|date',
            'end_date' => 'required_with:start_date|date',
            'sort_by' => 'sometimes|string|in:' . implode(',', $this->sortableColumns),
            'in_order' => 'sometimes|string|in:asc,desc',
            'date_type' => 'sometimes|string|in:updated_at,created_at'
        ];
    }

    private function determineSortByOrder(Request $request) : Request
    {
        if ($request->get('sort_by') && strpos($request->get('sort_by'), '-') === 0) {
            $request->merge(['sort_by' => str_replace('-', '', $request['sort_by'])]);
            $request->merge(['in_order' => 'desc']);
        }
        return $request;
    }

    public function index(Request $request) : JsonResponse
    {
        $request = $this->determineSortByOrder($request);
        $params = $request->all();
        $rules = $this->getCommonRules();
        $rules['seller_id'] = 'sometimes|integer';
        $rules['buyer_id'] = 'sometimes|integer';
        $rules['type_id'] = 'sometimes|array';
        $rules['event_id'] = 'sometimes|integer';
        if (!$request->user->hasRole(['Superadmin', 'Admin'])
            && isset($params['buyer_id'])
            && intval($params['buyer_id']) !== intval($request->user->id)
            || !$request->user->hasRole(['Superadmin', 'Admin'])
            && !isset($params['buyer_id'])) {
            $params['seller_id'] = $request->user->id;
        }
        $rules['seller_pid'] = 'sometimes|string';
        $rules['buyer_pid'] = 'sometimes|string';
        $request = $this->determineSortByOrder($request);
        $this->validate($request, $rules);
        $params = $this->reformatDateStrings($params);
        return response()->json($this->orderRepo->index($params));
    }

    public function orderById(Request $request, $id) : JsonResponse
    {
        $this->validate($request, [
            'orderlines' => 'sometimes|boolean',
            'tracking' => 'sometimes|boolean'
        ]);
        $order = $this->orderRepo->orderById($id, $request->only('orderlines', 'tracking'));
        if (!$order) {
            return response()->json('If your trying to access an order, you may have an invalid order ID.', 404);
        }
        return response()->json($order, 200);
    }

    public function byReceiptId(Request $request, $receiptId)
    {
        $this->validate($request, [
            'orderlines' => 'sometimes|boolean',
            'tracking' => 'sometimes|boolean'
        ]);
        $order = $this->orderRepo->orderByReceiptId($receiptId, $request->only('orderlines', 'tracking'));
        if (!$order) {
            return response()->json('If your trying to access an order, you may have an invalid receipt ID.', 404);
        }
        return response()->json($order, 200);
    }

    public function ordersBySellerId($id, Request $request) : JsonResponse
    {
        $rules = $this->getCommonRules();
        $request = $this->determineSortByOrder($request);
        $this->validate($request, $rules);
        $params = $request->all();
        $params = $this->reformatDateStrings($params);
        return response()->json($this->orderRepo->ordersByCustomerId($id, $params));
    }

    public function ordersByBuyerId($id, Request $request) : JsonResponse
    {
        $rules = $this->getCommonRules();
        $request = $this->determineSortByOrder($request);
        $this->validate($request, $rules);
        $params = $request->all();
        $params = $this->reformatDateStrings($params);
        return response()->json($this->orderRepo->ordersByUserId($id, $params));
    }

    public function edit($id, Request $request) : JsonResponse
    {
        if (count($request->all()) < 1) {
            return response()->json(['error' => ['The request is empty.']], 422);
        }
        $rules = Order::$updateRules;
        $this->validate($request, $rules);
        $order = Order::where('id', $id)->first();
        if (!$order) {
            app('log')->error('Failed to find order for edit status.', ['order_id' => $id]);
            return response()->json(['error' => 'Could not find an order with an ID of ' . $id], 404);
        }
        if ($order->status !== $request['status']) {
            $updateCount = $this->orderRepo->edit($id, $request->only('status'));
            // Check update count in case somebody is hitting the api too fast with repeate requests
            // This has happened
            if ($updateCount > 0) {
                $oldStatus = $order->status;
                $order->status = $request['status'];
                if ($request['status'] === 'cancelled') {
                    $this->sendOrderCancelledEvent($order, $oldStatus, $request->user->orgId);
                } elseif ($request['status'] === 'fulfilled') {
                    $this->sendOrderFulfilledEvent($order, $oldStatus, $request->user->orgId);
                }
            }
        }

        return response()->json(true);
    }

    public function editOrders(Request $request) : JsonResponse
    {
        $updateRules = Order::$updateRules;
        $rules = [
            'status' => $updateRules['status'].'|required_with:*.id',
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ];
        $this->validate($request, $rules);
        $params = $request->only('ids', 'status');
        // Grab ids that will actually modify to minimize event overlap
        $modifyIds = Order::select('id')->whereIn('id', $params['ids'])->where('status', '!=', $params['status'])->get()->pluck('id');
        $updated = $this->orderRepo->editMultiple($modifyIds, $params['status']);
        if (!$updated) {
            return response()->json([
                'ids' => ['The system was unable to update the given orders. Please verify all order IDs are valid.']
            ], 422);
        }

        // Send event
        event(new \CPCommon\Events\GenericEvent(
            'order-batch-update',
            [
                'status' => $params['status'],
                'orderIds' => $modifyIds
            ],
            $request->user->orgId,
            0
        ));

        return response()->json('Success.', 200);
    }

    public function editShippingAddress(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'line_1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
        ]);
        $order = $this->orderRepo->orderById($id);
        if (!$order) {
            abort(404, 'Order not found');
        }
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        if (!$isAdmin && $request->user->pid != $order->seller_pid) {
            abort(403, 'Seller or admin only');
        }
        $order->shipping_address = $request->only(['name', 'line_1', 'line_2', 'city', 'state', 'zip']);
        $order->save();
        return $order;
    }

    public function acceptInventory(Request $request, $id)
    {
        $order = Order::where('id', '=', $id)->first();
        if ($order != null) {
            if (!$request->user->hasRole(['Admin', 'Superadmin']) && $order->buyer_pid != $request->user->pid) {
                abort(403, 'Buyer or admin only');
            }
        } else {
            abort(404, 'Order not found');
        }

        if ($order->inventory_received_at == null) {
            $updates = $this->inventoryService->confirmInventoryForOrder($order);
            if ($updates != null && !empty($updates)) {
                $order->inventory_received_at = Carbon::now()->toDateTimeString();
                $order->save();
                return $order;
            } else {
                abort(500, 'Failed to receive inventory, try again later');
            }
        } else {
            abort(400, 'Order inventory already marked as received');
        }
    }

    private function sendOrderFulfilledEvent($order, $oldStatus, $orgId)
    {
        $order->load(['lines', 'tracking']);
        event(new \CPCommon\Events\GenericEvent(
            'order-fulfilled',
            [
                'order' => $order,
                'oldStatus' => $oldStatus
            ],
            $orgId,
            0
        ));
    }

    private function sendOrderCancelledEvent($order, $oldStatus, $orgId)
    {
        $order->load(['lines']);
        event(new \CPCommon\Events\GenericEvent(
            'order-cancelled',
            [
                'order' => $order,
                'oldStatus' => $oldStatus
            ],
            $orgId,
            0
        ));
    }
}
