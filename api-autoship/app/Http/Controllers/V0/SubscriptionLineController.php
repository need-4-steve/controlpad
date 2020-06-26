<?php

namespace App\Http\Controllers\V0;

use App\Models\SubscriptionLine;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriptionLines\FindRequest;
use App\Http\Requests\SubscriptionLines\CreateRequest;
use App\Http\Requests\SubscriptionLines\UpdateRequest;
use App\Http\Requests\SubscriptionLines\DeleteRequest;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\V0\SubscriptionLineRepository;
use App\Services\Interfaces\V0\UserServiceInterface;
use Illuminate\Http\JsonResponse;

class SubscriptionLineController extends Controller
{
    public function __construct()
    {
        $this->SubscriptionLineRepo = new SubscriptionLineRepository();
    }

    public function find(Request $request, string $pid) : JsonResponse
    {
        $this->validateRequest(new FindRequest, $request);
        $subscriptionLine = $this->SubscriptionLineRepo->find($request->all(), $pid);
        if (!$subscriptionLine) {
            return response()->json(['error' => 'Unable to find subscription line.'], 404);
        }
        return response()->json($subscriptionLine, 200);
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
        $subscriptionLine = $this->SubscriptionLineRepo->updateOrCreate($request->all());
        return response()->json($subscriptionLine, 200);
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
        $subscriptionLine = $this->SubscriptionLineRepo->updateOrCreate($request->all(), $pid);
        return response()->json($subscriptionLine, 200);
    }

    public function delete(Request $request, string $pid) : JsonResponse
    {
        $this->validateRequest(new DeleteRequest, $request, $pid);
        $deleted = $this->SubscriptionLineRepo->delete($pid);
        if ($deleted) {
            return response()->json('Success', 200);
        }
        return response()->json(['error' => 'Unable to delete.'], 422);
    }
}
