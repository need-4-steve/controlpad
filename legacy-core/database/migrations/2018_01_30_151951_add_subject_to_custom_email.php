<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\CustomEmail;

class AddSubjectToCustomEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_emails', function (Blueprint $table) {
            $table->string('subject');
            $table->text('body');
            $table->string('display_name');
            $table->dropColumn('greeting');
            $table->dropColumn('content_1');
            $table->dropColumn('content_2');
            $table->dropColumn('content_3');
            $table->dropColumn('signature');
        });

        $email = CustomEmail::where('title', 'expire_notice');
        if (isset($email) && $email) {
            $email->update([
                'subject' => 'Subscription Renewed Notice',
                'display_name' => 'Subscription Renewed Notice',
                'body' => '<p>Dear [first_name] [last_name],</p>
                <p>Your subscription will be renewed on [billing_date]</p><p><br></p>
                <p><br></p><p><br></p><p><br></p>
                <p style="text-align: center;">[back_office_logo]</p>
                <p style="text-align: center;">This is an important notification from [company_name]</p>
                <p style="text-align: center;">[company_name] [company_address]</p>'
            ]);
        }
        $email = CustomEmail::where('title', 'renew_success');
        if (isset($email) && $email) {
            $email->update([
                'subject' => 'Subscription Renewed Successfully',
                'display_name' => 'Subscription Renewed Successfully',
                'body' => "<p>Dear [first_name] [last_name],</p>
                <p>We've sent this email to inform you that your auto-renewal of your&nbsp;
                subscription has succeeded.</p><p><br></p>
                <p><br></p><p><br></p><p><br></p>
                <p style='text-align: center;'>[back_office_logo]</p>
                <p style='text-align: center;'>This is an important notification from [company_name]</p>
                <p style='text-align: center;'>[company_name] [company_address]</p>"
            ]);
        }
        $email = CustomEmail::where('title', 'renew_fail');
        if (isset($email) && $email) {
            $email->update([
                'subject' => 'Subscription Failed',
                'display_name' => 'Subscription Failed',
                'body' => "<p>[first_name] [last_name],</p>
                    <p>We've sent this email to inform you that your auto-renewal of your&nbsp;subscription has failed.
                    Your subscription ended on [billing_date]</p>
                    <p>The reason was logged as the following: </p><p>[reason]</p>
                    <p>Please ensure that all of your information in your account is up to date. If you would like,
                     you may sign in to your account and renew it using a different credit card. Please be
                     aware that doing so will mean that future subscription renewals will be charged to that card.</p>
                    <p><br></p><p><br></p><p><br></p><p style='text-align: center;'>[back_office_logo]</p>
                    <p style='text-align: center;'>This is an important notification from [company_name]</p>
                    <p style='text-align: center;'>[company_name] [company_address]</p>"
            ]);
        }
        $email = CustomEmail::where('title', 'missing_card');
        if (isset($email) && $email) {
            $email->update([
                'subject' => 'No Card on File',
                'display_name' => 'Missing Card',
                'body' => '<p>Dear [first_name] [last_name],</p>
                <p>You do not have a credit card on file for your subscription</p>
                <p><br></p><p><br></p><p><br></p><p style="text-align: center;">[back_office_logo]</p>
                <p style="text-align: center;">This is an important notification from [company_name]</p>
                <p style="text-align: center;">[company_name] [company_address]</p>'
            ]);
        }
        $email = CustomEmail::where('title', 'card_update');
        if (isset($email) && $email) {
            $email->update([
                'subject' => 'Subscription Expired',
                'display_name' => 'Subscription Failed',
                'body' => '<p>Dear [first_name] [last_name],</p>
                <p>Your credit card on file needs to be updated.</p>
                <p>If this is not done soon you will be locked out of the system.</p>
                <p><br></p><p><br></p><p><br></p><p style="text-align: center;">[back_office_logo]</p>
                <p style="text-align: center;">This is an important notification from [company_name]</p>
                <p style="text-align: center;">[company_name] [company_address]</p>'
            ]);
        }
        $email = CustomEmail::where('title', 'order_receipt');
        if (isset($email) && $email) {
            $email->update([
                'subject' => 'Order [order_receipt_id]',
                'display_name' => 'Order Receipt',
                'body' => "<p>[company_name] Receipt</p>
                <p>Order Number: [order_receipt_id]</p>
                <p>Hi [first_name],</p><p>We are currently getting your order ready to be shipped.
                We will notify you when it has been sent.</p>
                <p>Your Order Details</p><p>[orderlines]</p>
                <p>Subtotal Price: [order_subtotal]</p>
                <p>Discount: [order_discount]</p>
                <p>Tax: [order_tax]</p>
                <p>Shipping: [order_shipping]</p>
                <p>Total Price: [order_total]</p>
                <p><br></p><p>If you have any questions or concerns, please don't hesitate to
                contact customer support.</p>
                <p><br></p><p><br></p><p><br></p><p style='text-align: center;'>[back_office_logo]</p>
                <p style='text-align: center;'>This is an important notification from [company_name]</p>
                <p style='text-align: center;'>[company_name] [company_address]</p>"
            ]);
        }
        $email = CustomEmail::where('title', 'fbc_order_received');
        if (isset($email) && $email) {
            $email->update([
                'subject' => 'FBC Order',
                'display_name' => 'Order Received FBC',
                'body' => '<p>Dear [first_name] [last_name]</p>
                <p>We wanted to let you know that one or more of your fulfilled by corporate items has
                been sold on [company_name] Congratulations! You are not required to take any action for this order.
                If you would like to view the details of this transaction, please log in to your back office.</p>
                <p><br></p><p><br></p><p><br></p><p style="text-align: center;">[back_office_logo]</p>
                <p style="text-align: center;">This is an important notification from [company_name]</p>
                <p style="text-align: center;">[company_name] [company_address]</p>'
            ]);
        }
        $email = CustomEmail::where('title', 'new_order_received');
        if (isset($email) && $email) {
            $email->update([
                'subject' => 'You Have an New Order!',
                'display_name' => 'New Order Notice',
                'body' => '<p>Dear [first_name],</p>
                <p>Congratulations! You just sold an item! Here are the details from this order:</p>
                <p>Order ID: [order_receipt_id]</p>
                <p>Purchaser Name: [customer_first_name] [customer_last_name]</p>
                <p>Purchaser Email: [customer_email]</p><p>[orderlines]</p><p><br></p>
                <p><br></p><p><br></p><p><br></p>
                <p style="text-align: center;">[back_office_logo]</p>
                <p style="text-align: center;">This is an important notification from [company_name]</p>
                <p style="text-align: center;">[company_name] [company_address]</p>'
            ]);
        }
        $email = CustomEmail::where('title', 'fulfilled');
        if (isset($email) && $email) {
            $email->update([
                'subject' => 'Order Shipped',
                'display_name' => 'Order Fulfilled',
                'body' => '<p>Dear [first_name],</p>
                <p>We are pleased to inform you that the following order has shipped</p>
                <p>[orderlines]</p>
                <p>Subtotal Price: [order_subtotal]</p>
                <p>Discount: [order_discount]</p>
                <p>Tax: [order_tax]</p>
                <p>Shipping: [order_shipping]</p>
                <p>Total Price: [order_total]</p>
                <p><br></p><p><br></p><p><br></p>
                <p style="text-align: center;">[back_office_logo]</p>
                <p style="text-align: center;">This is an important notification from [company_name]</p>
                <p style="text-align: center;">[company_name] [company_address]</p>'
            ]);
        }
        $email = CustomEmail::where('title', 'invoice');
        if (isset($email) && $email) {
            $email->update([
                'subject' => 'Invoice',
                'display_name' => 'Invoice',
                'body' => '<p>Hi [first_name],</p>
                <p>Thanks for placing an order with [company_name]. We are currently waiting to receive your
                payment to complete your order.</p>
                <p><a href="http://[invoice_url]"
                target="_blank">http://[invoice_url]</a> Click here to view order</p>
                <p>User:  [first_name] [last_name]</p>
                <p>Amount: [amount]</p>
                <p>Review this order details. If you have any question or concerns,
                &nbsp;please do not hesitate to contact customer support.</p>
                <p><br></p><p><br></p><p><br></p>
                <p style="text-align: center;">[back_office_logo]</p>
                <p style="text-align: center;">This is an important notification from [company_name]</p>
                <p style="text-align: center;">[company_name] [company_address]</p>'
            ]);
        }
        $email = CustomEmail::where('title', 'sponsor_notice');
        if (isset($email) && $email) {
            $email->update([
                'subject' => 'Sponsor Notice',
                'display_name' => 'Sponsor Notice new Rep',
                'body' => "<p>[sponsor_first_name]</p>
                <p>Congratulations a new user has registered with [company_name] and you are their sponsor!</p>
                <p>New Rep's contact info</p>
                <p>Name: [first_name] [last_name]</p><p>Phone: [phone]</p><p>Email: [email]</p><p><br></p>
                <p><br></p><p><br></p><p><br></p>
                <p style='text-align: center;'>[back_office_logo]</p>
                <p style='text-align: center;'>This is an important notification from [company_name]</p>
                <p style='text-align: center;'>[company_name] [company_address]</p>"
            ]);
        }
        $email = CustomEmail::where('title', 'new_rep');
        if (isset($email) && $email) {
            $email->update([
                'subject' => 'New rep at [company_name]',
                'display_name' => 'New Rep Notice',
                'body' => '<p>[company_name]</p><p>You have a new user, [first_name] [last_name].</p><p><br></p>
                <p><br></p><p><br></p><p><br></p>
                <p style="text-align: center;">[back_office_logo]</p>
                <p style="text-align: center;">This is an important notification from [company_name]</p>
                <p style="text-align: center;">[company_name] [company_address]</p>'
            ]);
        }
        $email = CustomEmail::where('title', 'welcome_email');
        if (isset($email) && $email) {
            $email->update([
                'subject' => 'Welcome to [company_name]',
                'display_name' => 'Welcome Email',
                'body' => '<p>[first_name]</p>
                <p>Congratulations on registering with [company_name]! You can now log in with
                your email address and chosen password.
                <a href=" http://core-ds.cplocal/login" target="_blank">[company_name]</a></p>
                <p><br></p><p><br></p><p><br></p>
                <p style="text-align: center;">[back_office_logo]</p>
                <p style="text-align: center;">This is an important notification from [company_name]</p>
                <p style="text-align: center;">[company_name] [company_address]</p>'
            ]);
        }
        $email = CustomEmail::where('title', 'new_password');
        if (isset($email) && $email) {
            $email->update([
                'subject' => 'Reset Password',
                'display_name' => 'Password Reset',
                'body' => '<p>Dear, [first_name] [last_name]</p><p>You have reset your password.</p>
                <p><br></p><p><br></p><p><br></p><p style="text-align: center;">[back_office_logo]</p>
                <p style="text-align: center;">This is an important notification from [company_name]</p>
                <p style="text-align: center;">[company_name] [company_address]</p>'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_emails', function (Blueprint $table) {
            $table->dropColumn('subject');
            $table->dropColumn('body');
            $table->dropColumn('display_name');
        });
    }
}
