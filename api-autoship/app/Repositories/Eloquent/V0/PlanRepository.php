<?php

namespace App\Repositories\Eloquent\V0;

use App\Models\Plan;
use App\Repositories\Eloquent\V0\Repository;
use App\Repositories\Interfaces\PlanInterface;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class PlanRepository extends Repository implements PlanInterface
{
    public function __construct()
    {
        $this->paramsTable = [
            'expands' => function (Builder $query, array $expands, array $params) : void {
                foreach ($expands as $expand) {
                    try {
                        $this->expandsTable[$expand]($query, $params);
                    } catch (\Exception $e) {
                    }
                }
            },
            'show_disabled' => function (Builder $query, bool $value, array $params) : void {
                if (!$value) {
                    $query->whereNull('disabled_at');
                }
            },
            'search_term' => function (Builder $query, string $value, array $params) : void {
                if ($value !== "") {
                    $query->search($value);
                    $query->addSelect('relevance');
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
            'visibilities' => function (Builder $query, array $value, array $params) : void {
                $query->join('autoship_plan_visibility', 'autoship_plan_visibility.autoship_plan_id', '=', 'autoship_plans.id')
                    ->join('autoship_visibilities', 'autoship_visibilities.id', '=', 'autoship_plan_visibility.visibility_id');
                // checks to see if the array of $value has numeric values
                if (count($value) === count(array_filter($value, 'is_numeric'))) {
                    $query->whereIn('autoship_visibilities.id', $value);
                } else {
                    $query->whereIn('autoship_visibilities.name', $value);
                }
            },
        ];
        $this->expandsTable = [
            'visibilities' => function (Builder $query, array $params) {
                $query->with('visibilities');
            },
        ];
    }

    public function find(array $request, string $pid) : ?Plan
    {
        $plan = Plan::select(Plan::$selects)
            ->addSelect(DB::raw("CASE WHEN autoship_plans.disabled_at IS NULL THEN 0 ELSE 1 END AS disable"))
            ->where('autoship_plans.pid', $pid);
        $this->getParams($plan, $request);
        return $plan->first();
    }

    public function index(array $request) : LengthAwarePaginator
    {
        $plans = Plan::select(Plan::$selects)
            ->addSelect(DB::raw("CASE WHEN autoship_plans.disabled_at IS NULL THEN 0 ELSE 1 END AS disable"));
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
        if (isset($request['visibilities'])) {
            $plan->visibilities()->sync(array_pluck($request['visibilities'], 'id'));
            $plan->load(['visibilities']);
        }
        $plan->disable = isset($plan->disabled_at) ? true : false;
        return $plan;
    }

    public function delete(string $pid) : bool
    {
        return Plan::where('pid', $pid)->delete();
    }
}
