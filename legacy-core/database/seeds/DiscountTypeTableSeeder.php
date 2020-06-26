<?php

use Illuminate\Database\Seeder;
use App\Models\DiscountType;

class DiscountTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DiscountType::create([
            'name' => 'Facebook',
            'keyname' => str_slug('Facebook'),
            'Description' => 'Discount was applied during a Facebook Live sale.'
        ]);
    }
}
