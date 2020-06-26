<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\ReturnStatus;

class UpdateReturnStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // this whole solution feels terrible and hacky, but I can't see
        // another way to do it as conceisely.
        //
        // Regretably,
        // Brian

        Schema::table('return_statuses', function (Blueprint $table) {
            // we cannot make it unique at this point otherwise it
            // will throw a 'duplicate entry' error
            $table->string('keyname');
        });

        $statuses = ReturnStatus::all();
        foreach ($statuses as $returnStatus) {
            $returnStatus->keyname = str_slug($returnStatus->name);
            $returnStatus->save();
        }

        Schema::table('return_statuses', function (Blueprint $table) {
            // making it unique now
            $table->string('keyname')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('return_statuses', function (Blueprint $table) {
            $table->dropColumn('keyname');
        });
    }
}
