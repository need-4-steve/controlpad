<?php

use Illuminate\Database\Seeder;

class UpdateReplocaterSetting extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_setting')->update(['show_location' => true]);
    }
}
