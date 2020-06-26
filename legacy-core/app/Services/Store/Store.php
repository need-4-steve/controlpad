<?php

namespace App\Services\Store;

use App\Models\User;
use App\Models\UserSite;
use App\Models\Category;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\StoreSettingRepository;
use App\Repositories\Eloquent\CustomLinksRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class Store
{
    protected $productRepo;
    public $queryStrs = [];
    public $sortOptions = [];
    public $selectedValue = "";
    public $products;
    public $selectedCategory = null;
    public $parentCategory = null;

    public function __construct(array $request, int $user_id, ?ProductRepository $productRepo, $event = false, $products = true)
    {
        $this->customLinksRepo = new CustomLinksRepository;
        $this->sortOptions = $this->setSortOptions();
        $this->selectedValue = $this->setSelectedValue($request);
        $this->queryStrs = $this->getQueryStrs($request);
        if ($products) {
            $this->productRepo = $productRepo;
            $this->products = $this->getProduct($this->queryStrs, $user_id, $event);
        }
        $this->categories = $this->getCategories($user_id);
        $this->subCategories = $this->getSubCategories($this->queryStrs, $user_id);
        $this->settings = $this->getSettings($user_id);
        $this->customLinks = $this->getCustomLinks();
    }

    public function getCustomLinks()
    {
        if (cache()->has('custom_links')) {
            return cache()->get('custom_links');
        }
        $customLinks = $this->customLinksRepo->getIndexByType('corporate_rep_site_links');
        cache()->put('custom_links', $customLinks);
        return $customLinks;
    }
    /**
     * generates the search params based on user input
     *
     * @param array, string
     * @return array
     */
    private function getQueryStrs(array $request): array
    {

        //set default query strings
        $queryStrs = [
            'searchTerm' => '',
            'sortBy' => 'name',
            'order' => 'ASC',
            'category' => null,
            'limit' => 24
        ];
        //override default query strings with request
        foreach ($queryStrs as $key => $value) {
            if (array_key_exists($key, $request) && $request[$key] !== null) {
                $queryStrs[$key] = $request[$key];
            }
        }
        if (!isset($queryStrs['searchTerm']) or $queryStrs['searchTerm'] !== '') {
            $queryStrs['category'] = null;
        }
        //set if desc requests
        if (isset($request['sortBy'])) {
            if ($request['sortBy'] === 'name_desc') {
                $queryStrs['sortBy'] = 'name';
                $queryStrs['order'] = 'DESC';
            } elseif ($request['sortBy'] === 'name') {
                $queryStrs['sortBy'] = 'name';
                $queryStrs['order'] = 'ASC';
            } elseif ($request['sortBy'] === 'price_desc') {
                $queryStrs['sortBy'] = 'price';
                $queryStrs['order'] = 'DESC';
            } elseif ($request['sortBy'] === 'price') {
                $queryStrs['sortBy'] = 'price';
                $queryStrs['order'] = 'ASC';
            }
        }

        return $queryStrs;
    }
    /**
     * determines whether or not to show the store banner
     *
     * @return Boolean
     */
    public function showStoreBanner(): Bool
    {
        // don't show store banner if a user is being show search results or is browsing a category
        if (url()->full() !== route('store')) {
            return false;
        }
        return true;
    }
    /**
     * finds the selected value of the sorting from
     *
     * @param array
     * @return array
     */
    private function setSelectedValue(array $request): string
    {
        $selectedValue = "";
        if (isset($request['sortBy'])) {
            $selectedValue = $request['sortBy'];
        }
        return $selectedValue;
    }

    private function setSortOptions(): array
    {
        return [
                'name' => 'Alphabetically, A-Z',
                'name_desc' => 'Alphabetically, Z-A',
                'price' => 'Price, low to high',
                'price_desc' => 'Price, high to low'
            ];
    }
    /**
     * calls repository for products, and builds out the data
     *
     * @param Array
     * @return Array
     */
    private function getProduct(array $queryStrs, int $user_id, $event)
    {
        if ($user_id === config('site.apex_user_id')) {
            $role_id = 3; // Customer
            if (auth()->check()) {
                $role_id = auth()->user()->role->id;
            }
            return $this->productRepo->getCorporateProductsByRole($role_id, $queryStrs, false, $event);
        } elseif (isset($this->rep) and $this->rep->hasSellerType(['Affiliate'])) {
            $role_id = 2; // Affiliate
            return $this->productRepo->getCorporateProductsByRole($role_id, $queryStrs, true, $event);
        }
        return $this->productRepo->getByInventoryAndCategory($user_id, $queryStrs, $event);
    }

    /**
     * Calls repository to get a user's categories settings
     *
     * @param int $user_id
     * @return Category $categories
     */
    private function getCategories(int $user_id)
    {
        $storeSettingRepo = new StoreSettingRepository;
        $categories = $storeSettingRepo->getCategories($user_id);
        return $categories;
    }

    /**
     * Grabs the sub categories if there is a chosen category.
     *
     * @param array $queryStrs
     * @return Category $categories
     */
    private function getSubCategories(array $queryStrs, $userId)
    {
        if (isset($this->rep) and $this->rep->hasSellerType(['Affiliate'])) {
            $userId = config('site.apex_user_id'); // Affiliate
        }

        $this->selectedCategory = Category::where('id', '=', $queryStrs['category'])->first();
        if ($this->selectedCategory == null) {
            return null;
        }
        $this->parentCategory = (
            $this->selectedCategory->parent_id !== null ?
            Category::where('id', '=', $this->selectedCategory->parent_id)->first() :
            $this->selectedCategory
        );

        if ($this->parentCategory !== null && $this->parentCategory->show_on_store) {
            if (isset($this->parentCategory->children)) {
                return $this->parentCategory->children()->whereHas('product.items.inventory', function ($query) use ($userId) {
                    $query->inStock($userId);
                })->get();
            }
        }
        return null;
    }

    /**
     * Get a user's store settings
     *
     * @param int $user_id
     * @return StoreSettings $settings
     */
    private function getSettings(int $user_id)
    {
        $storeSettingRepo = new StoreSettingRepository;
        if (isset($this->affiliateDisplayName)) {
            return $storeSettingRepo->getSettingsByUser(config('site.apex_user_id'));
        }
        return $storeSettingRepo->getSettingsByUser($user_id);
    }
}
