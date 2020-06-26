<?php

namespace App\Services;

interface SettingsServiceInterface
{
    public function getSettings($keyList);
    public function getCompanyPid();
}
