<?php namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\SubscriptionRepository;

class ReportsController extends Controller
{
    /* @var \App\Repositories\Eloquent\SubscriptionRepository */
    protected $subscriptionRepo;

    public function __construct(SubscriptionRepository $subscriptionRepo)
    {
        $this->subscriptionRepo = $subscriptionRepo;
    }

    public function isAffiliate()
    {
        $affiliate = $this->subscriptionRepo->isAffiliate();
        if ($affiliate) {
            $affiliate = 'true';
        } else {
            $affiliate = 'false';
        }
        return $affiliate;
    }
}
