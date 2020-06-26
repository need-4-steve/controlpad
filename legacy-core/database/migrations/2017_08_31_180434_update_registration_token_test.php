<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\RegistrationToken;

class UpdateRegistrationTokenTest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $token = RegistrationToken::where('email', 'testaug07afn@gmail.com')->first();
        if (isset($token)) {
            $token->user_id = 1823;
            $token->save();
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
