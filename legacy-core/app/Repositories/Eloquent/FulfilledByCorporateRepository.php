<?php namespace App\Repositories\Eloquent;

use App\Models\Inventory;
use App\Models\Media;
use Carbon\Carbon;
use DB;

class FulfilledByCorporateRepository
{
    public function relist($itemId)
    {
        $inventory = Inventory::where('item_id', $itemId)
                        ->where('user_id', config('site.apex_user_id'))
                        ->where('owner_id', '!=', config('site.apex_user_id'))
                        ->with('item.product')
                        ->first();
        if (!$inventory) {
            return ['error' => 'Could not find inventory for item.'];
        }
        if ($inventory->item->product->type_id !== 5) {
            return ['error' => 'Can not relist products that are not type fulfilled by corporate.'];
        }
        $inventory->expires_at = Carbon::now()->addDays($inventory->item->product->duration)->toDateTimeString();
        $inventory->save();
        $inventory->status = 'Active';
        return $inventory;
    }

    public function getInventory($request, $user)
    {
        $request = $this->buildParameters($request);
        $inventory = Inventory::where('user_id', config('site.apex_user_id'))
                        ->search($request['search_term'])
                        ->with('item.product', 'item.msrp', 'item.premiumPrice', 'owner');

        if ($user->hasRole(['Admin', 'Superadmin'])) {
            $inventory->where('owner_id', '!=', config('site.apex_user_id'));
        } else {
            $inventory->where('owner_id', $user->id);
        }

        if ($request['status'] === 'expired') {
            $inventory->where('quantity_available', '>', 0)
                ->where('expires_at', '<', Carbon::now());
        } elseif ($request['status'] === 'sold_out') {
            $inventory->where('quantity_available', 0);
        } elseif ($request['status'] === 'active') {
            $inventory->where('quantity_available', '>', 0)
                ->where('expires_at', '>', Carbon::now());
        }

        $inventory = $inventory->orderBy($request['column'], $request['order'])
            ->paginate($request['per_page']);

        foreach ($inventory as $inventoryItem) {
            if ($inventoryItem->quantity_available <= 0) {
                $inventoryItem->status = 'Sold Out';
            } elseif ($inventoryItem->quantity_available > 0 and $inventoryItem->expires_at < Carbon::now()) {
                $inventoryItem->status = 'Expired';
            } else {
                $inventoryItem->status = 'Active';
            }
            $inventoryItem->default_media = Media::join('mediables', function ($join) use ($inventoryItem) {
                $join->on('mediables.media_id', '=', 'media.id')
                ->where('mediables.mediable_type', '=', 'App\Models\Product')
                ->where('mediables.mediable_id', '=', $inventoryItem->item->product_id);
            })->take(1)->first();
        }

        return $inventory;
    }

    public function changeOwnerOfSoldOut()
    {
        DB::table('inventories')
            ->where('user_id', config('site.apex_user_id'))
            ->where('owner_id', '!=', config('site.apex_user_id'))
            ->where('expires_at', '<', Carbon::now()->toDateTimeString())
            ->where('quantity_available', 0)
            ->where('quantity_staged', 0)
            ->update(['owner_id' => config('site.apex_user_id')]);
    }

    public function fixInventoryOwnerIdOfZero()
    {
        $inventory = Inventory::where('user_id', config('site.apex_user_id'))->where('owner_id', 0)->pluck('id');
        if (count($inventory) > 0) {
            logger()->error([
                'Inventory with owner_id of 0',
                'inventory_ids' => $inventory->toArray()
            ]);
            DB::statement('UPDATE inventories SET owner_id = user_id WHERE owner_id = 0');
        }
    }

    private function buildParameters($request)
    {
        if (!isset($request['status'])) {
            $request['status'] = 'all';
        }
        if (!isset($request['per_page'])) {
            $request['per_page'] = 50;
        }
        if (!isset($request['search_term'])) {
            $request['search_term'] = '';
        }
        if (!isset($request['column'])) {
            $request['column'] = 'expires_at';
        }
        if (!isset($request['order'])) {
            $request['order'] = 'asc';
        }
        return $request;
    }
}
