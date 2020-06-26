<?php

namespace App\Services\Interfaces\V0;

interface SettingsServiceInterface
{
    public function getSettings($orgId = null);
}
