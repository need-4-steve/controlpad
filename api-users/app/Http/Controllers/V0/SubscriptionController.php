<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Repositories\EloquentV0\SubscriptionRepository;
use App\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected $subscriptionRepo;

    public function __construct()
    {
        $this->subscriptionRepo = new SubscriptionRepository;
    }

    public function index(Request $request)
    {
        $this->validate($request, Subscription::$indexRules);
        $request['per_page'] = $request->input('per_page', 100);
        $subscriptions = $this->subscriptionRepo->index($request->all());
        return response()->json($subscriptions, 200);
    }

    public function find(Request $request, $id)
    {
        $subscription = $this->subscriptionRepo->find($request->all(), $id);
        if (!$subscription) {
            return response()->json(['error' => 'Unable to find subscription'], 404);
        }
        return response()->json($subscription, 200);
    }

    public function findByUser(Request $request, $pid)
    {
        $subscription = $this->subscriptionRepo->findByUser($request->all(), $pid);
        if (!$subscription) {
            return response()->json(['error' => 'Unable to find subscription'], 404);
        }
        return response()->json($subscription, 200);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, Subscription::$updateRules);
        $subscription = $this->subscriptionRepo->update($request->all(), $id);
        return response()->json($subscription, 200);
    }
}
