<?php

namespace App\Services\Store;

use App\Models\UserSite;
use App\Models\Party;
use App\Models\User;
use App\Models\Role;
use App\Repositories\Eloquent\ProductRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * prepares data for a rep's store.
 */
class WholesaleStore extends Store
{
    public function __construct(array $request, int $role_id, ProductRepository $productRepo)
    {
        parent::__construct($request, config('site.apex_user_id'), $productRepo);
        $this->products = $this->getProduct($this->queryStrs, auth()->id());
    }

    /**
     * calls repository for products
     *
     * @param Array
     * @return Array
     */
    private function getProduct(array $queryStrs, int $user_id)
    {
        return $this->productRepo->getCorporateProductsByRole(5, $queryStrs);
    }
}
