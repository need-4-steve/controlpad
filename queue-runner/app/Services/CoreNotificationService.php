<?php

namespace App\Services;

use CPCommon\Jwt\Jwt;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class CoreNotificationService implements NotificationServiceInterface
{
    const EVENT_PATH_MAP = [
        'order-fulfilled' => '/api/v2/notification-event/order-fulfilled',
        'coupon-created' => '/api/v2/notification-event/coupon-created',
    ];

    public function sendNotifications($event)
    {
        if (array_key_exists($event->event, CoreNotificationService::EVENT_PATH_MAP)) {
            $client = app('utils')->getClientInfo($event->orgId);
            if ($client === null) {
                app('log')->error('Client was null for event', ['event' => $event]);
                return;
            }
            // Create core url based on environment and domain config
            $protocol = env('APP_ENV') == 'production' ? 'https://' : 'http://';
            if (strpos($client->domain, 'controlpad.com')) {
                $domain = $client->domain;
            } else {
                $domain = 'myoffice.' . $client->domain;
            }
            $url = $protocol . $domain . CoreNotificationService::EVENT_PATH_MAP[$event->event];
            $this->sendNotification($event, $url);
        }
    }

    private function sendNotification($event, $url)
    {
        try {
            $client = new Client;
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => app('utils')->getJWTAuthHeader($event->orgId)
            ];
            $body = json_encode($event);

            $response = $client->post(
                $url,
                [
                    'body' => $body,
                    'headers' => $headers
                ]
            );
            $body = json_decode($response->getBody());
            return $body;
        } catch (RequestException $re) {
            app('utils')->logGuzzleException($re);
            // TODO how should we handle failures?
            // For now we are just dropping them
        }
    }
}
