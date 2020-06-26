<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsTaxesSeeder extends Seeder
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
            'key' => 'tax_exempt_wholesale',
            'value' => '{"value": "wholesale sales are not taxed", "show": true}',
            'category' => 'taxes',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'tax_classes_required',
            'value' => '{"value": "tax classes are required", "show": true}',
            'category' => 'taxes'
        ]);
        DB::commit();
    }
}
