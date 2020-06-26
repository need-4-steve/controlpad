<?php

namespace App\Services;

use CPCommon\Jwt\Jwt;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;

class WebhookService implements WebhookServiceInterface
{
    private $webhookApiUrl;

    public function __construct(Request $request)
    {
        $this->webhookApiUrl = env('WEBHOOK_API_URL', 'https://webhooks.controlpadapi.com/api/v0');
    }

    public function getWebhook($id, $orgId)
    {
        $client = new Client;
        try {
            $response = $client->get(
                $this->webhookApiUrl . '/webhooks/' . $id,
                [
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader($orgId)
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $re) {
            app('utils')->logGuzzleException($re);
            abort(500);
        }
    }

    public function findWebhooks($event, $orgId, $active = true, $suspended = null)
    {
        $client = new Client;
        try {
            $response = $client->get(
                $this->webhookApiUrl . '/webhooks',
                [
                    'query' => [
                        'page' => 1,
                        'count' => 50, // a single event should never have more than 10
                        'event' => $event,
                        'active' => $active,
                        'suspended' => $suspended
                    ],
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader($orgId)
                    ]
                ]
            );
            $body = json_decode($response->getBody());
            return $body;
        } catch (RequestException $re) {
            app('utils')->logGuzzleException($re);
            abort(500);
        }
    }

    public function sendHooks($event)
    {
        $webhooks = $this->findWebhooks($event->event, $event->orgId)->data;
        foreach ($webhooks as $webhook) {
            $this->sendHook($event, $webhook);
        }
    }

    private function sendHook($event, $webhook)
    {
        try {
            $client = new Client;
            $headers = [
                'Content-Type' => 'application/json'
            ];
            $body = json_encode($event);
            $this->generateAuthHeader($headers, $body, $webhook);

            $response = $client->post(
                $webhook->url,
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

    private function generateAuthHeader(&$headers, $body, $webhook)
    {
        switch ($webhook->config->auth->type) {
            case 'none':
                // Do nothing
                break;
            case 'sha256':
                $headers['Payload-Signature'] = base64_encode(hash_hmac('sha256', $body, $webhook->config->auth->secret, true));
                break;
            default:
                app('log')->error('Unexpected webhook auth type', ['webhook' => $webhook]);
                break;
        }
    }
}
