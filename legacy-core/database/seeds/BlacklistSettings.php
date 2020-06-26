<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\Blacklist;
use Carbon\Carbon;

class BlacklistSettings extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = Setting::where('key', 'subdomain_blacklist')->first();
        if (isset($settings) && count($settings) > 0)
 {
            $value = json_decode($settings->value, true);
            $names = explode(',', $value['value']);
            foreach ($names as $key => $name) {
                DB::beginTransaction();
                Blacklist::insert([
                    'name' => $name,
                    'category' => $settings->category,
                    'user_created' => 1,
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString()
                ]);
                DB::commit();
            }
            $settings->delete();
        }
    }
}
