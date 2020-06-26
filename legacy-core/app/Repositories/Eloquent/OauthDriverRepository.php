<?php

namespace App\Repositories\Eloquent;

use App\Models\OauthDriver;
use App\Repositories\Contracts\OauthDriverRepositoryContract;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;

class OauthDriverRepository implements OauthDriverRepositoryContract
{
    use CommonCrudTrait;

    /**
     * Create a new instance of OauthDriver
     *
     * @param  array $inputs
     * @return boolean|OauthDriver
     */
    public function create(array $inputs = [])
    {
        $oauthDriver = new OauthDriver;

        $fields = [
            'name',
            'keyname',
        ];
        foreach ($fields as $field) {
            $oauthDriver[$field] = array_get($inputs, $field, '');
        }

        $oauthDriver->save();

        return $oauthDriver;
    }

    /**
     * Update an OauthDriver
     *
     * @param  OauthDriver $oauthDriver
     * @param  array $inputs
     * @return boolean|OauthDriver
     */
    public function update(OauthDriver $oauthDriver, array $inputs = [])
    {
        $fields = [
            'name',
            'keyname',
        ];
        foreach ($fields as $field) {
            $oauthDriver[$field] = array_get($inputs, $field, '');
        }

        $oauthDriver->update();

        return $oauthDriver;
    }
}
