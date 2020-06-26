<?php

namespace App\Repositories\Eloquent;

use App\Models\OauthToken;
use App\Models\OauthDriver;
use App\Models\User;
use App\Repositories\Contracts\OauthTokenRepositoryContract;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;

use Carbon\Carbon;

class OauthTokenRepository implements OauthTokenRepositoryContract
{
    use CommonCrudTrait;

    /**
     * Create a new instance of OauthToken
     *
     * @param  array $inputs
     * @return boolean|OauthToken
     */
    public function create(array $inputs = [])
    {
        $data = [];
        $fields = [
            'user_id',
            'driver_id',
            'access_token',
            'email',
            'service_email',
            'service_user_id',
            'refresh_token',
            'expires_at',
            'issued_at',
        ];

        foreach ($fields as $field) {
            $data[$field] = array_get($inputs, $field, '');
        }

        $oauthToken = OauthToken::create($data);
        return $oauthToken;
    }

    /**
     * Update an OauthToken
     *
     * @param  OauthToken $oauthToken
     * @param  array $inputs
     * @return boolean|OauthToken
     */
    public function update(OauthToken $oauthToken, array $inputs = [])
    {
        $fields = [
            'user_id',
            'driver_id',
            'access_token',
            'email',
            'service_email',
            'service_user_id',
            'refresh_token',
            'expires_at',
            'issued_at',
        ];
        foreach ($fields as $field) {
            $oauthToken[$field] = array_get($inputs, $field, '');
        }

        $oauthToken->update();

        return $oauthToken;
    }

    /**
     * Find a token for a service by an email address.
     *
     * @method findByEmail
     * @param  string      $email
     * @param  string      $driver facebook, instagram, etc.
     * @return bool|\App\Models\OauthToken
     */
    public function findByEmail(string $email, string $driver)
    {
        return OauthToken::whereDriver($driver)->where('email', $email)->first();
    }

    /**
     * Find a token for a service by a service_email address.
     *
     * @method findByEmail
     * @param  string      $email
     * @param  string      $driver facebook, instagram, etc.
     * @return bool|\App\Models\OauthToken
     */
    public function findByServiceEmail(string $email, string $driver)
    {
        return OauthToken::whereDriver($driver)->where('service_email', $email)->first();
    }

    /**
     * Use updateOrCreate because of the way we handle tokens and how
     * often they can change. Putting it here to centralize and
     * standardize it's usage.
     *
     * @method updateOrCreate
     * @param  array          $conditions
     * @param  array          $inputs
     * @return bool|\App\Models\OauthToken
     */
    public function updateOrCreate(array $conditions, array $inputs)
    {
        $driverId = $conditions['driver_id'];
        if (! is_integer($conditions['driver_id'])) {
            $driver = OauthDriver::where('keyname', $conditions['driver_id'])->first();
            $driverId = $driver->id;
        }

        try {
            $token = OauthToken::updateOrCreate([
                'user_id'         => $conditions['user_id'],
                'driver_id'       => $driverId,
                'email'           => $conditions['email'],
            ], [
                'user_id'         => $conditions['user_id'],
                'email'           => $conditions['email'],
                'service_email'   => $inputs['service_email'],
                'service_user_id' => $inputs['service_user_id'],
                'access_token'    => $inputs['access_token'],
                'refresh_token'   => $inputs['refresh_token'],
                'issued_at'       => $inputs['issued_at'],
                'expires_at'      => $inputs['expires_at'],
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $token;
    }
}
