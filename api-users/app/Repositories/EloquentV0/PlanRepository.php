<?php

namespace App\Repositories\EloquentV0;

use App\Plan;
use App\Price;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PlanRepository extends Repository
{
    public function __construct()
    {
        $this->paramsTable = [
            'search_term' => function (Builder $query, string $value, array $params) : void {
                $query->where(function ($query) use ($value) {
                    $query->where('subscriptions.title', 'LIKE', '%'.$value.'%')
                        ->orWhere('subscriptions.description', 'LIKE', '%'.$value.'%');
                });
            },
            'sort_by' => function (Builder $query, string $value, array $params) : void {
                $inOrder = 'ASC';
                if (strpos($value, '-') === 0) {
                    $inOrder = 'DESC';
                    $value = str_replace('-', '', $value);
                }
                $query->orderBy($value, $inOrder);
            },
            'sign_up' => function (Builder $query, string $value, array $params) : void {
                if ($value) {
                    $query->where('on_sign_up', true);
                }
            },
        ];
    }

    public function find(array $request, string $pid) : ?Plan
    {
        $plan = Plan::select(Plan::$selects)
            ->where('subscriptions.pid', $pid);
        $this->getParams($plan, $request);
        return $plan->first();
    }

    public function index(array $request) : LengthAwarePaginator
    {
        $plans = Plan::select(Plan::$selects);
        $this->getParams($plans, $request);
        $plans->distinct();
        return $plans->paginate($request['per_page']);
    }

    public function updateOrCreate(array $request, $pid = null) : Plan
    {
        $model = new Plan;
        $plan = Plan::updateOrCreate(
            [
                'pid' => $pid
            ],
            array_only($request, $model->getFillable())
        );
        if (isset($request['plan_price'])) {
            // create price in table
            Price::updateOrCreate(
                [
                    'priceable_id' => $plan->id,
                    'priceable_type' => 'App\Models\Subscription',
                    'price_type_id' => 1
                ],
                [
                    'price' => $request['plan_price'],
                ]
            );
        }
        return $plan;
    }

    public function delete(string $pid) : bool
    {
        return Plan::where('pid', $pid)->delete();
    }
}
