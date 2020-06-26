<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueConstraintItemsUsersInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $update = "UPDATE inventories, (SELECT MIN(id) as id, item_id, user_id, SUM(quantity_available) as totals, COUNT(*) as count FROM inventories GROUP BY item_id, user_id HAVING count > 1 ORDER BY count ASC) t SET inventories.quantity_available=t.totals WHERE inventories.id=t.id;";
        $delete = "DELETE inventories.* FROM inventories, (SELECT MIN(id) as id, item_id, user_id, SUM(quantity_available), COUNT(*) as count FROM inventories GROUP BY item_id, user_id HAVING count > 1 ORDER BY count ASC) t WHERE inventories.item_id = t.item_id  AND inventories.user_id = t.user_id AND inventories.id != t.id;";
        DB::beginTransaction();
        DB::statement($update);
        DB::statement($delete);
        DB::commit();
        Schema::table('inventories', function (Blueprint $table) {
            $table->unique(array('item_id', 'user_id'))->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
