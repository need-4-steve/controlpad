<?php

namespace App\Repositories\Eloquent\V0;

use App\Models\SubscriptionLine;
use App\Repositories\Eloquent\V0\Repository;
use App\Repositories\Eloquent\V0\SubscriptionRepository;
use App\Repositories\Interfaces\SubscriptionLineInterface;
use Illuminate\Database\Eloquent\Builder;
use DB;

class SubscriptionLineRepository extends Repository implements SubscriptionLineInterface
{
    public function __construct()
    {
        $this->paramsTable = [];
        $this->SubscriptionRepo = new SubscriptionRepository();
    }

    public function find($request, $pid) : ?SubscriptionLine
    {
        $subscriptionLine = SubscriptionLine::select(SubscriptionLine::$selects)
            ->addSelect(DB::raw("CASE WHEN autoship_subscription_lines.disabled_at IS NULL THEN 0 ELSE 1 END AS disable"))
            ->where('autoship_subscription_lines.pid', $pid);
        $this->getParams($subscriptionLine, $request);
        return $subscriptionLine->first();
    }

    public function updateOrCreate(array $request, $pid = null) : SubscriptionLine
    {
        if (isset($request['subscription_pid'])) {
            $subscription = $this->SubscriptionRepo->find([], $request['subscription_pid']);
            $request['autoship_subscription_id'] = $subscription->id;
            $request['inventory_owner_pid'] = $subscription->seller_pid;
        }
        $model = new SubscriptionLine;
        $subscriptionLine = SubscriptionLine::updateOrCreate(
            [
                'pid' => $pid
            ],
            array_only($request, $model->getFillable())
        );
        $subscriptionLine->disable = isset($subscriptionLines->disabled_at) ? true : false;
        if (isset($subscription)) {
            $this->SubscriptionRepo->determineDiscount($subscription);
        }
        return $subscriptionLine;
    }

    public function delete(string $pid) : bool
    {
        $subscriptionLine = SubscriptionLine::where('pid', $pid)->with('subscription')->first();
        $subscription = $subscriptionLine->subscription;
        $subscriptionLine->delete();
        $this->SubscriptionRepo->determineDiscount($subscription);
        return true;
    }
}
