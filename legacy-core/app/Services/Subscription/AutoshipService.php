<?php

namespace App\Services\Subscription;

use DB;
use Carbon\Carbon;
use App\Mailers\AutoshipSubReminderMailer;
use App\Models\AutoshipSub;

class AutoshipService
{
    public function sendPaymentReminders()
    {
        try {
            $remindOn = app('globalSettings')->getGlobal('autoship_reminder', 'show');
            if (!$remindOn) {
                return;
            }
            $remindDays = app('globalSettings')->getGlobal('autoship_reminder', 'value');
            // Look for next_billing_at to be '$remindDays' in the future
            $minDate = Carbon::now()->addDays($remindDays)->startOfDay()->toDateTimeString();
            $maxDate = Carbon::now()->addDays($remindDays)->endOfDay()->toDateTimeString();
            // Only process up to existing records(max id). Prevents processing future records.
            $maxId = DB::table('autoship_subscriptions')->max('id');
            if (empty($maxId)) {
                return;
            }
            $lastId = 0; // Helps paginate orders to balance db calls and memory loads
            $mailer = app()->make(AutoshipSubReminderMailer::class);
            do {
                $upcomingSubs = AutoshipSub::with('lines')
                    ->where('id', '>', $lastId)
                    ->where('next_billing_at', '>=', $minDate)
                    ->where('next_billing_at', '<', $maxDate)
                    ->whereNull('disabled_at')->whereNull('deleted_at')
                    ->limit(25)
                    ->get();
                foreach ($upcomingSubs as $key => $subscription) {
                    $lastId = $subscription->id;
                    $mailer->sendNotification(json_decode(json_encode($subscription), 1));
                }
            } while ($upcomingSubs !== null && $upcomingSubs->isNotEmpty() && $lastId < $maxId);
        } catch (\Exception $e) {
            app('log')->error($e);
        }
    }
}
