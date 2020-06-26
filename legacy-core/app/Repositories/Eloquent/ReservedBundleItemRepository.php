<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\ReservedItemRepositoryContract;
use App\Models\ReservedItem;
use App\Repositories\Eloquent\CartRepository;
use App\Models\Cartline;
use DB;

class ReservedBundleItemRepository
{

    public function __construct(
        CartRepository $cartRepo
    ) {
        $this->cartRepo = $cartRepo;
    }

    public function create($item, $bundle_cart, $user_id)
    {
        // Gather count of reserved inventory //
        $reserved_inventory_count = "(SELECT "
                                        ."coalesce(sum(reserved.quantity), 0) as res "
                                        ."FROM "
                                        ."reserved_items as reserved "
                                    ."WHERE "
                                        ."reserved.item_id=".$item['id']." AND "
                                        ."reserved.user_id=".$user_id
                                    .")";

        // Gather quantity available //
        $quantity_available = "(SELECT "
                                    ."COALESCE(SUM(inventories.quantity_available), 0) as avail "
                                ."FROM "
                                    ."inventories "
                                ."WHERE "
                                    ."inventories.user_id=".$user_id." AND "
                                    ."inventories.item_id=".$item['id']." AND "
                                    ."inventories.owner_id=".config('site.apex_user_id')." AND "
                                    ."inventories.deleted_at IS NULL "
                                ."LIMIT 1"
            .")";
        // Conditionally return item_id, cartline_id, bundle_cart_id, and quantity if quantity is available //
        $conditional_query = "(SELECT "
                                ."IF(((available.avail - reserved.res - (".$bundle_cart['quantity']*$item['pivot']['quantity'].")"
                                .") >= 0), ".$item['id'].", NULL), ".$user_id.", NULL, ".$bundle_cart['id'].", ".$bundle_cart['quantity']*$item['pivot']['quantity']
                            ." FROM "
                                .$reserved_inventory_count." AS reserved, "
                                .$quantity_available." AS available"
                            .")";

        // Insert values into reserved_items //
        $query = "INSERT INTO reserved_items(item_id, user_id, cartline_id, bundle_cart_id, quantity) ".$conditional_query;
        DB::statement($query);
    }

    public function createAvailable($cart, $item, $bundle_cart, $user_id)
    {
        // Gather count of reserved inventory //
        $reserved_inventory_count = "(SELECT "
                                        ."coalesce(sum(reserved.quantity), 0) as res "
                                        ."FROM "
                                        ."reserved_items as reserved "
                                    ."WHERE "
                                        ."reserved.item_id=".$item['id']." AND "
                                        ."reserved.user_id=".$user_id
                                    .")";

        // Gather quantity available //
        $quantity_available = "(SELECT "
                                    ."COALESCE(SUM(inventories.quantity_available), 0) as avail "
                                ."FROM "
                                    ."inventories "
                                ."WHERE "
                                    ."inventories.user_id=".$user_id." AND "
                                    ."inventories.item_id=".$item['id']." AND "
                                    ."inventories.owner_id=".config('site.apex_user_id')." AND "
                                    ."inventories.deleted_at IS NULL "
                                ."LIMIT 1"
            .")";

        // Conditionally return item_id, cartline_id, bundle_cart_id, and quantity if quantity is available //
        $conditional_query = "(SELECT "
                                ."IF((((available.avail - reserved.res) DIV ".$item['pivot']['quantity']
                                .") > 0), ".$item['id'].", NULL), ".$user_id.", NULL, ".$bundle_cart['id'].", "
                                ."IF("
                                    ."(((available.avail - reserved.res) DIV ".$item['pivot']['quantity'].")*".$item['pivot']['quantity'].") > ".$bundle_cart['quantity']*$item['pivot']['quantity']
                                    .", ".$bundle_cart['quantity']*$item['pivot']['quantity'].", (((available.avail - reserved.res) DIV ".$item['pivot']['quantity'].")*".$item['pivot']['quantity'].")"
                                .")"
                            ." FROM "
                                .$reserved_inventory_count." AS reserved, "
                                .$quantity_available." AS available"
                            .")";

        $query = "INSERT INTO reserved_items(item_id, user_id, cartline_id, bundle_cart_id, quantity) ".$conditional_query;
        //Retrun message with qty
        try {
            DB::beginTransaction();
            DB::statement($query);
            // Gather quantity reserved
            $reserved = ReservedItem::where('bundle_cart_id', '=', $bundle_cart['id'])->where('item_id', '=', $item['id'])->first();
            DB::commit();
            DB::table('bundle_cart')->where('id', $reserved->bundle_cart_id)->update(['quantity' => $reserved->quantity / $item['pivot']['quantity']]);
            // Update Cart in db and in session
            $cart = $this->cartRepo->patchBundle($bundle_cart['bundle_id'], $cart, $reserved->quantity / $item['pivot']['quantity']);
            if ($cart) {
                session()->put('cart', $cart);
            }
            return $reserved->quantity / $item['pivot']['quantity'];
        } catch (\Illuminate\Database\QueryException $e) {
            $var = DB::table('bundle_cart')->where('id', $bundle_cart['id'])->delete();
            DB::commit();
            return 0;
        }
    }

    public function update($item, $bundle_cart, $user_id, $reserved_item)
    {

        // Gather count of reserved inventory //
        $reserved_inventory_count = "(SELECT "
                                        ."coalesce(sum(reserved.quantity), 0) as res "
                                        ."FROM "
                                        ."reserved_items as reserved "
                                    ."WHERE "
                                        ."reserved.item_id=".$item['id']." AND "
                                        ."reserved.user_id=".$user_id
                                    .")";

        // Gather quantity available //
        $quantity_available = "(SELECT "
                                    ."COALESCE(SUM(inventories.quantity_available), 0) as avail "
                                ."FROM "
                                    ."inventories "
                                ."WHERE "
                                    ."inventories.user_id=".$user_id." AND "
                                    ."inventories.item_id=".$item['id']." AND "
                                    ."inventories.owner_id=".config('site.apex_user_id')." AND "
                                    ."inventories.deleted_at IS NULL "
                                ."LIMIT 1"
            .")";
        // Conditionally return item_id, cartline_id, bundle_cart_id, and quantity if quantity is available //
        $conditional_query = "(SELECT "
                                ."IF(((available.avail - reserved.res + $reserved_item->quantity - (".$bundle_cart['quantity']*$item['pivot']['quantity'].")"
                                .") >= 0), ".$item['id'].", NULL)"
                            ." FROM "
                                .$reserved_inventory_count." AS reserved, "
                                .$quantity_available." AS available"
                            .")";

        // Insert values into reserved_items //
        $query = "UPDATE reserved_items SET item_id=".$conditional_query.", quantity=".$bundle_cart['quantity']*$item['pivot']['quantity']."  WHERE id=".$reserved_item->id;
        $status = (DB::update($query)) ? "true" : "false";
    }

    public function updateAvailable($cart, $item, $bundle_cart, $user_id, $reserved_item)
    {
        // Gather count of reserved inventory //
        $reserved_inventory_count = "(SELECT "
                                        ."coalesce(sum(reserved.quantity), 0) as res "
                                        ."FROM "
                                        ."reserved_items as reserved "
                                    ."WHERE "
                                        ."reserved.item_id=".$item['id']." AND "
                                        ."reserved.user_id=".$user_id
                                    .")";

        // Gather quantity available //
        $quantity_available = "(SELECT "
                                    ."COALESCE(SUM(inventories.quantity_available), 0) as avail "
                                ."FROM "
                                    ."inventories "
                                ."WHERE "
                                    ."inventories.user_id=".$user_id." AND "
                                    ."inventories.item_id=".$item['id']." AND "
                                    ."inventories.owner_id=".config('site.apex_user_id')." AND "
                                    ."inventories.deleted_at IS NULL "
                                ."LIMIT 1"
            .")";

        $conditional_query = "(SELECT "
                                ."IF((((available.avail - reserved.res + ".$reserved_item->quantity.") DIV ".$item['pivot']['quantity']
                                .") > 0), ".$item['id'].", NULL) as item, (((available.avail - reserved.res + ".$reserved_item->quantity.") DIV ".$item['pivot']['quantity'].")*".$item['pivot']['quantity'].") as qty"
                            ." FROM "
                                .$reserved_inventory_count." AS reserved, "
                                .$quantity_available." AS available"
                            .")";

        $query = "UPDATE reserved_items dest, $conditional_query src SET item_id=src.item, quantity=IF(src.qty > ".$bundle_cart['quantity']*$item['pivot']['quantity'].", ".$bundle_cart['quantity']*$item['pivot']['quantity'].", src.qty) WHERE dest.id=".$reserved_item->id;

        //Retrun message with qty
        try {
            DB::beginTransaction();
            DB::statement($query);
            // Gather quantity reserved
            $reserved = ReservedItem::where('bundle_cart_id', '=', $bundle_cart['id'])->where('item_id', '=', $item['id'])->first();
            DB::commit();
            DB::table('bundle_cart')->where('id', $reserved->bundle_cart_id)->update(['quantity' => $reserved->quantity / $item['pivot']['quantity']]);
            // Update Cart in db and in session
            $cart = $this->cartRepo->patchBundle($bundle_cart['bundle_id'], $cart, $reserved->quantity / $item['pivot']['quantity']);
            if ($cart) {
                session()->put('cart', $cart);
            }
            return $reserved->quantity / $item['pivot']['quantity'];
        } catch (\Illuminate\Database\QueryException $e) {
            $var = DB::table('bundle_cart')->where('id', $bundle_cart['id'])->delete();
            DB::commit();
            return 0;
        }
    }

    public function findByBundleCartId($bundleCartId, $itemId)
    {
        return ReservedItem::where('bundle_cart_id', $bundleCartId)->where('item_id', $itemId)->first();
    }

    public function delete($cart)
    {
        $bundleCartIds = [];
        foreach ($cart->bundles as $bundle) {
            $bundleCartIds[] = $bundle->pivot->id;
        }
        return ReservedItem::whereIn('bundle_cart_id', $bundleCartIds)->delete();
    }
}
