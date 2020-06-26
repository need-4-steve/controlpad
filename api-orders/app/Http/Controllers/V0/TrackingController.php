<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Repositories\EloquentV0\TrackingRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Tracking;
use Carbon\Carbon;

class TrackingController extends Controller
{
    private $trackingRepo;

    public function __construct()
    {
        $this->trackingRepo = new TrackingRepository;
    }

    public function create(Request $request) : JsonResponse
    {
        $rules = Tracking::$rules;
        $this->validate($request, $rules);
        $inputs = $request->only(Tracking::$updateFields);
        if (isset($inputs['shipped_at'])) {
            $inputs['shipped_at'] = Carbon::parse($inputs['shipped_at'])->toDateTimeString();
        }
        $tracking = $this->trackingRepo->create($request->only(Tracking::$updateFields));
        return response()->json('Success', 200);
    }

    public function delete($id, Request $request) : JsonResponse
    {
        $deleted = $this->trackingRepo->delete($id);
        if (!$deleted) {
            return response()->json(['error' => 'Unable to delete. Could not find a tracking number with an ID of ' . $id], 404);
        }
        return response()->json('Success');
    }
}
