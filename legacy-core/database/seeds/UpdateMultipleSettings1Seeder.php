<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;

class UpdateMultipleSettings1Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

            // categories
            $general = ['category' => 'general'];
            $rep = ['category' => 'rep'];

            // update to general category
            Setting::where('key', 'company_name')->update($general);
            Setting::where('key', 'company_email')->update($general);
            Setting::where('key', 'landing_page')->update($general);
            Setting::where('key', 'order_notifaction_email')->update($general); // where is this?
            Setting::where('key', 'landing_page')->update($general);
            Setting::where('key', 'phone')->update($general);
            Setting::where('key', 'address')->update($general);
            Setting::where('key', 'title_announcement')->update($general);
            Setting::where('key', 'title_store')->update($general);
            Setting::where('key', 'use_built_in_store')->update($general);
            Setting::where('key', 'rep_facebook_login')->update($general);
            Setting::where('key', 'rep_gmail_login')->update($general); // is this in use?
            Setting::where('key', 'rep_instagram_login')->update($general); // is this in use?
            Setting::where('key', 'order_notification_email')->update($general);
            Setting::where('key', 'from_email')->update($general); // is this in use?


            // update to rep category
            Setting::where('key', 'reseller_coupons')->update($rep);
            Setting::where('key', 'title_rep')->update($rep);
            Setting::where('key', 'rep_welcome')->update($rep);
            Setting::where('key', 'product_locator')->update($rep);
            Setting::where('key', 'reseller_payment_option')->update($rep);



        DB::commit();
    }
}
