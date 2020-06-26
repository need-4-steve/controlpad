<?php

use App\Models\ReturnStatus;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReturnStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datetime = Carbon::now()->toDateTimeString();
        DB::beginTransaction();
        ReturnStatus::create([
            'id' => 1,
            'name' => 'Open',
            'keyname' => str_slug('Open'),
            'created_at' => $datetime,
            'updated_at' => $datetime
        ]);
        ReturnStatus::create([
            'id' => 2,
            'name' => 'Pending',
            'keyname' => str_slug('Pending'),
            'created_at' => $datetime, 'updated_at' => $datetime
        ]);
        ReturnStatus::create([
            'id' => 3,
            'name' => 'Closed',
            'keyname' => str_slug('Closed'),
            'created_at' => $datetime,
            'updated_at' => $datetime
        ]);
        DB::commit();
    }
}
