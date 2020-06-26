<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscriptions\IndexRequest;
use App\Http\Requests\Subscriptions\CreateRequest;
use App\Http\Requests\Subscriptions\FindRequest;
use App\Http\Requests\Subscriptions\UpdateRequest;
use App\Http\Requests\Subscriptions\DeleteRequest;
use App\Models\Subscription;
use App\Repositories\Eloquent\V0\SubscriptionRepository;
use App\Repositories\Eloquent\V0\PlanRepository;
use App\Services\Interfaces\V0\OrderServiceInterface;
use App\Services\Interfaces\V0\UserServiceInterface;
use App\Services\V0\OrderService;
use App\Services\V0\SubscriptionService;
use Carbon\Carbon;
use Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(OrderServiceInterface $OrderService)
    {
        $this->OrderService = $OrderService;
        $this->SubscriptionRepo = new SubscriptionRepository();
    }

    public function index(Request $request) : JsonResponse
    {
        $this->validateRequest(new IndexRequest, $request);
        $request['per_page'] = $request->input('per_page', 100);
        $filter = $request->input('filter');
        if ($filter !== 'disabled' && $filter !== 'all') {
            $request['show_disabled'] = $request->input('show_disabled', false);
        }
        $subscriptions = $this->SubscriptionRepo->index($request->all());
        foreach ($subscriptions as $subscription) {
            SubscriptionService::calculateTotals($subscription);
        }
        return response()->json($subscriptions, 200);
    }

    public function find(Request $request, string $pid) : JsonResponse
    {
        $this->validateRequest(new FindRequest, $request);
        $subscription = $this->SubscriptionRepo->find($request->all(), $pid);
        $request['show_disabled'] = $request->input('show_disabled', false);
        if (!$subscription) {
            return response()->json(['error' => 'Unable to find subscription.'], 404);
        }
        SubscriptionService::calculateTotals($subscription);
        return response()->json($subscription, 200);
    }

    public function create(Request $request) : JsonResponse
    {
        $this->validateRequest(new CreateRequest, $request);
        if ($request->has('disable')) {
            if (filter_var($request->input('disable'), FILTER_VALIDATE_BOOLEAN)) {
                $request['disabled_at'] = Carbon::now('UTC');
            } else {
                $request['disabled_at'] = null;
            }
        }
        $cart = $this->OrderService->getCart($request->input('cart_pid'));
        foreach ($cart['lines'] as $line) {
            if (!isset($line['inventory_owner_pid'])) {
                $line['inventory_owner_pid'] = $cart['seller_pid'];
            }
        }
        $inputs = array_merge($cart, $request->all());
        if ($request->has('plan_pid')) {
            $PlanRepo = new PlanRepository();
            $plan = $PlanRepo->find([], $request->input('plan_pid'));
            $inputs = array_merge($inputs, [
                'duration' => $plan->duration,
                'free_shipping' => $plan->free_shipping,
                'frequency' => $plan->frequency,
                'discounts' => $plan->discounts,
                'autoship_plan_id' => $plan->id,
            ]);
        }
        $subscription = $this->SubscriptionRepo->updateOrCreate($inputs);
        SubscriptionService::calculateTotals($subscription);
        $this->OrderService->clearCart($cart);
        return response()->json($subscription, 200);
    }

    public function update(Request $request, string $pid) : JsonResponse
    {
        $this->validateRequest(new UpdateRequest, $request, $pid);
        if ($request->has('disable')) {
            if (filter_var($request->input('disable'), FILTER_VALIDATE_BOOLEAN)) {
                $request['disabled_at'] = Carbon::now('UTC');
            } else {
                $request['disabled_at'] = null;
            }
        }
        $subscription = $this->SubscriptionRepo->updateOrCreate($request->all(), $pid);
        SubscriptionService::calculateTotals($subscription);
        return response()->json($subscription, 200);
    }

    public function delete(Request $request, string $pid) : JsonResponse
    {
        $this->validateRequest(new DeleteRequest, $request, $pid);
        $deleted = $this->SubscriptionRepo->delete($pid);
        if ($deleted) {
            return response()->json('Success', 200);
        }
        return response()->json(['error' => 'Unable to delete.'], 422);
    }

    public function processSubscription(Request $request, $pid)
    {
        $UserService = app()->make(UserServiceInterface::class);
        $subscription = $this->SubscriptionRepo->find([
            'expands' => ['lines', 'cycle_attempts'],
            'show_disabled' => false
        ], $pid);
        if (!isset($subscription)) {
            abort(404, 'Subscription Not Found');
        }
        if ($subscription->next_billing_at > Carbon::now()) {
            abort(500, 'Subscription cannot be processed until after '.$subscription->next_billing_at);
        }
        $buyer = $UserService->getBuyer($subscription->buyer_pid, $subscription);
        $checkout = $this->OrderService->createCheckout($subscription, $buyer);
        $order = $this->OrderService->checkout($subscription, $buyer, $checkout);
        SubscriptionService::createAttempt($subscription, 'success', 'success', (isset($order->order->pid) ? $order->order->pid : null));
        $order->subscription = $subscription;
        return response()->json($order, 200);
    }
}
