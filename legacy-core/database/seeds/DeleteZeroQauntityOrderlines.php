<?php

use Illuminate\Database\Seeder;
use App\Models\Orderline;

class DeleteZeroQauntityOrderlines extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Orderline::where('quantity', 0)->forceDelete();
    }
}
