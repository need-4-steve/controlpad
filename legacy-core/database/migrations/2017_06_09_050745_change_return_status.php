<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\ReturnStatus;

class ChangeReturnStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $returnStatus = ReturnStatus::where('id', 1)->first();
        if (isset($returnStatus) && $returnStatus->name !== 'Open') {
            $seeder = new \UpdateReturnStatusesTableSeeder;
            $seeder->run();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
