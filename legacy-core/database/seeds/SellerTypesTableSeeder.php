<?php

use Illuminate\Database\Seeder;
use App\Models\SellerType;

class SellerTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('seller_types')->truncate();
        DB::beginTransaction();
        SellerType::create([
            "id" => 1,
            "name" => "Affiliate",
            "description" => "Affiliates sell corporate inventory for commissions."
        ]);
        SellerType::create([
            "id" => 2,
            "name" => "Reseller",
            "description" => "Resellers purchase inventory from corporate and then sell their personal inventory."
        ]);
        DB::commit();
    }
}
