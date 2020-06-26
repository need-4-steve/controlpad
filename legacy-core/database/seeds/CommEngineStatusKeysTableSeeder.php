<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CommEngineStatusKeysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now('UTC');
        DB::table('comm_engine_status_keys')->insert([
            ['id' => 1, 'name' => 'uncommitted',        'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'committed',          'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'non-commisionable',  'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'error',              'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'backfill',           'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'name' => 'queued',             'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'name' => 'cancel-error',       'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'name' => 'cancelled',          'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'name' => 'retcount-error',     'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
