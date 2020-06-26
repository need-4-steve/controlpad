<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\ReservedItemRepositoryContract;
use App\Models\ReservedItem;
use App\Repositories\Eloquent\CartRepository;
use App\Models\Cartline;
use DB;

class ReservedCartlineItemRepository implements ReservedItemRepositoryContract
{

    public function __construct(
        CartRepository $cartRepo
    ) {
        $this->cartRepo = $cartRepo;
    }

    public function create($line, $user_id)
    {

        // Gather count of reserved inventory //
        $reserved_inventory_count = "(SELECT "
                                        ."coalesce(sum(reserved.quantity), 0) as res "
                                        ."FROM "
                                        ."reserved_items as reserved "
                                    ."WHERE "
                                        ."reserved.item_id=".$line['item_id']." AND "
                                        ."reserved.user_id=".$user_id
                                    .")";

        // Gather quantity available //
        $quantity_available = "(SELECT "
                                    ."COALESCE(SUM(inventories.quantity_available), 0) as avail "
                                ."FROM "
                                    ."inventories "
                                ."WHERE "
                                    ."inventories.user_id=".$user_id." AND "
                                    ."inventories.item_id=".$line['item_id']." AND "
                                    ."inventories.deleted_at IS NULL "
                                ."LIMIT 1"
            .")";
        // Conditionally return item_id, cartline_id, bundle_cart_id, and quantity if quantity is available //
        $conditional_query = "(SELECT "
                                ."IF(((available.avail - reserved.res - ".$line['quantity']
                                .") >= 0), ".$line['item_id'].", NULL), ".$user_id.", ".$line['id'].", NULL, ".$line['quantity']
                            ." FROM "
                                .$reserved_inventory_count." AS reserved, "
                                .$quantity_available." AS available"
                            .")";

        // Insert values into reserved_items //
        $query = "INSERT INTO reserved_items(item_id, user_id, cartline_id, bundle_cart_id, quantity) ".$conditional_query;
        DB::statement($query);
    }

    public function createAvailable($line, $user_id)
    {
        // Gather count of reserved inventory //
        $reserved_inventory_count = "(SELECT "
                                        ."coalesce(sum(reserved.quantity), 0) as res "
                                        ."FROM "
                                        ."reserved_items as reserved "
                                    ."WHERE "
                                        ."reserved.item_id=".$line['item_id']." AND "
                                        ."reserved.user_id=".$user_id
                                    .")";

        // Gather quantity available //
        $quantity_available = "(SELECT "
                                    ."COALESCE(SUM(inventories.quantity_available), 0) as avail "
                                ."FROM "
                                    ."inventories "
                                ."WHERE "
                                    ."inventories.user_id=".$user_id." AND "
                                    ."inventories.item_id=".$line['item_id']." AND "
                                    ."inventories.deleted_at IS NULL "
                                ."LIMIT 1"
            .")";
        // Conditionally return item_id, cartline_id, bundle_cart_id, and quantity if quantity is available //
        $conditional_query = "(SELECT "
                                ."IF(((available.avail - reserved.res"
                                .") > 0), ".$line['item_id'].", NULL), ".$user_id.", ".$line['id'].", NULL, "
                                ."IF((available.avail - reserved.res) > ".$line['quantity'].", ".$line['quantity'].", (available.avail - reserved.res))"
                            ." FROM "
                                .$reserved_inventory_count." AS reserved, "
                                .$quantity_available." AS available"
                            .")";

        // Insert values into reserved_items //
        $query = "INSERT INTO reserved_items(item_id, user_id, cartline_id, bundle_cart_id, quantity) ".$conditional_query;

        //Retrun message with qty
        try {
            DB::beginTransaction();
            DB::statement($query);
            // Gather quantity reserved
            $reserved = ReservedItem::where('cartline_id', '=', $line['id'])->first();
            DB::commit();
            DB::table('cartlines')->where('id', $reserved->cartline_id)->update(['quantity' => $reserved->quantity]);
            // Update Cart in db and in session
            $cart = $this->cartRepo->patch($reserved->quantity, $line['item_id']);
            if ($cart) {
                session()->put('cart', $cart);
            }
            return $this->reservedInventoryMessage($reserved->quantity, $reserved->cartline->item->product->name, $reserved->cartline->item->size);
        } catch (\Illuminate\Database\QueryException $e) {
            $var = DB::table('cartlines')->where('id', $line['id'])->delete();
            DB::commit();
            return "Out of Inventory of ".$line->item->product->name." (".$line->item->size."). ";
        }
    }

    public function update($line, $user_id, $reserved_item)
    {

        // Gather count of reserved inventory //
        $reserved_inventory_count = "(SELECT "
                                        ."coalesce(sum(reserved.quantity), 0) as res "
                                        ."FROM "
                                        ."reserved_items as reserved "
                                    ."WHERE "
                                        ."reserved.item_id=".$line['item_id']." AND "
                                        ."reserved.user_id=".$user_id
                                    .")";

        // Gather quantity available //
        $quantity_available = "(SELECT "
                                    ."COALESCE(SUM(inventories.quantity_available), 0) as avail "
                                ."FROM "
                                    ."inventories "
                                ."WHERE "
                                    ."inventories.user_id=".$user_id." AND "
                                    ."inventories.item_id=".$line['item_id']." AND "
                                    ."inventories.deleted_at IS NULL "
                                ."LIMIT 1"
            .")";
        // Conditionally return item_id, cartline_id, bundle_cart_id, and quantity if quantity is available //
        $conditional_query = "(SELECT "
                                ."IF(((available.avail - reserved.res + ".$reserved_item['quantity']." - ".$line['quantity']
                                .") >= 0), ".$line['item_id'].", NULL)"
                            ." FROM "
                                .$reserved_inventory_count." AS reserved, "
                                .$quantity_available." AS available"
                            .")";
        $query = "UPDATE reserved_items SET item_id=".$conditional_query.", quantity=".$line['quantity']." WHERE id=".$reserved_item->id;
        DB::statement($query);
    }

    public function updateAvailable($line, $user_id, $reserved_item)
    {
        // Gather count of reserved inventory //
        $reserved_inventory_count = "(SELECT "
                                        ."coalesce(sum(reserved.quantity), 0) as res "
                                        ."FROM "
                                        ."reserved_items as reserved "
                                    ."WHERE "
                                        ."reserved.item_id=".$line['item_id']." AND "
                                        ."reserved.user_id=".$user_id
                                    .")";

        // Gather quantity available //
        $quantity_available = "(SELECT "
                                    ."COALESCE(SUM(inventories.quantity_available), 0) as avail "
                                ."FROM "
                                    ."inventories "
                                ."WHERE "
                                    ."inventories.user_id=".$user_id." AND "
                                    ."inventories.item_id=".$line['item_id']." AND "
                                    ."inventories.deleted_at IS NULL "
                                ."LIMIT 1"
            .")";
        // Conditionally return item_id, cartline_id, bundle_cart_id, and quantity if quantity is available //
        $conditional_query = "(SELECT "
                                ."IF(((available.avail - reserved.res + ".$reserved_item['quantity']
                                .") > 0), ".$line['item_id'].", NULL) as item,"
                                ." (available.avail - reserved.res + ".$reserved_item['quantity'].") as qty"
                            ." FROM "
                                .$reserved_inventory_count." AS reserved, "
                                .$quantity_available." AS available"
                            .")";
        $query = "UPDATE reserved_items dest, $conditional_query src SET item_id=src.item, quantity=IF(src.qty > ".$line['quantity'].", ".$line['quantity'].", src.qty) WHERE dest.id=$reserved_item->id";
        try {
            DB::beginTransaction();
              DB::statement($query);
              //Gather quantity reserved
              $reserved = ReservedItem::where('cartline_id', '=', $line['id'])->first();
            DB::commit();
            DB::table('cartlines')->where('id', '=', $reserved->cartline_id)
                ->update(['quantity' => $reserved->quantity]);
            //update cartline quantity in db and session
            $cart = $this->cartRepo->patch($reserved->quantity, $line['item_id']);
            if ($cart) {
                session()->put('cart', $cart);
            }
            return $this->reservedInventoryMessage($reserved->quantity, $reserved->cartline->item->product->name, $reserved->cartline->item->size);
        } catch (\Illuminate\Database\QueryException $e) {
            $var = DB::table('cartlines')->where('id', $line['id'])->delete();
            DB::commit();
            return "Out of Inventory of ".$line->item->product->name." (".$line->item->size."). ";
        }
    }

    public function findByCartlineId($cartlineId)
    {
        return ReservedItem::where('cartline_id', $cartlineId)->first();
    }

    private function reservedInventoryMessage($quantityAvailable, $name, $size)
    {
        $message = "There is only ".$quantityAvailable." available item";
        if ($quantityAvailable > 1) {
            $message .="s";
        }
        $message .= " of ".$name." (".$size."). ";
        return $message;
    }

    public function delete($cart)
    {
        $cartlineIds = [];
        foreach ($cart->lines as $cartline) {
            $cartlineIds[] = $cartline->id;
        }
        return ReservedItem::whereIn('cartline_id', $cartlineIds)->delete();
    }
}
