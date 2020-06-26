<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\User;

class FixDeletedUserEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();
        $users = User::onlyTrashed()->get();
        foreach ($users as $user) {
            if (!preg_match('/\s/', $user->email)) {
                $user->update([
                    'email' => $user->email.' '.date("Ymdhis"),
                    'public_id' => null
                ]);
            }
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
