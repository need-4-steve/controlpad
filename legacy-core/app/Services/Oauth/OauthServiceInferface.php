<?php

namespace App\Services\Oauth;

interface OauthServiceInferface
{
    public function getToken($state);
    public function getUser();
}
