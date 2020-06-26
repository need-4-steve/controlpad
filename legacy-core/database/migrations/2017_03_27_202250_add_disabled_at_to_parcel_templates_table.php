<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDisabledAtToParcelTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parcel_templates', function (Blueprint $table) {
            $table->timestamp('disabled_at')->nullable()->default(null);
            $table->boolean('show_rep');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parcel_templates', function (Blueprint $table) {
            $table->dropIfExists('disabled_at');
            $table->dropIfExists('show_rep');
        });
    }
}
