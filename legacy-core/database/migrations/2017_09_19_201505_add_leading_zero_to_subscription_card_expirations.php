<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\CardToken;

class AddLeadingZeroToSubscriptionCardExpirations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // some tokens were missing leading 0 for 1 digit months
        $tokens = CardToken::whereRaw('LENGTH(expiration) < 4')->get();
        foreach ($tokens as $token) {
            if (strlen($token->expiration) == 3) {
                $token->expiration = '0' . $token->expiration;
                $token->save();
            }
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
