<?php

namespace App\Repositories\EloquentV0;

use App\Subscription;
use App\User;
use App\Plan;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionRepository extends Repository
{
    public function __construct()
    {
        $this->paramsTable = [
            'search_term' => function (Builder $query, string $value, array $params) : void {
                if (!empty($value)) {
                    $query->where(function ($query) use ($value) {
                        $query->where('users.id', 'LIKE', '%'.$value.'%')
                            ->orWhere('users.pid', 'LIKE', '%'.$value.'%')
                            ->orWhere('subscription_user.pid', 'LIKE', '%'.$value.'%')
                            ->orWhereRaw("(CONCAT(users.first_name, ' ', users.last_name) LIKE '%".$value."%')");
                    });
                }
            },
            'sort_by' => function (Builder $query, string $value, array $params) : void {
                $inOrder = 'ASC';
                if (strpos($value, '-') === 0) {
                    $inOrder = 'DESC';
                    $value = str_replace('-', '', $value);
                }
                $query->orderBy($value, $inOrder);
            },
            'start_date' => function (Builder $query, string $value, array $params) : void {
                $query->where('subscription_user.'.$params['date_column'], '>', $value);
            },
            'end_date' => function (Builder $query, string $value, array $params) : void {
                $query->where('subscription_user.'.$params['date_column'], '<', $value);
            },
            'seller_type_id' => function (Builder $query, string $value, array $params) : void {
                $query->where('users.seller_type_id', $value);
            },
        ]; 
    }

    public function find(array $request, string $pid) : ?Subscription
    {
        $subscription = Subscription::select(Subscription::$selects)
            ->where('subscription_user.pid', $pid)
            ->join('users', 'users.id', '=', 'subscription_user.user_id');
        $this->getParams($subscription, $request);
        return $subscription->first();
    }

    public function findByUser(array $request, string $pid) : ?Subscription
    {
        $subscription = Subscription::select(Subscription::$selects)
            ->where('subscription_user.user_pid', $pid)
            ->join('users', 'users.id', '=', 'subscription_user.user_id');
        $this->getParams($subscription, $request);
        return $subscription->first();
    }

    public function index(array $request) : LengthAwarePaginator
    {
        $subscriptions = Subscription::select(Subscription::$selects)
            ->join('users', 'users.id', '=', 'subscription_user.user_id');
        $this->getParams($subscriptions, $request);
        $subscriptions->distinct();
        return $subscriptions->paginate($request['per_page']);
    }

    public function create(User $user, Plan $plan)
    {
        $subscription = Subscription::create([
            'subscription_id' => $plan->id,
            'user_id' => $user->id,
            'user_pid' => $user->pid,
            'auto_renew' => $plan->renewable,
            'ends_at' => Carbon::now('UTC')->addMonth()->addDays($plan->free_trial_time),
            'subscription_price' => $plan->plan_price,
        ]);
        $user->seller_type_id = $plan->seller_type_id;
        $user->save();
        return $subscription;
    }

    public function update(array $request, string $pid) : Subscription
    {
        $model = new Subscription;
        $subscription = Subscription::where('pid', $pid)->update(
            array_only($request, $model->getFillable())
        );
        $subscription = Subscription::where('pid', $pid)->first();
        return $subscription;
    }
}
