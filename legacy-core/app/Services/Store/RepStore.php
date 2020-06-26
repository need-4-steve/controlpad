<?php

namespace App\Services\Store;

use App\Models\User;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\StoreSettingRepository;

/**
 * prepares data for a rep's store.
 */
class RepStore extends Store
{
    public $rep;
    public $affiliateDisplayName = null;
    protected $storeSettingRepo;

    public function __construct(
        array $request,
        User $rep,
        ?ProductRepository $productRepo,
        $event = null,
        $products = true
    ) {
        $this->rep = $rep;
        $this->affiliateDisplayName = $this->getAffiliateDisplayName($rep);
        parent::__construct($request, $rep->id, $productRepo, $event, $products);
    }

    private function getAffiliateDisplayName($rep)
    {
        if ($rep->seller_type_id === 1) {
            $storeSettingRepo = new StoreSettingRepository;
            return $storeSettingRepo->getDisplayName($rep->id);
        } else {
            return null;
        }
    }
}
