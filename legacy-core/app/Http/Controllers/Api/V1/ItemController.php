<?php namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Eloquent\ItemRepository;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    protected $inventoryRepo;
    protected $itemRepo;

    /**
     * Create a new controller instance.
     *
     * @param  InventoryRepository $inventoryRepo
     * @param  ItemRepository $itemRepo
     * @return void
     */
    public function __construct(InventoryRepository $inventoryRepo, ItemRepository $itemRepo)
    {
        $this->inventoryRepo = $inventoryRepo;
        $this->itemRepo =  $itemRepo;
    }

    /**
     * Show details on a specific item
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        return Item::find($id);
    }

    /**
     * Get an index of items that the auth user has in inventory.
     *
     * @return Response
     */
    public function index()
    {
        if (auth()->check() && auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            $item = Item::with('inventory');
        } else {
            $item = Item::with('inventory')->whereHas('inventory', function ($query) {
                $query->where('user_id', auth()->user()->id);
            });
        }
        return response()->json(['count' => $item->count(), 'data' => $item->get()], HTTP_SUCCESS);
    }

    public function getWholesaleItemsByProduct(int $product_id)
    {
        return $this->itemRepo->getItemsByProductAndInventory($product_id, config('site.apex_user_id'));
    }

    /**
     * Delete an item.
     *
     * @param  int $id
     * @return Response
     */
    public function delete($id)
    {
        $count = $this->inventoryRepo->checkItemInventory($id);

        if ((int)$count === 0) {
            $this->itemRepo->delete($id);
            return response()->json('Item deleted.', HTTP_SUCCESS);
        }
        return response()->json(['You cannot delete this item because it is still being sold in one or more rep stores.'], HTTP_BAD_REQUEST);
    }

    public function updateVariantClaimNumber()
    {
        $this->itemRepo->updateVariantClaimNumber();
        return response()->json('Success', 200);
    }
}
