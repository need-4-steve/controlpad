<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use CPCommon\Pid\Pid;
use App\Tenant;

class UpdateTenantsAddGUID extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenants', function($table) {
          $table->string('org_id');
        });
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
          $guid = Pid::create();
          $tenant->update(['org_id' => $guid]);
        }
        Schema::table('tenants', function($table) {
          $table->string('org_id')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenants', function($table) {
          $table->dropColumn(['org_id']);
        });
    }
}
