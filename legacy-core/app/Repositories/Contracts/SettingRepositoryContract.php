<?php

namespace App\Repositories\Contracts;

use App\Models\Setting;

interface SettingRepositoryContract
{
    public function create(array $inputs = []);
    public function update($input);
    public function show($user_id);
    public function createEmail($input);
    public function updateEmail($id, $input);
    public function showEmail($user_id);
    public function showId($id);
}
