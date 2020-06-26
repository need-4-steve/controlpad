<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Address;

class AddEmptyDefaultOnName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('name')->default('')->change();
        });
        $addresses = Address::whereNull('name')->get();
        DB::beginTransaction();
        foreach ($addresses as $address) {
            $user = $address->addressable()->first();
            if (isset($user->full_name)) {
                $address->name = $user->full_name;
            } else {
                $address->name = '';
            }
            $address->save();
        }
        DB::commit();
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
