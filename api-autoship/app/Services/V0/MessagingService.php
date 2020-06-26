<?php

namespace App\Services\V0;

use App\Mail\Reminder;
use App\Mail\Failure;
use App\Services\Interfaces\V0\MessagingServiceInterface;
use App\Services\Interfaces\V0\SettingsServiceInterface;
use Exception;
use Illuminate\Support\Facades\Mail;

class MessagingService
{
    public static function sendReminderEmail($buyer, $subscription, ?String $orgId = null)
    {
        try {
            $SettingsService = app()->make(SettingsServiceInterface::class);
            $settings = $SettingsService->getSettings($orgId);
            $email = new Reminder($buyer, $subscription, $settings);
            Mail::send($email);
            return $email;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function sendFailureEmail($buyer, $subscription, ?String $orgId = null, ?String $message = null)
    {
        try {
            $SettingsService = app()->make(SettingsServiceInterface::class);
            $settings = $SettingsService->getSettings($orgId);
            $email = new Failure($buyer, $subscription, $settings, $message);
            Mail::send($email);
            return $email;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
