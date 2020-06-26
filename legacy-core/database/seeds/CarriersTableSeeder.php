<?php

use Illuminate\Database\Seeder;
use App\Models\Carrier;

class CarriersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        Carrier::create([
            'name'  => 'Australia Post',
            'token' => 'australia_post',
            'account_id' => null
        ]);
        Carrier::create([
            'name'  => 'Asendia',
            'token' => 'asendia_us',
            'account_id' => null
        ]);
        Carrier::create([
            'name'  => 'Canada Post',
            'token' => 'canada_post',
            'account_id' => null
        ]);
        Carrier::create([
            'name'  => 'Deutsche Post',
            'token' => 'deutsche_post',
            'account_id' => '668342faf87847378acb6896401763e2'
        ]);
        Carrier::create([
            'name'  => 'DHL Germany',
            'token' => 'dhl_germany',
            'account_id' => null
        ]);
        Carrier::create([
            'name'  => 'DHL eCommerce',
            'token' => 'dhl_ecommerce',
            'account_id' => null
        ]);
        Carrier::create([
            'name'  => 'DHL Express',
            'token' => 'dhl_express',
            'account_id' => '82d4176def514463b64e0c7069769c19'
        ]);
        Carrier::create([
            'name'  => 'FedEx',
            'token' => 'fedex',
            'account_id' => null
        ]);
        Carrier::create([
            'name'  => 'GLS Germany',
            'token' => 'gls_de',
            'account_id' => null
        ]);
        Carrier::create([
            'name'  => 'GLS France',
            'token' => 'gls_fr',
            'account_id' => null
        ]);
        Carrier::create([
            'name'  => 'Hermes UK',
            'token' => 'hermes_uk',
            'account_id' => null
        ]);
        Carrier::create([
            'name'  => 'Lasership',
            'token' => 'lasership',
            'account_id' => null
        ]);
        Carrier::create([
            'name'  => 'Mondial Relay',
            'token' => 'mondial_relay',
            'account_id' => null
        ]);
        Carrier::create([
            'name'  => 'Newgistics',
            'token' => 'newgistics',
            'account_id' => null
        ]);
        Carrier::create([
            'name'  => 'OnTrac',
            'token' => 'ontrac',
            'account_id' => null
        ]);
        Carrier::create([
            'name'  => 'Purolator',
            'token' => 'purolator',
            'account_id' => null
        ]);
        Carrier::create([
            'name'  => 'UPS',
            'token' => 'ups',
            'account_id' => null
        ]);
        Carrier::create([
            'name'  => 'USPS',
            'token' => 'usps',
            'account_id' => 'c358cbcf51174cd2adef08dfa44eb79d'
        ]);
        DB::commit();
    }
}
