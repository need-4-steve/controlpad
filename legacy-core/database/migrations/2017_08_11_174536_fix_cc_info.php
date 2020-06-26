<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\CardToken;

class FixCcInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $cardToken = CardToken::get();
        DB::beginTransaction();
        foreach ($cardToken as $token) {
            $token->card_digits = "************" . substr($token->card_digits, -4);
            $token->save();
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
