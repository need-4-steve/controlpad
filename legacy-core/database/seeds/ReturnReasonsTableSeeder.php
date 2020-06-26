<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\ReturnReason;

class ReturnReasonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datetime = Carbon::now()->toDateTimeString();
        $reasons = ReturnReason::all();
        if (count($reasons) == 0) {
            DB::table('return_reasons')->insert([
                ['id' => 1, 'name' => 'Damaged', 'keyname' => str_slug('Damaged'), 'created_at' => $datetime, 'updated_at' => $datetime],
                ['id' => 2, 'name' => 'Sizing', 'keyname' => str_slug('Sizing'), 'created_at' => $datetime, 'updated_at' => $datetime],
                ['id' => 3, 'name' => 'Wrong Item', 'keyname' => str_slug('Wrong Item'), 'created_at' => $datetime, 'updated_at' => $datetime],
            ]);
        }
    }
}
