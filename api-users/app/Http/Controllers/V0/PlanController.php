<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Repositories\EloquentV0\PlanRepository;
use App\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    protected $planRepo;

    public function __construct()
    {
        $this->planRepo = new PlanRepository;
    }

    public function index(Request $request)
    {
        $this->validate($request, Plan::$indexRules);
        $request['per_page'] = $request->input('per_page', 100);
        if ($request->has('sign_up')) {
            $request['sign_up'] = filter_var($request->input('sign_up'), FILTER_VALIDATE_BOOLEAN);
        }
        $request['sign_up'] = $request->input('sign_up', false);
        $plans = $this->planRepo->index($request->all());
        return response()->json($plans, 200);
    }

    public function find(Request $request, $id)
    {
        $plan = $this->planRepo->find($request->all(), $id);
        if (!$plan) {
            return response()->json(['error' => 'Unable to find plan'], 404);
        }
        return response()->json($plan, 200);
    }

    public function create(Request $request)
    {
        $this->validate($request, Plan::$createRules);
        $plan = $this->planRepo->updateOrCreate($request->all());
        return response()->json($plan, 200);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, Plan::$updateRules);
        $plan = $this->planRepo->updateOrCreate($request->all(), $id);
        return response()->json($plan, 200);
    }

    public function delete(Request $request, $id)
    {
        $plan = $this->planRepo->delete($id);
        return response()->json('Success', 200);
    }
}
