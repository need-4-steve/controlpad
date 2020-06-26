<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Email;

class AddNewEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();
        Email::create([
            'title' => 'invoice_reminder',
            'standard' => true,
            'subject' => 'Invoice Reminder',
            'display_name' => 'Unpaid Invoice Reminder',
            'body' => '<p>Dear [first_name] [last_name],</p>
            <p>Thanks for placing an order with [company_name]. This is a reminder that we are waiting to receive your
            payment to complete your order</p><p><br></p>
            <p><br></p><p><br></p><p><br></p>
            <p style="text-align: center;">[back_office_logo]</p>
            <p style="text-align: center;">This is an important notification from [company_name]</p>
            <p style="text-align: center;">[company_name] [company_address]</p>'
        ]);
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
