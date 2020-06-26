<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class ReplicatedSiteSetting extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        Setting::create([
            'user_id' => 1,
            'key' => 'replicated_site',
            'value' => '{"value": false, "show": true}',
            'category' => 'rep',
        ]);
        DB::commit();
    }
}
