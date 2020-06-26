<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\UserSettingsRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class SalesController extends Controller
{
    public function __construct(
        OrderRepository $orderRepo,
        UserSettingsRepository $userSettingsRepo
    ) {
        $this->orderRepo = $orderRepo;
        $this->userSettingsRepo = $userSettingsRepo;
    }

    /**
    * This gets all the sales info.
    *
    * @return \Illuminate\Http\Response
    */
    public function getIndex(Request $request)
    {
        $rules = [
            'start_date' => 'required',
            'end_date' => 'required',
            'order' => 'required',
            'column' => 'required',
            'per_page' => 'required'
        ];

        $this->validate($request, $rules);
        $request = $request->all();
        if (auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            $user_id = config('site.apex_user_id');
        } else {
            $user_id = auth()->id();
        }
        $timezone = $this->userSettingsRepo->getUserTimeZone($user_id);
        if (isset($request['start_date']) and $request['start_date'] != 'Invalid date') {
            $request['start_date'] = Carbon::parse($request['start_date'], $timezone)->startOfDay()->setTimezone('UTC');
        } else {
            $request['start_date'] = Carbon::now()->startOfCentury()->setTimezone('UTC');
        }
        if (isset($request['end_date']) and $request['end_date'] != 'Invalid date') {
            $request['end_date'] = Carbon::parse($request['end_date'], $timezone)->endOfDay()->setTimezone('UTC');
        } else {
            $request['end_date'] = Carbon::now()->endOfCentury()->setTimezone('UTC');
        }

        $sales = $this->orderRepo->sales($request, $user_id);

        return response()->json($sales, HTTP_SUCCESS);
    }

    public function getRepSales()
    {
        $sales = $this->orderRepo->repSales(request()->all());
        return response()->json($sales, HTTP_SUCCESS);
    }
}
