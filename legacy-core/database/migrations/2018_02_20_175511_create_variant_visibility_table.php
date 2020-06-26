<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Variant;

class CreateVariantVisibilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('variant_visibility')) {
            Schema::create('variant_visibility', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('variant_id')->unsigned()->index();
                $table->foreign('variant_id')->references('id')->on('variants')->onDelete('cascade');
                $table->integer('visibility_id')->unsigned()->index();
                $table->foreign('visibility_id')->references('id')->on('visibilities')->onDelete('cascade');
            });
        }
        $variants = Variant::with('product.roles')->get();
        $variantVisibility = [];
        foreach ($variants as $variant) {
            if (isset($variant->product->roles)) {
                foreach ($variant->product->roles as $visibility) {
                    $variantVisibility[] = [
                        'variant_id' => $variant->id,
                        'visibility_id' => $visibility->id
                    ];
                }
            }
        }
        DB::table('variant_visibility')->insert($variantVisibility);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('variant_visibility');
    }
}
