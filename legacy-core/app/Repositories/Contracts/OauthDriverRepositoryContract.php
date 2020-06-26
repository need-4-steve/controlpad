<?php

namespace App\Repositories\Contracts;

use App\Models\OauthDriver;

interface OauthDriverRepositoryContract
{
    public function create(array $inputs = []);
    public function update(OauthDriver $oauthDriver, array $inputs = []);
}
