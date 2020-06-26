<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Item;
use App\Models\Variant;

class AddVariationIdToItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->integer('variant_id');
            $table->string('print')->nullable(false)->default('')->change();
        });

        $items = Item::all();
        DB::beginTransaction();
        foreach ($items as $item) {
            $variant = Variant::firstOrCreate([
               'product_id'   => $item->product_id,
               'name'         => $item->print,
               'type'         => 'Print',
               'option_label'  => 'Size',
            ]);
            $item->variant_id = $variant->id;
            $item->save();
        }
        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('variant_id');
            $table->string('print')->nullable(true)->change();
        });
    }
}
