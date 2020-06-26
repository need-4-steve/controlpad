<?php

use Illuminate\Database\Seeder;
use App\Models\ProductType;

class ProductTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('product_type')->truncate();
        DB::beginTransaction();
        ProductType::create([
            'name' => 'Product'
        ]);

        ProductType::create([
            'name' => 'Subscription'
        ]);

        ProductType::create([
            'name' => 'Donation'
        ]);

        ProductType::create([
            'name' => 'Digital'
        ]);

        ProductType::create([
            'name' => 'Fulfilled by Corporate'
        ]);

        ProductType::create([
            'name' => 'Business Tools'
        ]);
        Schema::enableForeignKeyConstraints();
        DB::commit();
    }
}
