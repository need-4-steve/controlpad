<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\CustomEmail;
use App\Models\Setting;

class AutoshipRetailChanges extends Migration
{
    public function up()
    {
        $count = DB::table('autoship_visibilities')->count();
        if ($count > 0) {
            // Table is seeded, update visibilities
            DB::table('autoship_visibilities')->where('id', '=', 3)->update(['name' => 'Reseller Retail', 'description' => 'Reseller sites.']);
            DB::table('autoship_visibilities')->where('id', '=', 5)->update(['name' => 'Wholesale', 'description' => 'Wholesale purchase.']);
            DB::table('autoship_visibilities')->insert([
                ['id' => 1, 'name' => 'Corp Retail', 'description' => 'Retail store for corporate.'],
                ['id' => 2, 'name' => 'Affiliate', 'description' => 'Affiliate stores.'],
                ['id' => 6, 'name' => 'Preferred Retail', 'description' => 'Preferred Retail purchase in backoffice.'],
                ['id' => 4, 'name' => 'Registration', 'description' => 'Registration purchase.']
            ]);
        }

        $settings = Setting::first();
        if (!is_null($settings)) {
            // Change autoship setting
            Setting::where('key', '=', 'autoship_wholesale')->update(['key' => 'autoship_enabled']);
            Setting::where('key', '=', 'autoship_retail')->delete();
            Setting::create([
                'user_id' => 1,
                'key' => 'autoship_reminder',
                'value' => '{"show":true, "value":5}',
                'category' => 'auto_ship'
            ]);
            cache()->forget('globalSettings');
            cache()->forget('global-settings');
        }
        Schema::table('checkouts', function (Blueprint $table) {
            $table->string('autoship_pid', 25)->nullable();
        });
        $autoshipSubs = DB::select("SELECT MAX(id) as id FROM autoship_subscriptions");
        if (isset($autoshipSubs[0]->id) && $autoshipSubs[0]->id < 1001) {
            // Start subscription id at a larger value so the link isn't so small on the index pages
            DB::statement("ALTER TABLE autoship_subscriptions AUTO_INCREMENT = 1001");
        }
        CustomEmail::create([
            'title' => 'autoship_sub_receipt',
            'subject' => 'Autoship created',
            'display_name' => 'Autoship Receipt',
            'body' => "<p>[company_name] Receipt</p>
            <p>Hi [buyer_first_name],</p>
            <p>Your autoship has been created.</p>
            <p>Your autoship details</p><p>[subscription_lines]</p>
            <p>Subtotal Price: [subscription_subtotal]</p>
            <p>Discount: [subscription_discount]</p>
            <p><br></p><p>If you have any questions or concerns, please don't hesitate to
            contact customer support.</p>
            <p><br></p><p><br></p><p><br></p><p style='text-align: center;'>[back_office_logo]</p>
            <p style='text-align: center;'>This is an important notification from [company_name]</p>
            <p style='text-align: center;'>[company_name] [company_address]</p>"
        ]);

        CustomEmail::create([
            'title' => 'autoship_sub_received',
            'subject' => 'You Have an New Autoship Subscriber!',
            'display_name' => 'New Autoship Subscriber',
            'body' => '<p>Dear [seller_full_name],</p>
            <p>Congratulations! You have a new subscriber! Here are the details from this subscription:</p>
            <p>Subscription ID: [subscription_id]</p>
            <p>Purchaser Name: [buyer_full_name]</p>
            <p>Purchaser Email: [buyer_email]</p><p>[subscription_lines]</p><p><br></p>
            <p><br></p><p><br></p><p><br></p><p style="text-align: center;">[back_office_logo]</p>
            <p style="text-align: center;">This is an important notification from [company_name]</p>
            <p style="text-align: center;">[company_name] [company_address]</p>'
        ]);

        CustomEmail::create([
            'title' => 'autoship_sub_cancel',
            'subject' => 'Autoship cancelled',
            'display_name' => 'Autoship Cancelled (Buyer)',
            'body' => "<p>[company_name]</p>
            <p>Hi [buyer_first_name],</p>
            <p>Your autoship has been cancelled.</p>
            <p>Your autoship details</p><p>[subscription_lines]</p>
            <p>Subtotal Price: [subscription_subtotal]</p>
            <p>Discount: [subscription_discount]</p>
            <p><br></p><p>If you have any questions or concerns, please don't hesitate to
            contact customer support.</p>
            <p><br></p><p><br></p><p><br></p><p style='text-align: center;'>[back_office_logo]</p>
            <p style='text-align: center;'>This is an important notification from [company_name]</p>
            <p style='text-align: center;'>[company_name] [company_address]</p>"
        ]);

        CustomEmail::create([
            'title' => 'autoship_sub_cancel_seller',
            'subject' => 'An autoship subscriber has cancelled',
            'display_name' => 'Autoship Cancelled (Seller)',
            'body' => '<p>Dear [seller_full_name],</p>
            <p>A subscriber has cancelled an autoship</p>
            <p>Subscription ID: [subscription_id]</p>
            <p>Purchaser Name: [buyer_full_name]</p>
            <p>Purchaser Email: [buyer_email]</p><p>[subscription_lines]</p><p><br></p>
            <p><br></p><p><br></p><p><br></p><p style="text-align: center;">[back_office_logo]</p>
            <p style="text-align: center;">This is an important notification from [company_name]</p>
            <p style="text-align: center;">[company_name] [company_address]</p>'
        ]);

        CustomEmail::create([
            'title' => 'welcome_customer',
            'subject' => 'An account has been created for you',
            'display_name' => 'Welcome Customer',
            'send_email' => false,
            'body' => '<p>Hi [user_first_name],</p>
            <p>Thanks for visiting [company_name]. A password has been generated for you. You can now log in to see your orders.</p>
            <p>Username: [user_email]</p>
            <p>Password: [generated_password]</p>
            <p>We look forward to seeing you soon</p><p><br></p>
            <p><br></p><p><br></p><p><br></p><p style="text-align: center;">[back_office_logo]</p>
            <p style="text-align: center;">This is an important notification from [company_name]</p>
            <p style="text-align: center;">[company_name] [company_address]</p>'
        ]);

        CustomEmail::create([
           'title' => 'autoship_reminder',
           'subject' => 'Reminder: Upcoming Subscription Order',
           'display_name' => 'Autoship Reminder',
           'body' => '<p>Hi [buyer_first_name],</p>
           <p>This is friendly reminder that you subscription order will be place on [subscription_next_billing_date].
           <p><br></p>
           Your subscription
           [subscription_lines]
           <p><br></p>
           <p>To manager your subscription please log into your customer portal: [backoffice_login_link]</p><p><br></p>
           <p><br></p><p><br></p><p><br></p><p style="text-align: center;">[back_office_logo]</p>'
        ]);

        CustomEmail::where('title', '=', 'fbc_order_received')->delete();
        CustomEmail::where('title', '=', 'new_order_received')->update(['display_name' => 'New Order Notice']);
    }

    public function down()
    {
        DB::table('autoship_visibilities')->where('id', '=', 3)->update(['name' => 'Customer', 'description' => 'Someone who has purchased a product or service.']);
        DB::table('autoship_visibilities')->where('id', '=', 5)->update(['name' => 'Rep', 'description' => 'A fully-featured member and representative. Can only access features related to their sales and resources.']);
        DB::table('autoship_visibilities')->whereIn('id', [1,2,4,6])->delete();
        Schema::table('checkouts', function (Blueprint $table) {
            $table->dropColumn(['autoship_pid']);
        });
        Setting::where('key', '=', 'autoship_enabled')->update(['key' => 'autoship_wholesale']);
        Setting::create([
            'user_id' => 1,
            'key' => 'autoship_retail',
            'value' => '{"show":false, "value":false}',
            'category' => 'auto_ship'
        ]);
        Setting::where('key', '=', 'autoship_reminder')->delete();
        cache()->forget('globalSettings');
        cache()->forget('global-settings');

        CustomEmail::where('title', '=', 'autoship_sub_receipt')->delete();
        CustomEmail::where('title', '=', 'autoship_sub_received')->delete();
        CustomEmail::where('title', '=', 'autoship_sub_cancel')->delete();
        CustomEmail::where('title', '=', 'autoship_sub_cancel_seller')->delete();
        CustomEmail::where('title', '=', 'welcome_customer')->delete();
        CustomEmail::where('title', '=', 'autoship_reminder')->delete();
    }
}
