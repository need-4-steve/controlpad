<?php

use Illuminate\Database\Seeder;
use App\Models\Carrier;
use App\Models\ParcelTemplate;

class ParcelTemplatesTableSeeder extends Seeder
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

        // FedEx
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_Box_10kg',
            'name'          => 'Box 10kg',
            'length'        => '15.81',
            'width'         => '12.94',
            'height'        => '10.19',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_Box_25kg',
            'name'          => 'Box 25kg',
            'length'        => '54.80',
            'width'         => '42.10',
            'height'        => '33.50',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_Box_Extra_Large_1',
            'name'          => 'Box Extra Large (1)',
            'length'        => '11.88',
            'width'         => '11.00',
            'height'        => '10.75',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_Box_Extra_Large_2',
            'name'          => 'Box Extra Large (2)',
            'length'        => '15.75',
            'width'         => '14.13',
            'height'        => '6.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_Box_Large_1',
            'name'          => 'Box Large (1)',
            'length'        => '17.50',
            'width'         => '12.38',
            'height'        => '3.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_Box_Large_2',
            'name'          => 'Box Large (2)',
            'length'        => '11.25',
            'width'         => '8.75',
            'height'        => '7.75',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_Box_Medium_1',
            'name'          => 'Box Medium (1)',
            'length'        => '13.25',
            'width'         => '11.50',
            'height'        => '2.38',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_Box_Medium_2',
            'name'          => 'Box Medium (2)',
            'length'        => '11.25',
            'width'         => '8.75',
            'height'        => '4.38',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_Box_Small_1',
            'name'          => 'Box Small (1)',
            'length'        => '12.38',
            'width'         => '10.88',
            'height'        => '1.50',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_Box_Small_2',
            'name'          => 'Box Small (2)',
            'length'        => '11.25',
            'width'         => '8.75',
            'height'        => '4.38',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_Envelope',
            'name'          => 'Envelope',
            'length'        => '12.50',
            'width'         => '9.50',
            'height'        => '0.80',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_Padded_Pak',
            'name'          => 'Padded Pak',
            'length'        => '11.75',
            'width'         => '14.75',
            'height'        => '2.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_Pak_1',
            'name'          => 'Pak (1)',
            'length'        => '15.50',
            'width'         => '12.00',
            'height'        => '0.80',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_Pak_2',
            'name'          => 'Pak (2)',
            'length'        => '12.75',
            'width'         => '10.25',
            'height'        => '0.80',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_Tube',
            'name'          => 'Tube',
            'length'        => '38.00',
            'width'         => '6.00',
            'height'        => '6.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $fedexId,
            'token'         => 'FedEx_XL_Pak',
            'name'          => 'XL Pak',
            'length'        => '17.50',
            'width'         => '20.75',
            'height'        => '2.00',
            'distance_unit' => 'in'
        ]);


        // UPS
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_Box_10kg',
            'name'          => 'Box 10kg',
            'length'        => '410.00',
            'width'         => '335.00',
            'height'        => '265.00',
            'distance_unit' => 'mm'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_Box_25kg',
            'name'          => 'Box 25kg',
            'length'        => '484.00',
            'width'         => '433.00',
            'height'        => '350.00',
            'distance_unit' => 'mm'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_Express_Box',
            'name'          => 'Express Box',
            'length'        => '460.00',
            'width'         => '315.00',
            'height'        => '95.00',
            'distance_unit' => 'mm'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_Express_Box_Large',
            'name'          => 'Express Box Large',
            'length'        => '18.00',
            'width'         => '13.00',
            'height'        => '3.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_Express_Box_Medium',
            'name'          => 'Express Box Medium',
            'length'        => '15.00',
            'width'         => '11.00',
            'height'        => '3.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_Express_Box_Small',
            'name'          => 'Express Box Small',
            'length'        => '13.00',
            'width'         => '11.00',
            'height'        => '2.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_Express_Envelope',
            'name'          => 'Express Envelope',
            'length'        => '12.50',
            'width'         => '9.50',
            'height'        => '2.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_Express_Hard_Pak',
            'name'          => 'Express Hard Pak',
            'length'        => '14.75',
            'width'         => '11.50',
            'height'        => '2.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_Express_Legal_Envelope',
            'name'          => 'Express Legal Envelope',
            'length'        => '15.00',
            'width'         => '9.50',
            'height'        => '2.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_Express_Pak',
            'name'          => 'Express Pak',
            'length'        => '16.00',
            'width'         => '12.75',
            'height'        => '2.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_Express_Tube',
            'name'          => 'Express Tube',
            'length'        => '970.00',
            'width'         => '190.00',
            'height'        => '165.00',
            'distance_unit' => 'mm'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_Laboratory_Pak',
            'name'          => 'Laboratory Pak',
            'length'        => '17.25',
            'width'         => '12.75',
            'height'        => '2.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_MI_BPM',
            'name'          => 'BPM (Mail Innovations - Domestic & International)',
            'length'        => '0.00',
            'width'         => '0.00',
            'height'        => '0.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_MI_BPM_Flat',
            'name'          => 'BPM Flat (Mail Innovations - Domestic & International)',
            'length'        => '0.00',
            'width'         => '0.00',
            'height'        => '0.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_MI_BPM_Parcel',
            'name'          => 'BPM Parcel (Mail Innovations - Domestic & International)',
            'length'        => '0.00',
            'width'         => '0.00',
            'height'        => '0.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_MI_First_Class',
            'name'          => 'First Class (Mail Innovations - Domestic only)',
            'length'        => '0.00',
            'width'         => '0.00',
            'height'        => '0.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_MI_Flat',
            'name'          => 'Flat (Mail Innovations - Domestic only)',
            'length'        => '0.00',
            'width'         => '0.00',
            'height'        => '0.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_MI_Irregular',
            'name'          => 'Irregular (Mail Innovations - Domestic only)',
            'length'        => '0.00',
            'width'         => '0.00',
            'height'        => '0.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_MI_Machinable',
            'name'          => 'Machinable (Mail Innovations - Domestic only)',
            'length'        => '0.00',
            'width'         => '0.00',
            'height'        => '0.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_MI_MEDIA_MAIL',
            'name'          => 'Media Mail (Mail Innovations - Domestic only)',
            'length'        => '0.00',
            'width'         => '0.00',
            'height'        => '0.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_MI_Parcel',
            'name'          => 'Parcel (Mail Innovations - Domestic only)',
            'length'        => '0.00',
            'width'         => '0.00',
            'height'        => '0.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_MI_Parcel_Post',
            'name'          => 'Parcel Post (Mail Innovations - Domestic only)',
            'length'        => '0.00',
            'width'         => '0.00',
            'height'        => '0.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_MI_Priority',
            'name'          => 'Priority (Mail Innovations - Domestic only)',
            'length'        => '0.00',
            'width'         => '0.00',
            'height'        => '0.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_MI_Standard_Flat',
            'name'          => 'Standard Flat (Mail Innovations - Domestic only)',
            'length'        => '0.00',
            'width'         => '0.00',
            'height'        => '0.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_Pad_Pak',
            'name'          => 'Pad Pak',
            'length'        => '14.75',
            'width'         => '11.00',
            'height'        => '2.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $upsId,
            'token'         => 'UPS_Pallet',
            'name'          => 'Pallet',
            'length'        => '120.00',
            'width'         => '80.00',
            'height'        => '200.00',
            'distance_unit' => 'cm'
        ]);


        // USPS
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_FlatRateCardboardEnvelope',
            'name'          => 'Flat Rate Cardboard Envelope',
            'length'        => '12.50',
            'width'         => '9.50',
            'height'        => '0.75',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_FlatRateEnvelope',
            'name'          => 'Flat Rate Envelope',
            'length'        => '12.50',
            'width'         => '9.50',
            'height'        => '0.75',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_FlatRateGiftCardEnvelope',
            'name'          => 'Flat Rate Gift Card Envelope',
            'length'        => '10.00',
            'width'         => '7.00',
            'height'        => '0.75',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_FlatRateLegalEnvelope',
            'name'          => 'Flat Rate Legal Envelope',
            'length'        => '15.00',
            'width'         => '9.50',
            'height'        => '0.75',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_FlatRatePaddedEnvelope',
            'name'          => 'Flat Rate Padded Envelope',
            'length'        => '12.50',
            'width'         => '9.50',
            'height'        => '1.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_FlatRateWindowEnvelope',
            'name'          => 'Flat Rate Window Envelope',
            'length'        => '10.00',
            'width'         => '5.00',
            'height'        => '0.75',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_IrregularParcel',
            'name'          => 'Irregular Parcel',
            'length'        => '0.00',
            'width'         => '0.00',
            'height'        => '0.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_LargeFlatRateBoardGameBox',
            'name'          => 'Large Flat Rate Board Game Box',
            'length'        => '24.06',
            'width'         => '11.88',
            'height'        => '3.13',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_LargeFlatRateBox',
            'name'          => 'Large Flat Rate Box',
            'length'        => '12.25',
            'width'         => '12.25',
            'height'        => '6.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_APOFlatRateBox',
            'name'          => 'APO/FPO/DPO Large Flat Rate Box',
            'length'        => '12.25',
            'width'         => '12.25',
            'height'        => '6.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_LargeVideoFlatRateBox',
            'name'          => "Flat Rate Large Video Box (Int'l only)",
            'length'        => '9.60',
            'width'         => '6.40',
            'height'        => '2.20',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_MediumFlatRateBox1',
            'name'          => 'Medium Flat Rate Box 1',
            'length'        => '11.25',
            'width'         => '8.75',
            'height'        => '6.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_MediumFlatRateBox2',
            'name'          => 'Medium Flat Rate Box 2',
            'length'        => '14.00',
            'width'         => '12.00',
            'height'        => '3.50',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_RegionalRateBoxA1',
            'name'          => 'Regional Rate Box A1',
            'length'        => '10.13',
            'width'         => '7.13',
            'height'        => '5.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_RegionalRateBoxA2',
            'name'          => 'Regional Rate Box A2',
            'length'        => '13.06',
            'width'         => '11.06',
            'height'        => '2.50',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_RegionalRateBoxB1',
            'name'          => 'Regional Rate Box B1',
            'length'        => '12.25',
            'width'         => '10.50',
            'height'        => '5.50',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_RegionalRateBoxB2',
            'name'          => 'Regional Rate Box B2',
            'length'        => '16.25',
            'width'         => '14.50',
            'height'        => '3.00',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_SmallFlatRateBox',
            'name'          => 'Small Flat Rate Box',
            'length'        => '8.69',
            'width'         => '5.44',
            'height'        => '1.75',
            'distance_unit' => 'in'
        ]);
        ParcelTemplate::create([
            'user_id'       => config('site.apex_user_id'),
            'carrier_id'    => $uspsId,
            'token'         => 'USPS_SmallFlatRateEnvelope',
            'name'          => 'Small Flat Rate Envelope',
            'length'        => '10.00',
            'width'         => '6.00',
            'height'        => '4.00',
            'distance_unit' => 'in'
        ]);
        DB::commit();
    }
}
