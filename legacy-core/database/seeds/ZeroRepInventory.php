<?php

use Illuminate\Database\Seeder;

class ZeroRepInventory extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('inventories')
            ->where('user_id', '!=', config('site.apex_user_id'))
            ->update(['quantity_available' => 0]);
    }
}
