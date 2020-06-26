<?php

use App\Email;
use Illuminate\Database\Seeder;

class EmailsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        Email::truncate();
        DB::beginTransaction();
        Email::create([
            'title' => 'new_password',
            'standard' => true,
            'subject' => 'Reset Password',
            'display_name' => 'Password Reset',
            'body' => '<p>Dear, [first_name] [last_name]</p><p>You have reset your password.</p>
            <p><br></p><p><br></p><p><br></p><p style="text-align: center;">[back_office_logo]</p>
            <p style="text-align: center;">This is an important notification from [company_name]</p>
            <p style="text-align: center;">[company_name] [company_address]</p>',
            'org_id' => '1k64c8w0av3o848g5jabe95d8'
        ]);
        Email::create([
            'title' => 'welcome_email',
            'standard' => true,
            'subject' => 'Welcome to [company_name]',
            'display_name' => 'Welcome Email',
            'body' => '<p>[first_name]</p>
            <p>Congratulations on registering with [company_name]! You can now log in with
            your email address and chosen password.
            <a href=" http://core-ds.cplocal/login" target="_blank">[company_name]</a></p>
            <p><br></p><p><br></p><p><br></p>
            <p style="text-align: center;">[back_office_logo]</p>
            <p style="text-align: center;">This is an important notification from [company_name]</p>
            <p style="text-align: center;">[company_name] [company_address]</p>',
            'org_id' => '1k64c8w0av3o848g5jabe95d8'
        ]);
        Email::create([
            'title' => 'new_rep',
            'standard' => true,
            'subject' => 'New rep at [company_name]',
            'display_name' => 'New Rep Notice',
            'body' => '<p>[company_name]</p><p>You have a new user, [first_name] [last_name].</p><p><br></p>
            <p><br></p><p><br></p><p><br></p>
            <p style="text-align: center;">[back_office_logo]</p>
            <p style="text-align: center;">This is an important notification from [company_name]</p>
            <p style="text-align: center;">[company_name] [company_address]</p>',
            'org_id' => '1k64c8w0av3o848g5jabe95d8'
        ]);
        Email::create([
            'title' => 'sponsor_notice',
            'standard' => true,
            'subject' => 'Sponsor Notice',
            'display_name' => 'Sponsor Notice new Rep',
            'body' => "<p>[sponsor_first_name]</p>
            <p>Congratulations a new user has registered with [company_name] and you are their sponsor!</p>
            <p>New Rep's contact info</p>
            <p>Name: [first_name] [last_name]</p><p>Phone: [phone]</p><p>Email: [email]</p><p><br></p>
            <p><br></p><p><br></p><p><br></p>
            <p style='text-align: center;'>[back_office_logo]</p>
            <p style='text-align: center;'>This is an important notification from [company_name]</p>
            <p style='text-align: center;'>[company_name] [company_address]</p>",
            'org_id' => '1k64c8w0av3o848g5jabe95d8'
        ]);
        Email::create([
            'title' => 'invoice',
            'standard' => true,
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
            <p style="text-align: center;">[company_name] [company_address]</p>',
            'org_id' => '1k64c8w0av3o848g5jabe95d8'
        ]);
        Email::create([
            'title' => 'fulfilled',
            'standard' => true,
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
            <p style="text-align: center;">[company_name] [company_address]</p>',
            'org_id' => '1k64c8w0av3o848g5jabe95d8'
        ]);
        Email::create([
            'title' => 'new_order_received',
            'standard' => true,
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
            <p style="text-align: center;">[company_name] [company_address]</p>',
            'org_id' => '1k64c8w0av3o848g5jabe95d8'
        ]);
        Email::create([
            'title' => 'order_receipt',
            'standard' => true,
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
            <p style='text-align: center;'>[company_name] [company_address]</p>",
            'org_id' => '1k64c8w0av3o848g5jabe95d8'
        ]);
        Email::create([
            'title' => 'card_update',
            'standard' => true,
            'subject' => 'Subscription Expired',
            'display_name' => 'Subscription Failed',
            'body' => '<p>Dear [first_name] [last_name],</p>
            <p>Your credit card on file needs to be updated.</p>
            <p>If this is not done soon you will be locked out of the system.</p>
            <p><br></p><p><br></p><p><br></p><p style="text-align: center;">[back_office_logo]</p>
            <p style="text-align: center;">This is an important notification from [company_name]</p>
            <p style="text-align: center;">[company_name] [company_address]</p>',
            'org_id' => '1k64c8w0av3o848g5jabe95d8'
        ]);
        Email::create([
            'title' => 'missing_card',
            'standard' => true,
            'subject' => 'No Card on File',
            'display_name' => 'Missing Card',
            'body' => '<p>Dear [first_name] [last_name],</p>
            <p>You do not have a credit card on file for your subscription</p>
            <p><br></p><p><br></p><p><br></p><p style="text-align: center;">[back_office_logo]</p>
            <p style="text-align: center;">This is an important notification from [company_name]</p>
            <p style="text-align: center;">[company_name] [company_address]</p>',
            'org_id' => '1k64c8w0av3o848g5jabe95d8'
        ]);
        Email::create([
            'title' => 'renew_fail',
            'standard' => true,
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
            <p style='text-align: center;'>[company_name] [company_address]</p>",
            'org_id' => '1k64c8w0av3o848g5jabe95d8'
        ]);
        Email::create([
            'title' => 'renew_success',
            'standard' => true,
            'subject' => 'Subscription Renewed Successfully',
            'display_name' => 'Subscription Renewed Successfully',
            'body' => "<p>Dear [first_name] [last_name],</p>
            <p>We've sent this email to inform you that your auto-renewal of your&nbsp;
            subscription has succeeded.</p><p><br></p>
            <p><br></p><p><br></p><p><br></p>
            <p style='text-align: center;'>[back_office_logo]</p>
            <p style='text-align: center;'>This is an important notification from [company_name]</p>
            <p style='text-align: center;'>[company_name] [company_address]</p>",
            'org_id' => '1k64c8w0av3o848g5jabe95d8'
        ]);
        Email::create([
            'title' => 'expire_notice',
            'standard' => true,
            'subject' => 'Subscription Renewed Notice',
            'display_name' => 'Subscription Renewed Notice',
            'body' => '<p>Dear [first_name] [last_name],</p>
            <p>Your subscription will be renewed on [billing_date]</p><p><br></p>
            <p><br></p><p><br></p><p><br></p>
            <p style="text-align: center;">[back_office_logo]</p>
            <p style="text-align: center;">This is an important notification from [company_name]</p>
            <p style="text-align: center;">[company_name] [company_address]</p>',
            'org_id' => '1k64c8w0av3o848g5jabe95d8'
        ]);
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
            <p style="text-align: center;">[company_name] [company_address]</p>',
            'org_id' => '1k64c8w0av3o848g5jabe95d8'
        ]);
        DB::commit();
    }
}
