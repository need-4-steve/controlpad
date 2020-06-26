<?php

use Illuminate\Database\Seeder;
use App\Models\Order;

class TaxUncommitFixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $chalkCommittedInvoices = [
            'OJCVKK-3644',
            'OZDLVZ-3540',
            'O3FLBJ-3277',
            'O7IJNP-3092',
            'OMCJRL-2869',
            'OCKDB5-2868',
            'OL5SRL-2867',
            'O6Y6M5-2866',
            'OGCSE1-2662',
            'OCYGGG-2661',
            'OCOFHU-2660',
            'OLGALT-2659',
            'OBNJTQ-1503',
            'OBGQYK-3616',
            'OAMIZM-1386',
            'ORYIQK-1383',
            'OIHH0I-2896',
            'O2MCHT-2895',
            'OMNBGS-1184',
            'OTYYFC-3182',
            'OTPJ0Y-1997',
            'OYVWAM-1799',
            'OWYLDX-1797',
            'OSQLFX-3883',
            'OFJJNF-3047',
            'O1T5AT-3046',
            'OFW0KY-3045',
            'OCX4B2-2202',
            'OFNZDM-2200',
            'OQCCWO-2199',
            'OMHB0H-2198',
            'OGOBNI-1226',
            'O4D6RL-3913',
            'OMSG3I-3560',
            'OMYM4T-6259',
            'OC88Q6-5014',
            'OCUOUY-5000',
            'ON0L8I-4807',
            'OBQCFM-4808',
            'OZB5FJ-4192'
        ];

        DB::beginTransaction();
        // maxwell has all commits from 8-24 on uncommitted
        $companyName = config('site.company_name');
        if ($companyName == 'shopmaxwell') {
            // get all uncommitted orders that may have been marked incorrectly
            $orders = Order::where('created_at', '>=', '2017-08-24 00:00:00')->get();
            foreach ($orders as $order) {
                $order->taxes_committed = 0;
                $order->save();
            }
        }

        // chalk couture has some only the above orders committed
        if ($companyName == 'chalkcouture.com') {
            $orders = Order::all();
            foreach ($orders as $order) {
                if (!in_array($order->receipt_id, $chalkCommittedInvoices)) {
                    $order->taxes_committed = 0;
                    $order->save();
                }
            }
        }
        DB::commit();
    }
}
