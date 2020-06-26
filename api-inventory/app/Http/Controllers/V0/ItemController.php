<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Repositories\Interfaces\ItemInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\FormRequest;
use App\Http\Requests\Items\FindRequest;
use App\Http\Requests\Items\CreateRequest;
use App\Http\Requests\Items\DeleteRequest;
use App\Http\Requests\Items\IndexRequest;
use App\Http\Requests\Items\InventoryRequest;
use App\Http\Requests\Items\UpdateRequest;
use App\Models\Inventory;
use DB;

class ItemController extends Controller
{
    protected $ItemRepo;

    public function __construct(ItemInterface $ItemRepo)
    {
        $this->ItemRepo = $ItemRepo;
    }

    public function index(Request $request)
    {
        $this->validateRequest(new IndexRequest, $request);
        $request['available'] = $request->input('available', null);
        $request['per_page'] = $request->input('per_page', 100);
        $request['sort_by'] = $request->input('sort_by', 'sku');
        $request['user_id'] = $request->input('user_id', null);
        $request['user_pid'] = $request->input('user_pid', null);
        $items = $this->ItemRepo->index($request->all());
        return response()->json($items, 200);
    }

    public function find(Request $request, $id)
    {
        $this->validateRequest(new FindRequest, $request, $id);
        $request['available'] = $request->input('available', null);
        $request['user_id'] = $request->input('user_id', null);
        $request['user_pid'] = $request->input('user_pid', null);
        $item = $this->ItemRepo->find($id, $request->all());
        if (!$item) {
            return response()->json(['error' => 'Unable to find an item with an id of ' . $id], 404);
        }
        return response()->json($item, 200);
    }

    public function create(Request $request)
    {
        $this->validateRequest(new CreateRequest, $request);
        $request['manufacturer_sku'] = $request->get('sku');
        $request['size'] = $request->input('option');
        $request['wholesale_price'] = $request->input('wholesale_price', null);
        $request['retail_price'] = $request->input('retail_price', null);
        $request['premium_price'] = $request->input('premium_price', null);
        $item = $this->ItemRepo->create($request->all());
        return response()->json($item, 200);
    }

    public function update(Request $request, $id)
    {
        $this->validateRequest(new UpdateRequest, $request, $id);
        if ($request->has('sku')) {
            $request['manufacturer_sku'] = $request->input('sku');
        }
        if ($request->has('option')) {
            $request['size'] = $request->input('option');
        }
        $item = $this->ItemRepo->update($request->all(), $id);
        return response()->json($item, 200);
    }

    public function updateInventory(Request $request, $id)
    {
        $this->validateRequest(new InventoryRequest, $request, $id);
        $request['quantity'] = $request->input('quantity', 0);
        $inventory = $this->ItemRepo->updateOrCreateInventory($request->all(), $id);
        return response()->json($inventory, 200);
    }

    public function delete(Request $request, $id)
    {
        $this->validateRequest(new DeleteRequest, $request, $id);
        $item = $this->ItemRepo->delete($id);
        if (!$item) {
            return response()->json(['error' => 'Unable to delete item with available inventory.'], 422);
        }
        return response()->json('Success', 200);
    }

    public function updateInventoryQuantities(Request $request)
    {
        $this->validate(
            $request,
            [
                'user' => 'filled|required',
                'user.id' => 'required|integer',
                'user.pid' => 'required',
                'items' => 'array|required',
                'items.*.id' => 'required|integer',
                'items.*.quantity' => 'required|integer',
            ]
        );
        $items = $request->input('items');
        $user = $request->input('user');

        if (sizeof($items) == 0) {
            abort(400, 'Request body empty');
        }

        $itemIds = array_map(
            function ($item) {
                return $item['id'];
            },
            $items
        );

        if ($user['id'] == 1) {
            // Company can increment anything
            $itemsAllowed = collect($itemIds);
        } else {
            $itemsAllowed = Item::select("items.id")->whereIn('items.id', $itemIds)
                ->join('products as p', 'p.id', '=', 'items.product_id')
                ->where('p.resellable', '=', true)->get()->pluck('id');
        }

        $inventories = [];
        DB::beginTransaction();
        foreach ($items as &$item) {
            if (!$itemsAllowed->contains($item['id'])) {
                continue;
            }
            // Prevent race condition with firstOrCreate
            $inventory = Inventory::lockForUpdate()->firstOrCreate(
                [
                    'user_id' => $user['id'],
                    'item_id' => $item['id']
                ],
                [
                    'owner_id' => $user['id'],
                    'user_pid' => $user['pid'],
                    'owner_pid' => $user['pid']
                ]
            );
            $inventory->increment('quantity_available', $item['quantity']);
            $inventories[] = $inventory;
        }
        DB::commit();

        return response()->json($inventories, 200);
    }
}
