<?php namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Services\Commission\CommissionServiceInterface;
use App\Jobs\CancelCommEngineOrder;
use App\Jobs\SendCommEngineUser;
use App\Jobs\SendCommEngineOrder;
use Carbon\Carbon;
use DB;

class CommissionEngineController extends Controller
{
    public function __construct(
        CommissionServiceInterface $commissionService,
        OrderRepository $orderRepo,
        UserRepository $userRepo
    ) {
        $this->commissionService = $commissionService;
        $this->orderRepo = $orderRepo;
        $this->userRepo = $userRepo;
        $this->settings = app('globalSettings');
    }

    public function getCommissionEngineSettings()
    {
        $settings['comm_email'] = env('COMM_EMAIL');
        $settings['comm_system_id'] = env('COMM_SYSTEM_ID');
        $settings['comm_url'] = env('COMM_URL');
        if (env('COMM_API_KEY')) {
            $settings['comm_api_key'] = '***'.substr(env('COMM_API_KEY'), -4);
        }
        return response()->json($settings, 200);
    }

    public function backfill()
    {
        $users = $this->userRepo->getUsersByCommissionEngineStatus(1);
        DB::beginTransaction();
        foreach ($users as $user) {
            $user->update(['comm_engine_status_id' => 6]);
            $job = (new SendCommEngineUser($user, true))->delay(Carbon::now()->addSeconds(3));
            dispatch($job);
        }
        DB::commit();
        return response()->json(count($users).' users queued. ', HTTP_SUCCESS);
    }

    public function backfillUsers()
    {
        $users = $this->userRepo->getUsersByCommissionEngineStatus(1, true);
        $firstUser = $users->first();
        if (!isset($firstUser)) {
            return response()->json('No more users to queue.', HTTP_SUCCESS);
        }
        DB::beginTransaction();
        foreach ($users as $user) {
            $user->update(['comm_engine_status_id' => 6]);
            $job = (new SendCommEngineUser($user, true))->delay(Carbon::now()->addSeconds(3));
            dispatch($job);
        }
        DB::commit();
        return response()->json(count($users).' users queued. Users from '.$firstUser->id.' to '.$user->id, HTTP_SUCCESS);
    }

    public function backfillErrorUsers()
    {
        $commSetting = $this->settings->getGlobal('use_commission_engine', 'value');
        if ($commSetting == true) {
            $users = User::whereIn('comm_engine_status_id', [1, 4, 6])->get();
            DB::beginTransaction();
            foreach ($users as $user) {
                $job = new SendCommEngineUser($user, false);
                dispatch($job);
            }
            DB::commit();
        }
        return response()->json('success', HTTP_SUCCESS);
    }

    public function backfillErrorOrders()
    {
        $commSetting = $this->settings->getGlobal('use_commission_engine', 'value');
        if ($commSetting == true) {
            // update orders that were stuck in the queue to resend
            Order::where('comm_engine_status_id', 6)->where('created_at', '<', Carbon::now()->subHours(1))->update(['comm_engine_status_id' => 1]);
            $orders = $this->orderRepo->getOrdersByCommissionEngineStatus(4, false);
            $orders = $orders->where('created_at', '>', Carbon::now()->subDays(31));
            if (!isset($orders)) {
                return response()->json('No more orders to queue.', HTTP_SUCCESS);
            }
            DB::beginTransaction();
            foreach ($orders as $order) {
                $job = (new SendCommEngineOrder($order, false));
                dispatch($job);
            }
            DB::commit();
            return response()->json(count($orders).' orders queued.', HTTP_SUCCESS);
        }
        return response()->json('Commission Engine setting turned off', HTTP_SUCCESS);
    }

    public function backfillCancelledErroredOrders()
    {
        $commSetting = $this->settings->getGlobal('use_commission_engine', 'value');
        if ($commSetting == true) {
            $errorCancelledOrders = $this->orderRepo->getOrdersByCommissionEngineStatus(7, false, true);
            $errorCancelledOrders = $errorCancelledOrders->where('created_at', '>', Carbon::now()->subDays(31));
            $cancelledOrders = $this->orderRepo->getOrdersByCommissionEngineStatus(2, false, true);
            $cancelledOrders = $cancelledOrders->where('created_at', '>', Carbon::now()->subDays(31));
            $orders = $errorCancelledOrders->merge($cancelledOrders);
            if (!isset($orders)) {
                return response()->json('No more orders to cancel.', HTTP_SUCCESS);
            }
            DB::beginTransaction();
            foreach ($orders as $order) {
                $job = new CancelCommEngineOrder($order, false);
                dispatch($job);
            }
            DB::commit();
            return response()->json(count($orders).' orders queued.', HTTP_SUCCESS);
        }
        return response()->json('Commission Engine setting turned off', HTTP_SUCCESS);
    }

    public function getOrdersByCommissionStatus($statusName)
    {
        $statusId = $this->commissionService->getCommissionStatusId($statusName);
        $orders = $this->orderRepo->getOrdersByCommissionEngineStatus($statusId, true);
        return response()->json($orders, HTTP_SUCCESS);
    }

    public function getUsersByCommissionStatus($statusName)
    {
        $statusId = $this->commissionService->getCommissionStatusId($statusName);
        $users = $this->userRepo->getUsersByCommissionEngineStatus($statusId, true);
        return response()->json($users, HTTP_SUCCESS);
    }
}
