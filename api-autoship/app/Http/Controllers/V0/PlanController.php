<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plans\CreateRequest;
use App\Http\Requests\Plans\DeleteRequest;
use App\Http\Requests\Plans\IndexRequest;
use App\Http\Requests\Plans\UpdateRequest;
use App\Repositories\Eloquent\V0\PlanRepository;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct()
    {
        $this->PlanRepo = new PlanRepository();
    }

    public function index(Request $request) : JsonResponse
    {
        $this->validateRequest(new IndexRequest, $request);
        $request['show_disabled'] = $request->input('show_disabled', false);
        $request['per_page'] = $request->input('per_page', 100);
        $plans = $this->PlanRepo->index($request->all());
        return response()->json($plans, 200);
    }

    public function find(Request $request, string $pid) : JsonResponse
    {
        $request['show_disabled'] = $request->input('show_disabled', false);
        $plan = $this->PlanRepo->find($request->all(), $pid);
        if (!$plan) {
            return response()->json(['error' => 'Unable to find plan.'], 404);
        }
        return response()->json($plan, 200);
    }

    public function create(Request $request) : JsonResponse
    {
        $this->validateRequest(new CreateRequest, $request);
        if ($request->has('disable')) {
            if (filter_var($request->input('disable'), FILTER_VALIDATE_BOOLEAN)) {
                $request['disabled_at'] = date('Y-m-d h:i:s');
            } else {
                $request['disabled_at'] = null;
            }
        }
        $plan = $this->PlanRepo->updateOrCreate($request->all());
        return response()->json($plan, 200);
    }

    public function update(Request $request, string $pid) : JsonResponse
    {
        $this->validateRequest(new UpdateRequest, $request, $pid);
        if ($request->has('disable')) {
            if (filter_var($request->input('disable'), FILTER_VALIDATE_BOOLEAN)) {
                $request['disabled_at'] = date('Y-m-d h:i:s');
            } else {
                $request['disabled_at'] = null;
            }
        }
        $plan = $this->PlanRepo->updateOrCreate($request->all(), $pid);
        return response()->json($plan, 200);
    }

    public function delete(Request $request, string $pid) : JsonResponse
    {
        $this->validateRequest(new DeleteRequest, $request, $pid);
        $deleted = $this->PlanRepo->delete($pid);
        if ($deleted) {
            return response()->json('Success', 200);
        }
        return response()->json(['error' => 'Unable to delete.'], 422);
    }
}
