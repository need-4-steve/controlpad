<?php

use App\Models\OrderType;

class OrderTypesTableSeeder extends DatabaseSeeder
{

    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('order_types')->truncate();
        DB::beginTransaction();
        OrderType::create(['id' => 1, 'name' => 'Corporate to Rep']);
        OrderType::create(['id' => 2, 'name' => 'Corporate to Customer']);
        OrderType::create(['id' => 3, 'name' => 'Rep to Customer']);
        OrderType::create(['id' => 4, 'name' => 'Rep to Rep']);
        OrderType::create(['id' => 5, 'name' => 'Corporate to Admin']);
        OrderType::create(['id' => 6, 'name' => 'Fulfilled by Corporate']);
        OrderType::create(['id' => 7, 'name' => 'Mixed']);
        OrderType::create(['id' => 8, 'name' => 'Transfer Inventory']);
        OrderType::create(['id' => 9, 'name' => 'Affiliate']);
        OrderType::create(['id' => 10, 'name' => 'Personal Use']);
        OrderType::create(['id' => 11, 'name' => 'Rep Transfer']);
        DB::commit();
        Schema::enableForeignKeyConstraints();
    }
}
