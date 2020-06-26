<?php

namespace App\Repositories\Contracts;

interface UserSettingsRepositoryContract
{
    public function update($request);
    public function show($user_id);
}
