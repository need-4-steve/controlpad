<?php namespace App\Http\Controllers\Api\V1;

use Auth;
use Response;
use Mail;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\EmailRepository;
use App\Services\Text\TextService;
use App\Services\Email\EmailService;
use App\Models\EmailMessage;

class EmailMessageController extends Controller
{
    public function __construct(EmailRepository $emailRepo, TextService $textService, EmailService $emailService)
    {
        $this->emailRepo = $emailRepo;
        $this->textService = $textService;
        $this->emailService = $emailService;
        $this->globalSettings = app('globalSettings');
    }
    public function getIndex()
    {
        if (Auth::user()->hasRole(['Superadmin', 'Admin'])) {
            return EmailMessage::with('sender')->get();
        }
        return EmailMessage::where('sender_id', Auth::id())->get();
    }

    public function customEmailIndex()
    {
        $emails = $this->emailRepo->customEmailIndex();
        return $emails;
    }

    public function updatecustomEmailIndex($title)
    {
        $request = request()->all();
        $emails = $this->emailRepo->update($title, $request);
        return $emails;
    }

    public function emailLogs()
    {
        $request = request()->all();
        $emailLogs = $this->emailRepo->emailLogs($request);
        return $emailLogs;
    }

    public function removeOldlogs()
    {
        return $this->emailRepo->removeEmailLogs();
    }

    public function showEmail($title)
    {
        $request = request()->all();
        if ($request !== []) {
            $email = $request['body'];
        } else {
            $email = $this->emailRepo->emailShow($title)->body;
        }

        $variables = $this->emailService->emailVar($title);
        $request = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'sponsor_first_name' => 'Jane',
            'sponsor_last_name' => 'Doe',
            'email' => 'JohnDoe@mail.com',
            'phone' => '555-444-1234',
            'amount' => '19.95',
            'order' => ['lines'=>[
                ['name'=>'Item Name Here',
                'price' => 50,
                'quantity'=> 2],
                ['name'=>'Item Name Here',
                'price' => 25,
                'quantity'=> 1]
                ]],
            'customer_first_name' => 'Bob',
            'customer_last_name' => 'Lee',
            'customer_email' => 'BobLee@mail.com',
            'orderlines' => [
                ['name'=>'Product Name Here',
                'variant' => 'Variant Name Here',
                'option' => 'Option Here',
                'price' => 50,
                'quantity'=> 2],
                ['name'=>'Product Name Here',
                'variant' => 'Variant Name Here',
                'option' => 'Option Here',
                'price' => 25,
                'quantity'=> 1]
            ],
            'order_subtotal' => '125.00',
            'order_tax' => '6.13',
            'order_discount' => '0.00',
            'order_shipping' => '5.00',
            'order_total' => '136.13',
            'order_receipt_id' => 'SPKPX9-39494',
            'tracking_number' => '123456789',
            'tracking_url' => 'https://tools.usps.com/go/TrackConfirmAction_input',
            'tracking_link' => '<a href="https://tools.usps.com/go/TrackConfirmAction_input">123456789</a>',
            'billing_date' => '12/05/2018',
            'reason' => 'test',
            'invoice_url' =>  config('app.url').'/orders/invoice/1517329741zS2GAPk6tQvfZirTXJG5',
            'company_name' => $this->globalSettings->getGlobal('company_name', 'value'),
            'back_office_logo' => $this->globalSettings->getGlobal('back_office_logo', 'value'),
            'coupon_code' => '9wndz5vj76ybf2c4jocw5r0yx'
        ];
        if ($title === 'invoice') {
            $request['note'] = 'Thank you for your order.';
        }
        $content = $this->textService->parseText($request, $variables, $email);
        return ['content' => $content,'var' => $variables];
    }
}
