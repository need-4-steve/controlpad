<?php

namespace App\Services\Zoom;

use CPCommon\Jwt\Jwt;
use App\Services\Settings\SettingsService;
use GuzzleHttp;
use Mail;
use Swift_TransportException;
use Exception;

class ZoomService
{
    const BASE_ZOOM_URL = 'https://api.zoom.us/v2/';

    public function createZoomUser($user)
    {
        try {
            $client = new GuzzleHttp\Client();

            $body = json_encode([
                'action' => 'create',
                'user_info' => [
                    'email' => $user->email,
                    'type' => 2,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name
                ]
            ]);
            $response = $client->post(
                ZoomService::BASE_ZOOM_URL . 'users',
                [
                    'body' => $body,
                    'headers' => $this->getHeaders()
                ]
            );
            if ($response->getStatusCode() === 201) {
                if (isset($user->woocom_customer_id)) {
                    $wooComId = $user->woocom_customer_id;
                } else {
                    $wooComId = null;
                }
                $zoomUser = json_decode($response->getBody());
                \App\Models\ZoomUser::updateOrCreate(
                    ['user_id' => $user->id],
                    ['email' => $user->email, 'zoom_user_id' => $zoomUser->id, 'woocom_customer_id' => $wooComId]
                );
                // Update settings to have webinar and large meetings by default
                $this->updateUserSettings($zoomUser->id);
            } else {
                // Docs says errors can come back on a 200
                app('log')->error('Zoom response error', ['response' => $response, 'user' => $body]);
            }
        } catch (\Exception $e) {
            app('log')->error($e, ['user' => $body]);
            $this->sendErrorEmail($user, $e->getMessage());
        }
    }

    private function updateUserSettings($zoomUserId)
    {
        try {
            $client = new GuzzleHttp\Client();

            $body = json_encode([
                'feature' => [
                    'webinar' => true,
                    'large_meeting' => true
                ]
            ]);

            $response = $client->patch(
                ZoomService::BASE_ZOOM_URL . 'users/' . $zoomUserId . '/settings',
                [
                    'body' => $body,
                    'headers' => $this->getHeaders()
                ]
            );
        } catch (\Exception $e) {
            app('log')->error($e, ['zoomUserId' => $zoomUserId]);
        }
    }

    public function deleteZoomUser($user)
    {
        try {
            $zoomUser = \App\Models\ZoomUser::where('user_id', $user->id)->first();
            if (!isset($zoomUser->zoom_user_id)) {
                app('log')->error('Tried to delete zoom user without a record', ['user' => $user]);
                return;
            }

            $client = new GuzzleHttp\Client();

            $response = $client->delete(
                ZoomService::BASE_ZOOM_URL . 'users/' . $zoomUser->zoom_user_id,
                [
                    'headers' => $this->getHeaders()
                ]
            );
            if ($response->getStatusCode() === 204) {
                // Success, for now lets do nothing
                // By default zoom should disassociate the user from an account, this might mean a resub will acuire the same zoom_user_id anyway
            } else {
                // Docs says errors can come back on a 200
                app('log')->error('Zoom response error', ['response' => $response, 'user' => $user]);
            }
        } catch (\Exception $e) {
            app('log')->error($e, ['user' => $user]);
        }
    }

    private function getHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->createJwt()
        ];
    }

    private function createJwt()
    {
        // Fake issue a token with a 1000 second buffer, 16-33 minute window range in case system time is off
        // This causes the JWT generated to be the same for multiple requests
        $exp = round(time() + 1500, -3);
        $claims = [
            'aud' => null,
            'iss' => env('ZOOM_API_KEY'),
            'exp' => $exp,
            'iat' => ($exp - 2000)
        ];
        return Jwt::sign($claims, env('ZOOM_API_SECRET'));
    }

    private function sendErrorEmail($user, $message)
    {
        $emailAddress = env('ZOOM_ERROR_EMAIL');
        $settings = app('globalSettings');
        $fromEmail = $settings->getGlobal('from_email', 'value');
        $fromName = env('MAIL_FROM_NAME', $settings->getGlobal('company_name', 'value'));

        if (!empty($emailAddress)) {
            try {
                $data = [
                    'body' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' .
                        '<html xmlns="http://www.w3.org/1999/xhtml">' .
                        "<h2>Failed to create Zoom user during controlpad signup.</h2>" .
                        "<p>First Name: " . $user->first_name . "</p>" .
                        "<p>Last Name: " . $user->last_name . "</p>" .
                        "<p>Email: " . $user->email . "</p>" .
                        "<p>Error: " . $message . "</p></html>"
                ];
                Mail::send('emails.text', $data, function ($message) use ($emailAddress, $fromEmail, $fromName) {
                    $message->from($fromEmail, $fromName);
                    $message->to($emailAddress)->subject("Zoom API error");
                    $message->cc('john@myzoomlive.com');
                });
            } catch (Swift_TransportException $e) {
                app('log')->error($e);
            } catch (Exception $e) {
                app('log')->error($e);
            }
        } else {
            app('log')->error('Zoom error with no error email set');
        }
    }
}
