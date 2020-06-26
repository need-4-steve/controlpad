<?php

use Illuminate\Database\Seeder;
use App\Models\Carrier;
use App\Models\ServiceLevel;

class ServiceLevelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        $fedexId = Carrier::where('name', 'FedEx')->first()->id;
        $upsId = Carrier::where('name', 'UPS')->first()->id;
        $uspsId = Carrier::where('name', 'USPS')->first()->id;

        // USPS
        ServiceLevel::create([
            'carrier_id' => $uspsId,
            'token'      => 'usps_priority',
            'name'       => 'Priority Mail'
        ]);
        ServiceLevel::create([
            'carrier_id' => $uspsId,
            'token'      => 'usps_priority_express',
            'name'       => 'Priority Mail Express'
        ]);
        ServiceLevel::create([
            'carrier_id' => $uspsId,
            'token'      => 'usps_first',
            'name'       => 'First Class Mail/Package'
        ]);
        ServiceLevel::create([
            'carrier_id' => $uspsId,
            'token'      => 'usps_parcel_select',
            'name'       => 'Parcel Select'
        ]);
        ServiceLevel::create([
            'carrier_id' => $uspsId,
            'token'      => 'usps_media_mail',
            'name'       => 'Media Mail, only for existing Shippo customers with grandfathered Media Mail option.'
        ]);
        ServiceLevel::create([
            'carrier_id' => $uspsId,
            'token'      => 'usps_priority_mail_international',
            'name'       => 'Priority Mail International'
        ]);
        ServiceLevel::create([
            'carrier_id' => $uspsId,
            'token'      => 'usps_priority_mail_express_international',
            'name'       => 'Priority Mail Express International'
        ]);
        ServiceLevel::create([
            'carrier_id' => $uspsId,
            'token'      => 'usps_first_class_package_international_service',
            'name'       => 'First Class Package International'
        ]);

        // FedEx
        ServiceLevel::create([
            'carrier_id' => $fedexId,
            'token'      => 'fedex_ground',
            'name'       => 'Ground'
        ]);
        ServiceLevel::create([
            'carrier_id' => $fedexId,
            'token'      => 'fedex_home_delivery',
            'name'       => 'Home Delivery'
        ]);
        ServiceLevel::create([
            'carrier_id' => $fedexId,
            'token'      => 'fedex_smart_post',
            'name'       => 'Smartpost'
        ]);
        ServiceLevel::create([
            'carrier_id' => $fedexId,
            'token'      => 'fedex_2_day',
            'name'       => '2 Day'
        ]);
        ServiceLevel::create([
            'carrier_id' => $fedexId,
            'token'      => 'fedex_2_day_am',
            'name'       => '2 Day A.M.'
        ]);
        ServiceLevel::create([
            'carrier_id' => $fedexId,
            'token'      => 'fedex_express_saver',
            'name'       => 'Express Saver'
        ]);
        ServiceLevel::create([
            'carrier_id' => $fedexId,
            'token'      => 'fedex_standard_overnight',
            'name'       => 'Standard Overnight'
        ]);
        ServiceLevel::create([
            'carrier_id' => $fedexId,
            'token'      => 'fedex_priority_overnight',
            'name'       => 'Priority Overnight'
        ]);
        ServiceLevel::create([
            'carrier_id' => $fedexId,
            'token'      => 'fedex_first_overnight',
            'name'       => 'First Overnight'
        ]);
        ServiceLevel::create([
            'carrier_id' => $fedexId,
            'token'      => 'fedex_international_economy',
            'name'       => 'International Economy'
        ]);
        ServiceLevel::create([
            'carrier_id' => $fedexId,
            'token'      => 'fedex_international_priority',
            'name'       => 'International Priority'
        ]);
        ServiceLevel::create([
            'carrier_id' => $fedexId,
            'token'      => 'fedex_international_first',
            'name'       => 'International First'
        ]);
        ServiceLevel::create([
            'carrier_id' => $fedexId,
            'token'      => 'fedex_europe_first_international_priority',
            'name'       => 'Europe First International Priority'
        ]);

        // UPS
        ServiceLevel::create([
            'carrier_id' => $upsId,
            'token'      => 'ups_standard',
            'name'       => 'Standard℠'
        ]);
        ServiceLevel::create([
            'carrier_id' => $upsId,
            'token'      => 'ups_ground',
            'name'       => 'Ground'
        ]);
        ServiceLevel::create([
            'carrier_id' => $upsId,
            'token'      => 'ups_saver',
            'name'       => 'Saver®'
        ]);
        ServiceLevel::create([
            'carrier_id' => $upsId,
            'token'      => 'ups_3_day_select',
            'name'       => 'Three-Day Select®'
        ]);
        ServiceLevel::create([
            'carrier_id' => $upsId,
            'token'      => 'ups_second_day_air',
            'name'       => 'Second Day Air®'
        ]);
        ServiceLevel::create([
            'carrier_id' => $upsId,
            'token'      => 'ups_second_day_air_am',
            'name'       => 'Second Day Air A.M.®'
        ]);
        ServiceLevel::create([
            'carrier_id' => $upsId,
            'token'      => 'ups_next_day_air',
            'name'       => 'Next Day Air®'
        ]);
        ServiceLevel::create([
            'carrier_id' => $upsId,
            'token'      => 'ups_next_day_air_saver',
            'name'       => 'Next Day Air Saver®'
        ]);
        ServiceLevel::create([
            'carrier_id' => $upsId,
            'token'      => 'ups_next_day_air_early_am',
            'name'       => 'Next Day Air Early A.M.®'
        ]);
        ServiceLevel::create([
            'carrier_id' => $upsId,
            'token'      => 'ups_mail_innovations_domestic',
            'name'       => 'Mail Innovations (domestic)'
        ]);
        ServiceLevel::create([
            'carrier_id' => $upsId,
            'token'      => 'ups_surepost',
            'name'       => 'Surepost'
        ]);
        ServiceLevel::create([
            'carrier_id' => $upsId,
            'token'      => 'ups_surepost_lightweight',
            'name'       => 'Surepost Lightweight'
        ]);
        ServiceLevel::create([
            'carrier_id' => $upsId,
            'token'      => 'ups_express',
            'name'       => 'Express®'
        ]);
        ServiceLevel::create([
            'carrier_id' => $upsId,
            'token'      => 'ups_express_plus',
            'name'       => 'Express Plus®'
        ]);
        ServiceLevel::create([
            'carrier_id' => $upsId,
            'token'      => 'ups_expedited',
            'name'       => 'Expedited®'
        ]);
        DB::commit();
    }
}
