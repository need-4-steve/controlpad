<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\V0\SubscriptionRepository;
use App\Services\Interfaces\V0\UserServiceInterface;
use App\Services\V0\MessagingService;
use App\Services\V0\SubscriptionService;
use Illuminate\Http\Request;

class MessagingController extends Controller
{
    public function __construct()
    {
        $this->SubscriptionRepo = new SubscriptionRepository();
    }

    public function sendReminder(Request $request, $pid)
    {
        $UserService = app()->make(UserServiceInterface::class);
        $UserService = app()->make(UserServiceInterface::class);

        $subscription = $this->SubscriptionRepo->find([
            'expands' => ['lines', 'cycle_attempts']
        ], $pid);
        SubscriptionService::calculateTotals($subscription);
        $buyer = $UserService->getUser($subscription->buyer_pid, $subscription);
        return MessagingService::sendReminderEmail($buyer, $subscription, $request->user->orgId);
    }

    public function sendFailure(Request $request, $pid)
    {
        $UserService = app()->make(UserServiceInterface::class);
        $subscription = $this->SubscriptionRepo->find([
            'expands' => ['lines', 'cycle_attempts']
        ], $pid);
        SubscriptionService::calculateTotals($subscription);
        $buyer = $UserService->getUser($subscription->buyer_pid, $subscription);
        return MessagingService::sendFailureEmail($buyer, $subscription, $request->user->orgId, $request->input('message'));
    }
}
