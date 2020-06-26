<?php

namespace App\Services\V0;

use App\Services\Interfaces\V0\SettingsServiceInterface;
use DB;
use Cache;
use Carbon\Carbon;

class SettingsService implements SettingsServiceInterface
{
    public function getSettings($orgId = null)
    {
        if (!is_null($orgId)) {
            return Cache::remember('settings-'.$orgId, Carbon::now()->addHour(), function () {
                return $this->getFromDatabase();
            });
        }
        return $this->getFromDatabase();
    }

    private function getFromDatabase()
    {
        $settings = DB::connection('tenant')->table('settings')->select([
            'key',
            // Value is returned like ""Controlpad"" if not done this way.
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(value, '$.value')) as 'value'"),
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(value, '$.show')) as 'show'"),
        ])->where(function ($query) {
            $query->whereIn('key', ['from_email', 'back_office_logo', 'company_name', 'address', 'autoship_display_name']);
        })->get()->keyBy('key');
        return json_decode(json_encode($settings));
    }
}
