<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;

class UpdateMultipleSettings2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        Setting::where('key', 'allow_new_affiliates')->delete();
        Setting::where('key', 'allow_new_reps')->delete();
        Setting::where('key', 'allow_new_hybrids')->delete();
        Setting::where('key', 'rep_create_product')->update(['key' => 'reseller_create_product']);
        Setting::where('key', 'rep_coupons')->update(['key' => 'reseller_coupons']);
        Setting::where('key', 'rep_ewallet')->update(['key' => 'reseller_ewallet']);
        Setting::where('key', 'payment_option')->update(['key' => 'reseller_payment_option']);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_purchase_inventory',
            'value' => '{"value": "Affiliates can purchase inventory", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_create_product',
            'value' => '{"value": "Affiliates can create products", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_ewallet',
            'value' => '{"value": "Affiliates have access to their ewallet", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_returns',
            'value' => '{"value": "Resellers have access to returns", "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_returns',
            'value' => '{"value": "Affiliates have access to returns", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_media_library',
            'value' => '{"value": "Resellers have access to the media library", "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_media_library',
            'value' => '{"value": "Affiliates have access to the media library", "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_payment_option',
            'value' => '{"value": "", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_facebook_live',
            'value' => '{"value": "Resellers have access to Facebook Live", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_facebook_live',
            'value' => '{"value": "Affiliates have access to Facebook Live", "show": false}',
            'category' => 'rep',
        ]);
        DB::commit();
    }
}
