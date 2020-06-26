<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->truncate();
        DB::beginTransaction();
        Setting::create([
            'user_id' => 1,
            'key' => 'google_store_url',
            'value' => '{"value": "google store url", "show": false}',
            'category' => 'link',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'ios_store_url',
            'value' => '{"value": "ios store url", "show": false}',
            'category' => 'link',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'product_locator',
            'value' => '{"value": "product locator", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'social_media_link',
            'value' => '{"value": "social media link", "show": false}',
            'category' => 'link',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'terms',
            'value' => '{"value": "/terms", "show": false}',
            'category' => 'link',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'return_policy',
            'value' => '{"value": "/return-policy", "show": false}',
            'category' => 'link',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'about_us',
            'value' => '{"value": "http://www.controlpad.com", "show": false}',
            'category' => 'link',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'address',
            'value' => '{"value": "553 East Technology Ave Building C, Ste 1300 Orem, Utah 84097", "show": true}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'phone',
            'value' => '{"value": "800-830-4493", "show": false}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'company_email',
            'value' => '{"value": "info@controlpad.com", "show": true}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'order_notification_email',
            'value' => '{"value": "admin@controlpad.com", "show": true}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'from_email',
            'value' => '{"value": "no-reply@controlpad.com", "show": true}',
            'category' => 'general',
        ]);

        Setting::create([
            'user_id' => 1,
            'key' => 'corp_coupons',
            'value' => json_encode(['value' => false, 'show' => true]),
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'title_rep',
            'value' => '{"value": "Rep"}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'title_announcement',
            'value' => '{"value": "Announcements"}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_background_text_1',
            'value' => '{"value": "Independent CEO", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_background_text_2',
            'value' => '{"value": "sell more", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_background_text_3',
            'value' => '{"value": "through self promotion", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'title_store',
            'value' => '{"value": "Controlpad"}',
            'category' => 'store',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'loading_icon',
            'value' => '{"value": "https://s3-us-west-2.amazonaws.com/controlpad/ControlpadLoading50.gif"}',
            'category' => 'brand',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'favicon',
            'value' => '{"value": "https://s3-us-west-2.amazonaws.com/controlpad/controlpadfavicon.ico"}',
            'category' => 'brand',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_welcome',
            'value' => '{"value": "Welcome to Controlpad, thanks for joining the team." , "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'back_office_logo',
            'value' => '{"value": "https://s3-us-west-2.amazonaws.com/controlpad/cp-logo-bk.png"}',
            'category' => 'brand',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'front_office_logo_black',
            'value' => '{"value": "https://s3-us-west-2.amazonaws.com/controlpad/CPLogo-Black-Horizontal.png"}',
            'category' => 'brand',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'front_office_logo_white',
            'value' => '{"value": "https://s3-us-west-2.amazonaws.com/controlpad/Cp-logo-white.png"}',
            'category' => 'brand',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'store_image',
            'value' => '{"value": "https://s3-us-west-2.amazonaws.com/controlpad/CPlogo-Black-Horizonal.png"}',
            'category' => 'brand',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'company_name',
            'value' => '{"value": "Controlpad"}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'hex_color',
            'value' => '{"value": "Controlpad"}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'background_image_1',
            'value' => '{"value": "https://s3-us-west-2.amazonaws.com/controlpad/CPlogo-Black-Horizonal.png"}',
            'category' => 'brand',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'background_image_2',
            'value' => '{"value": "https://s3-us-west-2.amazonaws.com/controlpad/CPlogo-Black-Horizonal.png", "show": true}',
            'category' => 'brand',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'background_image_3',
            'value' => '{"value": "https://s3-us-west-2.amazonaws.com/controlpad/CPlogo-Black-Horizonal.png", "show": true}',
            'category' => 'brand',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_background_image_1',
            'value' => '{"value": "https://s3-us-west-2.amazonaws.com/controlpad/CPlogo-Black-Horizonal.png"}',
            'category' => 'brand',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_background_image_2',
            'value' => '{"value": "https://s3-us-west-2.amazonaws.com/controlpad/CPlogo-Black-Horizonal.png", "show": true}',
            'category' => 'brand',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_background_image_3',
            'value' => '{"value": "https://s3-us-west-2.amazonaws.com/controlpad/CPlogo-Black-Horizonal.png", "show": true}',
            'category' => 'brand',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_custom_prices',
            'value' => '{"value": "rep prices", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_facebook_login',
            'value' => '{"value": "facebook logins", "show": false}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_instagram_login',
            'value' => '{"value": "instagram logins", "show": false}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_gmail_login',
            'value' => '{"value": "gmail logins", "show": false}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'subdomain_blacklist',
            'value' => '{"value": "store", "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_create_product',
            'value' => '{"value": "Resellers can create products", "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'sold_out',
            'value' => '{"value": "0", "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'landing_page',
            'value' => '{"value": "login", "show": true}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_purchase_inventory',
            'value' => '{"value": "Affiliates can purchase inventory", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_purchase_inventory',
            'value' => '{"value": "Resellers can purchase inventory", "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_my_orders',
            'value' => '{"value": "Resellers can see orders that they have purchased", "show": true}',
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
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_payment_option',
            'value' => '{"value": "", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'store_join_link',
            'value' => '{"value": "Join", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_ewallet_withdraw',
            'value' => '{"value": "Affiliates have access to withdraw from their balance", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_ewallet_withdraw',
            'value' => '{"value": "Resellers have access to withdraw from their balance", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_ewallet_taxes_balance',
            'value' => '{"value": "Affiliates can pay taxes with their ewallet balance", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_ewallet_taxes_paid_first',
            'value' => '{"value": "Affiliates need to pay taxes first before withdrawing from eWallet", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_ewallet_taxes_ach',
            'value' => '{"value": "Affiliates can pay taxes with eCheck", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_ewallet_taxes_credit_card',
            'value' => '{"value": "Affiliates can pay taxes with credit card", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_ewallet_taxes_balance',
            'value' => '{"value": "Resellers can pay taxes with their ewallet balance", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_ewallet_taxes_paid_first',
            'value' => '{"value": "Resellers need to pay taxes first before withdrawing from eWallet", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_ewallet_taxes_ach',
            'value' => '{"value": "Resellers can pay taxes with eCheck", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_ewallet_taxes_credit_card',
            'value' => '{"value": "Resellers can pay taxes with credit card", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_ewallet_pending_balance',
            'value' => '{"show":true, "value":""}',
            'category' => 'rep'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_ewallet_pending_balance',
            'value' => '{"show":true, "value":""}',
            'category' => 'rep'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_transfer',
            'value' => '{"show": false, "value":"Reps can sell to other reps at wholesale price"}',
            'category' => 'rep'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_link',
            'value' => '{"show":false, "value":{"url": "", "display_on_rep_site": false, "display_name": ""}}',
            'category' => 'rep'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'collect_phone_on_registration',
            'value' => '{"value": "0", "show": false}',
            'category' => 'registration',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'require_phone_on_registration',
            'value' => '{"value": "0", "show": false}',
            'category' => 'registration',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'discount_wholesale_percent',
            'value' => '{"value": 1, "show": true}',
            'category' => 'commission_engine',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'require_sponsor_id_on_registration',
            'value' => '{"value": "0", "show": false}',
            'category' => 'registration',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'registration_shipping',
            'value' => '{"value": true, "show": true}',
            'category' => 'registration',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'registration_coupon',
            'value' => '{"value": null, "show": false}',
            'category' => 'registration'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'join_redirect',
            'value' => '{"value": null, "show": false}',
            'category' => 'registration'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'collect_sponsor_id',
            'value' => '{"value": null, "show": false}',
            'category' => 'registration',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'registration_payment_options',
            'value' => '{"show":false, "value":{"credit_card": true, "debit_card": false, "e_check": false}}',
            'category' => 'registration'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'checkout_v2',
            'value' => '{"value": "Checkout Version 2.0", "show": true}',
            'category' => 'checkout',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'ein',
            'value' => '{"value": " ", "show": false}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'sub_grace_period',
            'value' => '{"value": "45", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_youtube',
            'value' => '{"value": "Resellers have access to YouTube", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_youtube',
            'value' => '{"value": "Affiliate have access to YouTube", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'corp_youtube',
            'value' => '{"value": "Corporate have access to YouTube", "show": false}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'olark_chat_integration',
            'value' => '{"value": " ", "show": false}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'tawk_chat_integration',
            'value' => '{"value": " ", "show": false}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'tax_calculation',
            'value' => '{"value": "Taxes are calculated", "show": false}',
            'category' => 'taxes'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_logo',
            'value' => '{"value": "Resellers can upload their own logo", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_address_store',
            'value' => '{"value": "Reseller shows address in footer on their replicated site", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'payquicker',
            'value' => '{"value": " ", "show": false}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'direct_deposit',
            'value' => '{"value": "Use Direct Deposit", "show": true}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'commission_engine_link',
            'value' => '{"value": "", "show": false}',
            'category' => 'commission_engine',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'tax_subscription',
            'value' => '{"value": "Apply Taxes on Subscriptions", "show": false}',
            'category' => 'taxes',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'edit_join_date',
            'value' => '{"value": "Can edit join date.", "show": false}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,

            'key' => 'rep_orders_tab',
            'value' => '{"value": false, "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_sales_tab',
            'value' => '{"value": false, "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_edit_inventory',
            'value' => '{"value": "Can Edit Inventory", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'low_inventory_alert_rep',
            'value' => '{"show": false, "value": 5}',
            'category' => 'inventory'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'user_status_sell_url',
            'value' => '{"value": "", "show": true}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'store_builder_admin',
            'value' => '{"value": "Admin can Use Store Builder", "show": false}',
            'category' => 'store',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'store_builder_reseller',
            'value' => '{"value": "Reseller can Use Store Builder", "show": false}',
            'category' => 'store',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'new_product_create',
            'value' => '{"value": "Use Product Create Using Microservice", "show": false}',
            'category' => 'store',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'wholesale_cart_min',
            'value' => '{"value": "dollar", "show": false}',
            'category' => 'store',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'wholesale_cart_min_amount',
            'value' => '{"value": "1", "show": false}',
            'category' => 'store',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'wholesale_low_inventory',
            'value' => '{"value": "25", "show": false}',
            'category' => 'store',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'retail_low_inventory',
            'value' => '{"value": "25", "show": false}',
            'category' => 'store',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'variant_claim_number',
            'value' => '{"value": "Use variant claim number.", "show": false}',
            'category' => 'store',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'about_rep',
            'value' => '{"show":false, "value":null}',
            'category' => 'store'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'low_inventory_alert_corp',
            'value' => '{"show": false, "value": 10}',
            'category' => 'inventory'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'einvoice_expire_time',
            'value' => '{"value": "720", "show": false}',
            'category' => 'general',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_custom_order',
            'value' => '{"value": "Reseller can create custom orders", "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_custom_order',
            'value' => '{"value": "Affiliate can create custom orders", "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_shipping_rates',
            'value' => '{"value": "Affiliate can create custom orders", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
              'user_id' => 1,
              'key' => 'affiliate_custom_corp',
              'value' => '{"value": "Affiliate can user corporate inventory", "show": false}',
              'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_custom_corp',
            'value' => '{"value": "Reseller can create custom orders from corporate inventory", "show": false}',
            'category' => 'rep',
        ]);

        Setting::create([
            'user_id' => 1,
            'key' => 'allow_reps_events_img',
            'value' => '{"value": "Allow images by reps", "show": false}',
            'category' => 'events',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'allow_reps_events',
            'value' => '{"value": "Allow reps to have events on", "show": false}',
            'category' => 'events',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'events_as_replicated_site',
            'value' => '{"value": "Make events page the landing page for replicated sites ", "show": false}',
            'category' => 'events',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'events_default_img',
            'value' => '{"value": ""}',
            'category' => 'events',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'events_title',
            'value' => '{"show":true, "value":{"plural": "Events", "single": "Event"}}',
            'category' => 'events'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'simple_commissions',
            'value' => '{"value": 0, "show": false}',
            'category' => 'commission_engine',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_ewallet_balance',
            'value' => '{"value": 0, "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_ewallet_commission',
            'value' => '{"value": 0, "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_ewallet_taxes',
            'value' => '{"value": 0, "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_ewallet_balance',
            'value' => '{"value": 0, "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_ewallet_commission',
            'value' => '{"value": 0, "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_ewallet_taxes',
            'value' => '{"value": 0, "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'comm_engine_starter_kits',
            'value' => '{"value": "Enable Starter Kits to be written over to the commission engine.", "show": false}',
            'category' => 'commission_engine',
        ]);
        Setting::create([
            'key' => 'enable_shipping_label_creation',
            'value' => '{"value": "Enable Shipping Label Creation", "show": false}',
            'category' => 'shipping',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'back_office_logo_inverse',
            'value' => '{"value": "https://s3-us-west-2.amazonaws.com/controlpad/Cp-logo-white.png", "show": false}',
            'category' => 'brand',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'inventory_confirmation',
            'value' => '{"value": "Require inventory to be confirmed on order before transferring.", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'comm_engine_tab',
            'value' => '{"value": false, "show": false}',
            'category' => 'commission_engine'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'comm_engine_type',
            'value' => '{"value": "MCOM", "show": false}',
            'category' => 'commission_engine'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'autoship_enabled',
            'value' => '{"value": false, "show": false}',
            'category' => 'auto_ship',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'autoship_display_name',
            'value' => '{"value": "Autoship", "show": false}',
            'category' => 'auto_ship',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'autoship_purchase_label',
            'value' => '{"value": "Create Autoship", "show": false}',
            'category' => 'auto_ship',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'autoship_default_purchase',
            'value' => '{"value": false, "show": false}',
            'category' => 'auto_ship',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'autoship_reminder',
            'value' => '{"show":true, "value":5}',
            'category' => 'auto_ship'
        ]);
        // Can purchase inventory using ewallet
        Setting::create([
            'user_id' => 1,
            'key' => 'wholesale_ewallet',
            'value' => '{"value": "", "show": true}',
            'category' => 'rep',
        ]);
        // Can purchase inventory using card on file
        Setting::create([
            'user_id' => 1,
            'key' => 'wholesale_card_token',
            'value' => '{"value": "", "show": true}',
            'category' => 'rep',
        ]);
        // Affiliate sales go to the rep team (which should use a retail mid)
        Setting::create([
            'user_id' => 1,
            'key' => 'payman_affiliate_team',
            'value' => '{"value": "rep", "show": false}',
            'category' => 'checkout'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'lms_link',
            'value' => '{"value": "", "show": false}',
            'category' => 'rep'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'lms_link_name',
            'value' => '{"value": "LMS", "show": false}',
            'category' => 'rep'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'shipping_link',
            'value' => '{"value": "", "show": false}',
            'category' => 'rep'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'shipping_link_text',
            'value' => '{"value": "Shipping Link", "show": false}',
            'category' => 'rep'
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'self_pickup_wholesale',
            'value' => '{"value": "allow self pickup on wholesale", "show": false}',
            'category' => 'store',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'self_pickup_reseller',
            'value' => '{"value": "allow self pickup for resellers", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_ewallet',
            'value' => json_encode(['value' => false, 'show' => true]),
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_coupons',
            'value' => json_encode(['value' => false, 'show' => true]),
            'category' => 'rep',
        ]);
        $companyUser = DB::table('users')->select('pid')->where('id', '=', config('site.apex_user_id'))->first();
        if ($companyUser != null) {
            Setting::create([
                'user_id' => 1,
                'key' => 'company_pid',
                'value' => '{"value": "' . $companyUser->pid . '", "show": true}',
                'category' => 'general'
            ]);
        }
        DB::commit();
    }
}
