<?php

use Illuminate\Database\Seeder;
use App\Models\PriceType;

class PriceTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        PriceType::create([
            'name' => 'Wholesale'
        ]);

        PriceType::create([
            'name' => 'Suggested Retail'
        ]);

        PriceType::create([
            'name' => 'Premium'
        ]);

        PriceType::create([
            'name' => 'Rep'
        ]);

        PriceType::create([
            'name' => 'Commissionable Value'
        ]);
        DB::commit();
    }
}
