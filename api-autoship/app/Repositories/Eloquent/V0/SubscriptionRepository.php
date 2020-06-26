<?php

namespace App\Repositories\Eloquent\V0;

use App\Models\Subscription;
use App\Models\SubscriptionLine;
use App\Repositories\Eloquent\V0\Repository;
use App\Repositories\Interfaces\SubscriptionInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use DB;
use Carbon\Carbon;

class SubscriptionRepository extends Repository implements SubscriptionInterface
{
    public function __construct($connection = 'mysql')
    {
        $this->connection = $connection;
        $this->groupBy = [];
        $this->paramsTable = [
            'buyer_pid' => function (Builder $query, string $value, array $params) : void {
                $query->where('buyer_pid', $value);
            },
            'end_date' => function (Builder $query, string $value, array $params) : void {
                $query->where('autoship_subscriptions.'.$params['date_column'], '<', $value);
            },
            'expands' => function (Builder $query, array $expands, array $params) : void {
                foreach ($expands as $expand) {
                    try {
                        $this->expandsTable[$expand]($query, $params);
                    } catch (\Exception $e) {
                    }
                }
            },
            'filter' => function (Builder $query, string $value, array $params) : void {
                switch ($value) {
                    case 'active':
                        $query->where('autoship_attempts.status', 'success');
                        // expand last attempt if it isn't in the query parameters
                        if (!isset($params['expands']) || !in_array('last_attempt', $request['expands'])) {
                            $this->paramsTable['expands']($query, ['last_attempt'], $params);
                        }
                        break;
                    case 'failed':
                        $query->where('autoship_attempts.status', '!=', 'success');
                        // expand last attempt if it isn't in the query parameters
                        if (!isset($params['expands']) || !in_array('last_attempt', $request['expands'])) {
                            $this->paramsTable['expands']($query, ['last_attempt'], $params);
                        }
                        break;
                    case 'renewing_soon':
                        $query->where('next_billing_at', '<', Carbon::today()->addDays(7)->endOfDay());
                        break;
                    case 'disabled':
                        $query->whereNotNull('disabled_at');
                        break;
                }
            },
            'search_term' => function (Builder $query, string $value, array $params) : void {
                if ($value !== "") {
                    $query->search($value);
                    $query->addSelect('relevance');
                }
            },
            'show_disabled' => function (Builder $query, bool $value, array $params) : void {
                if (!$value) {
                    $query->whereNull('disabled_at');
                }
            },
            'sort_by' => function (Builder $query, string $value, array $params) {
                $inOrder = 'ASC';
                if (strpos($value, '-') === 0) {
                    $inOrder = 'DESC';
                    $value = str_replace('-', '', $value);
                }
                return $query->orderBy($value, $inOrder);
            },
            'start_date' => function (Builder $query, string $value, array $params) : void {
                $query->where('autoship_subscriptions.'.$params['date_column'], '>', $value);
            },
        ];
        $this->expandsTable = [
            'attempts' => function (Builder $query, array $params) {
                $query->with('attempts');
            },
            'cycle_attempts' => function (Builder $query, array $params) {
                $query->leftJoin('autoship_attempts as cycle', function ($join) {
                    $join->on('cycle.autoship_subscription_id', '=', 'autoship_subscriptions.id')
                        ->where('cycle.subscription_cycle', '=', DB::raw('autoship_subscriptions.cycle'))
                        ->where('cycle.status', '=', 'failure');
                });
                $query->addSelect(DB::raw('count(cycle.subscription_cycle) as cycle_attempts'));
                array_unshift($this->groupBy, 'autoship_subscriptions.id'); // needs to be at the beginning of group by
            },
            'last_attempt' => function (Builder $query, array $params) {
                $query->leftJoin('autoship_attempts', function ($join) {
                    $join->on('autoship_attempts.autoship_subscription_id', '=', 'autoship_subscriptions.id')
                         ->on('autoship_attempts.id', '=', DB::raw("(SELECT max(autoship_attempts.id) from autoship_attempts WHERE autoship_attempts.autoship_subscription_id = autoship_subscriptions.id)"));
                })->addSelect([
                    'autoship_attempts.description as last_attempt_description',
                    'autoship_attempts.status as last_attempt_status',
                    'autoship_attempts.created_at as last_attempted_at',
                    'autoship_attempts.order_pid as last_attempt_order_pid'
                ]);
                if (in_array('cycle_attempts', $params['expands'])) {
                    array_push($this->groupBy, 'autoship_attempts.id'); // needs to be at the end of group by
                }
            },
            'lines' => function (Builder $query, array $params) {
                $query->with(['lines' => function ($query) {
                    $query->select(SubscriptionLine::$selects);
                }]);
            },
        ];
    }

    public function find(array $request, string $pid) : ?Subscription
    {
        $subscription = Subscription::on($this->connection)->select(Subscription::$selects)
            ->addSelect(DB::raw("CASE WHEN autoship_subscriptions.disabled_at IS NULL THEN 0 ELSE 1 END AS disable"))
            ->where('autoship_subscriptions.pid', $pid);
        $this->getParams($subscription, $request);
        if (!empty($this->groupBy)) {
            $subscription->groupBy($this->groupBy);
        }
        return $subscription->first();
    }

    public function index(array $request) : LengthAwarePaginator
    {
        $subscriptions = Subscription::on($this->connection)->select(Subscription::$selects)
            ->addSelect(DB::raw("CASE WHEN autoship_subscriptions.disabled_at IS NULL THEN 0 ELSE 1 END AS disable"));
        $this->getParams($subscriptions, $request);
        $subscriptions->distinct();
        if (!empty($this->groupBy)) {
            $subscriptions->groupBy($this->groupBy);
        }
        return $subscriptions->paginate($request['per_page']);
    }

    public function updateOrCreate(array $request, $pid = null) : Subscription
    {
        $model = new Subscription;
        $subscription = Subscription::on($this->connection)->updateOrCreate(
            [
                'pid' => $pid
            ],
            array_only($request, $model->getFillable())
        );
        DB::beginTransaction();
        if (isset($request['lines']) && is_null($pid)) {
            foreach ($request['lines'] as $line) {
                $subscription->lines()->create($line);
            }
        }
        $subscription->load('lines');
        DB::commit();
        $subscription = $this->determineDiscount($subscription);
        $subscription->disable = isset($subscriptionLines->disabled_at) ? true : false;
        return $subscription;
    }

    public function delete(string $pid) : bool
    {
        $subscription = Subscription::on($this->connection)->where('pid', $pid)->with('lines')->first();
        // Delete subscription lines along with subscription
        foreach ($subscription->lines as $line) {
            $line->delete();
        }
        return $subscription->delete();
    }

    public function determineDiscount(Subscription $subscription) : Subscription
    {
        $itemCount = 0;
        $percentDiscount = 0;
        // Load the subscription lines to ensure correct quantity count
        $subscription->load('lines');
        // Add up the item count
        if (!is_null($subscription->lines)) {
            foreach ($subscription->lines as $line) {
                $itemCount += $line->quantity;
            }
        }
        // Find the correct percent discount according to how many items are in the subscription
        if (!is_null($subscription->discounts)) {
            foreach ($subscription->discounts as $discount) {
                if ($itemCount >= $discount->min_quantity) {
                    $percentDiscount = $discount->percent;
                }
            }
        }
        // Only need to save it if the discount has changed
        if ($percentDiscount !== $subscription->percent_discount) {
            $subscription->update(['percent_discount' => $percentDiscount]);
        }
        return $subscription;
    }
}
