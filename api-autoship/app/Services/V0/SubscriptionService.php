<?php

namespace App\Services\V0;

use App\Mail\Reminder;
use App\Mail\Failure;
use App\Models\User;
use App\Models\Subscription;
use App\Services\Interfaces\V0\SubscriptionServiceInterface;
use App\Services\V0\MessagingService;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Mail;

class SubscriptionService implements SubscriptionServiceInterface
{
    public static function renewError($buyer, Subscription $subscription, ?RequestException $exception, ?String $message = null, ?int $responseCode = null)
    {
        if (is_null($exception) && (is_null($message) || is_null($responseCode))) {
            throw new Exception('invalid parameters: must have a message and response code when there is no RequestException');
        }
        if (is_null($message)) {
            $response = json_decode($exception->getResponse()->getBody());
            $message = isset($response->message) ? (string) $response->message : (string) json_encode($response);
        }
        if (is_null($responseCode)) {
            $responseCode = $exception->getCode();
        }
        self::createAttempt($subscription, $message, 'failure');
        MessagingService::sendFailureEmail($buyer, $subscription, null, $message);
        abort($responseCode, $message);
    }

    public static function calculateTotals(Subscription $subscription)
    {
        if (!isset($subscription->lines)) {
            $subscription->load('lines');
        }
        $subtotal = 0;
        foreach ($subscription->lines as $line) {
            $subtotal += $line->quantity * $line->price;
        }
        $subscription->subtotal = $subtotal;
        $subscription->discount = round(($subscription->percent_discount / 100 * $subscription->subtotal), 2);
    }

    public static function createAttempt(Subscription $subscription, String $description, String $status, ?String $orderPid = null) : void
    {
        $subscription->attempts()->create([
            'autoship_subscription_id'      => $subscription->id,
            'subscription_cycle'            => $subscription->cycle,
            'description'                   => $description,
            'status'                        => $status,
            'order_pid'                     => $orderPid,
        ]);
        if ($status === 'success' || $status === 'failure' && $subscription->cycle_attempts + 1 >= 3) {
            self::addDuration($subscription);
        }
    }

    public static function addDuration(Subscription $subscription) : Subscription
    {
        $duration = 'add'.$subscription->duration;
        $subscription->next_billing_at = Carbon::createFromFormat('Y-m-d H:i:s', $subscription->next_billing_at)->$duration($subscription->frequency)->toDateTimeString();
        $subscription->cycle++;
        $subscription->save();
        return $subscription;
    }
}
