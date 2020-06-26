<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotifyOrderFulfilledRequest;
use App\Mailers\AutoshipSubCancelMailer;
use App\Mailers\AutoshipSubCreatedMailer;
use App\Mailers\OrderFulfilledMailer;
use App\Mailers\WelcomeCustomerMailer;

class NotificationEventController extends Controller
{

    public function orderFulfilledNotification(NotifyOrderFulfilledRequest $request)
    {
        // NotifyOrderFulfilledRequest validates inputs and filters wrong events
        // Force laravel to provide the mailer only when used, not at the controller level
        app()
        ->make(OrderFulfilledMailer::class)
        ->sendNotification($request->all()['data']['order']);

        return response()->json(null, 204);
    }

    public function couponCreatedNotification()
    {
        // TODO build email for coupon created for customer
    }

    public function autoshipSubCreatedNotification()
    {
        $this->validate(request(), [
            'event' => 'required|in:autoship-sub-created',
            'data.subscription' => 'required',
            'data.subscription.seller.email' => 'required|email',
            'data.subscription.buyer.email' => 'required|email'
        ]);
        app()->make(AutoshipSubCreatedMailer::class)
            ->sendNotification(request()->all()['data']['subscription']);
        return response()->json(null, 204);
    }

    public function autoshipSubCancelNotification()
    {
        $this->validate(request(), [
            'event' => 'required|in:autoship-sub-cancelled',
            'data.subscription' => 'required',
            'data.subscription.seller.email' => 'required|email',
            'data.subscription.buyer.email' => 'required|email'
        ]);
        app()->make(AutoshipSubCancelMailer::class)->sendNotification(request()->all()['data']['subscription']);
        return response()->json(null, 204);
    }

    public function newUserNotification()
    {
        $this->validate(request(), [
            'event' => 'required|in:user-created',
            'data.user' => 'required',
            'data.user.email' => 'required|email',
        ]);
        $user = request()->all()['data']['user'];
        if ($user['role'] === 'Customer') {
            app()->make(WelcomeCustomerMailer::class)->sendNotification($user);
        }
        // TODO support existing new rep flow
        return response()->json(null, 204);
    }
}
