<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class ShippingSettingsSeeder extends Seeder
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
            'key' => 'batch_label_create',
            'value' => json_encode(['value' => false, 'show' => false]),
            'category' => 'shipping',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'shipping_team_id',
            'value' => json_encode(['value' => 'controlpad', 'show' => false]),
            'category' => 'shipping',
        ]);
        DB::commit();
    }
}
