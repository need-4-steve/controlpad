<?php namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\ItemRepository;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Services\Store\WholesaleStore;
use App\Services\Store\RepStore;
use App\Services\Store\Store;
use App\Http\Requests\EditProductRequest;
use App\Http\Requests\CreateProductRequest;
use App\Models\Inventory;
use Validator;

class ProductController extends Controller
{
    protected $productRepo;
    protected $roleRepo;
    protected $itemRepo;
    protected $inventoryRepo;

    /**
     * Create a new controller instance.
     *
     * @param  ProductRepository  $product
     * @return void
     */
    public function __construct(
        ProductRepository $productRepo,
        RoleRepository $roleRepo,
        ItemRepository $itemRepo,
        InventoryRepository $inventoryRepo
    ) {
        $this->productRepo = $productRepo;
        $this->roleRepo = $roleRepo;
        $this->itemRepo = $itemRepo;
        $this->inventoryRepo = $inventoryRepo;
        $this->settingsService = app('globalSettings');
    }

    /**
     * Get all products based on authorized user's role
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $request = request()->all();
        $products = $this->productRepo->index($request);
        return response()->json($products, HTTP_SUCCESS);
    }

    public function wholesaleStore()
    {
        $input = request()->all();
        if ($input['category'] === 'all') {
            $input['category'] = null;
        }
        $store = new WholesaleStore($input, 5, $this->productRepo);
        if (count($store->products)> 0) {
            $store->products->load('media');
        }
        $store->totalAvailable = $store->products->totalAvailable;
        return response()->json($store, HTTP_SUCCESS);
    }

    /**
     * This creates a new product
     * The CreateProductRequest check to make sure the user is Authorized to create a product.
     * @param CreateProductRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(CreateProductRequest $request)
    {
        return response()->json($this->productRepo->create($request->all()), 200);
    }

    public function show($id)
    {
        return $product = $this->productRepo->find($id, ['items.wholesalePrice', 'media']);
    }

    public function showWholesale($id)
    {
        return $this->productRepo->showWholesale(intval($id));
    }

    public function showEdit($id)
    {
        $product = $this->productRepo->find($id, [
            'items.premiumPrice',
            'items.msrp',
            'items.wholesalePrice',
            'tags',
            'media',
            'category.parent',
            'roles',
        ]);

        $product->checkedRoles = $this->roleRepo->findCheckedRoles($this->roleRepo->all(), $product->roles);
        return $product;
    }

    public function edit(EditProductRequest $request)
    {
        $product = $this->productRepo->find($request['id']);

        $ownedInventory = $this->inventoryRepo->itemsOwnerInventoryCheck($request['items'], $request['type_id'], $product);
        if (isset($ownedInventory['error'])) {
            return response()->json([$ownedInventory['error']], 400);
        }

        if ($request['type_id'] == 5 and !$ownedInventory) {
            $validator = Validator::make($request->all(), ['roles.*' => 'in:3,7,8|nullable'], ['roles.*' => 'Cannot choose role rep on a product owned by a rep being fulfilled by corporate.'])->validate();
        } elseif ($request['type_id'] == 5 and $ownedInventory) {
            $validator = Validator::make($request->all(), ['roles.*' => 'in:7,8|nullable'], ['roles.*' => 'Products fulfilled by corporate can only be made available to reps by creating the product in a pack.'])->validate();
        }
        $product = $this->productRepo->update($product, request()->all());
        return $product;
    }

    public function delete($id)
    {
        $product = Product::find($id);
        $inRepStock = $product->items()->join('inventories', 'inventories.item_id', '=', 'items.id')
                                        ->sum('inventories.quantity_available');
        if ($inRepStock > 0) {
            return response()->json([
                'error' => true,
                'message' => 'You cannot delete this product because it is still being sold in one or more
                            rep stores. You may make this product unavailable by setting your inventory
                            to zero.',
                'data' => $product], 403);
        }

        foreach ($product->items as $item) {
            $inventory = $item->inventory->where('user_id', config('site.apex_user_id'))->first();
            if (!empty($inventory)) {
                Inventory::destroy($inventory->id);
            }
            $this->itemRepo->delete($item->id);
        }
        $this->productRepo->delete($id);
        return response()->json(['error' => false, 'message' => 'Product successfully deleted.'], 200);
    }

    public function type()
    {
        if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            $productTypes = $this->productRepo->productTypes([1,6]);
        } else {
            $productTypes = $this->productRepo->productTypes();
        }
        return response()->json($productTypes, 200);
    }

    public function search()
    {
        $request = request()->all();
        if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            $userId = config('site.apex_user_id');
        } else {
            $userId = auth()->user()->id;
        }

        $products = $this->productRepo->search($request, $userId);
        return response()->json($products, 200);
    }

    public function all()
    {
        $products = $this->productRepo->all(request()->all());
        return response()->json($products, 200);
    }
}
