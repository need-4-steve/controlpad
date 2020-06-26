<?php

namespace App\Repositories\Eloquent;

use App\Models\UserSetting;
use App\Models\CardToken;
use App\Repositories\Contracts\UserSettingsRepositoryContract;
use App\Repositories\Eloquent\SubscriptionRepository;

class UserSettingsRepository implements UserSettingsRepositoryContract
{
    public function __construct(SubscriptionRepository $subscriptionRepo)
    {
        $this->subscriptionRepo = $subscriptionRepo;
    }
    /**
     * Update user settings.
     *
     * @return App\Models\User;
     */
    public function update($request)
    {
        if (!is_array($request)) {
            $request = $request->toArray();
        }
        if (auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            $request['user_id'] =  config('site.apex_user_id');
        }
        $setting = UserSetting::firstOrCreate(['user_id' => $request['user_id']]);
        $setting->update($request);
        return UserSetting::find($setting->id);
    }

    /**
     * Show a user's settings.
     *
     * @return App\Models\User;
     */
    public function show($user_id)
    {
        return UserSetting::where('user_id', $user_id)->first();
    }

    public function newUser($userId, $userPid, $timezone = 'UTC')
    {
        if (request()->has('timezone')) {
            $timezone = request()->input('timezone');
        }
        $setting = UserSetting::create([
            'user_id' => $userId,
            'user_pid' => $userPid,
            'timezone' => $timezone,
        ]);
        return $setting;
    }

    /**
    * Add card token.
    *
    *
    */
    public function newOrUpdateCardToken($token, $user_id, $type)
    {
        $cardToken = cardToken::where('user_id', $user_id)->where('type', $type)->first();
        if (!$cardToken) {
            return $this->subscriptionRepo->createSubscriptionCardToken($token['data'], $user_id);
        } else {
            $cardNumber = "************" . substr($token['data']['cardNumber'], -4);
            $cardToken->token = $token['data']['cardToken'];
            $cardToken->card_digits = $cardNumber;
            $cardToken->card_type = $token['data']['cardType'];
            $cardToken->expiration = $token['data']['cardExpiration'];
            $cardToken->gateway_customer_id = $token['data']['gatewayCustomerId'];
            $cardToken->update();
            return $cardToken;
        }
    }

    public function showCardInfo($user_id, $type)
    {
        return CardToken::where('user_id', $user_id)->where('type', $type)->first();
    }

    public function deleteCardInfo($id, $user_id)
    {
        $cardInfo = CardToken::find($id);
        if ($cardInfo['user_id'] === $user_id) {
            CardToken::destroy($id);
            return true;
        } else {
            return false;
        }
    }

    public function getUserTimeZone($userId)
    {
        $timeZone = UserSetting::where('user_id', $userId)->first();
        if ($timeZone == null) {
            $timeZone = 'UTC';
        } else {
            $timeZone = $timeZone->timezone;
        }
        return $timeZone;
    }
}
