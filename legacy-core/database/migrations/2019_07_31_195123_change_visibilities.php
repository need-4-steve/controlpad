<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Visibility;

class ChangeVisibilities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $count = Visibility::count();
        if ($count > 0) {
            // Table is seeded, update visibilities
            Visibility::where('id', '=', 3)->update(['name' => 'Reseller Retail', 'description' => 'Reseller sites.']);
            Visibility::where('id', '=', 5)->update(['name' => 'Wholesale', 'description' => 'Wholesale purchase.']);
            Visibility::insert([
                ['id' => 1, 'name' => 'Corp Retail', 'description' => 'Retail store for corporate.'],
                ['id' => 2, 'name' => 'Affiliate', 'description' => 'Affiliate stores.'],
                ['id' => 6, 'name' => 'Preferred Retail', 'description' => 'Preferred Retail purchase in backoffice.'],
                ['id' => 4, 'name' => 'Registration', 'description' => 'Registration purchase.']
            ]);
            DB::insert('INSERT INTO product_visibility (product_id, visibility_id) (SELECT product_id, 2 FROM product_visibility WHERE visibility_id = 3)');
            DB::insert('INSERT INTO variant_visibility (variant_id, visibility_id) (SELECT variant_id, 2 FROM variant_visibility WHERE visibility_id = 3)');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Visibility::where('id', '=', 3)->update(['name' => 'Customer', 'description' => 'Someone who has purchased a product or service.']);
        Visibility::where('id', '=', 5)->update(['name' => 'Rep', 'description' => 'A fully-featured member and representative. Can only access features related to their sales and resources.']);
        Visibility::whereIn('id', [1,2,4,6])->delete();
        DB::delete('DELETE FROM product_visibility WHERE visibility_id = 2');
        DB::delete('DELETE FROM variant_visibility WHERE visibility_id = 2');
    }
}
