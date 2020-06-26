<?php

namespace App\Repositories\Contracts;

use App\Models\OauthToken;

interface OauthTokenRepositoryContract
{
    public function create(array $inputs = []);
    public function update(OauthToken $oauthToken, array $inputs = []);
}
