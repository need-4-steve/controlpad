<?php

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        factory(Coupon::class, 15)->create(['owner_id' => config('site.apex_user_id')]);
        factory(Coupon::class, 15)->create();
        DB::commit();
    }
}
