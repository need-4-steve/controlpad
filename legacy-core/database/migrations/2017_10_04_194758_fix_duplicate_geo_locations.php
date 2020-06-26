<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\GeoLocation;

class FixDuplicateGeoLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('geo_locations', function (Blueprint $table) {
            $table->index('address_id');
        });

        // Delete older updated_at duplicate records that have the same address_id
        DB::statement("DELETE location1 FROM geo_locations location1, geo_locations location2 WHERE location1.updated_at < location2.updated_at AND location1.address_id = location2.address_id;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('geo_locations', function (Blueprint $table) {
            $table->dropIndex(['address_id']);
        });
    }
}
