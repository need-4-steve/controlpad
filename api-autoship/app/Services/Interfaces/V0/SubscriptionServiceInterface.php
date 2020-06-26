<?php

namespace App\Services\Interfaces\V0;

use App\Models\Subscription;
use GuzzleHttp\Exception\RequestException;

interface SubscriptionServiceInterface
{
    public static function renewError($buyer, Subscription $subscription, ?RequestException $exception, ?String $message = null, ?int $responseCode = null);
    public static function calculateTotals(Subscription $subscription);
    public static function createAttempt(Subscription $subscription, String $description, String $status, ?String $orderPid = null);
    public static function addDuration(Subscription $subscription);
}
