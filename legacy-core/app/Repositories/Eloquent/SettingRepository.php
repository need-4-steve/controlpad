<?php

namespace App\Repositories\Eloquent;

use App\Models\SettingEmail;
use App\Models\Setting;
use App\Models\Blacklist;
use App\Repositories\Contracts\SettingRepositoryContract;
use App\Services\Oauth\FacebookOauthService;
use Carbon\Carbon;

class SettingRepository implements SettingRepositoryContract
{
    public function __construct(
        FacebookOauthService $facebookOauthService
    ) {
        $this->facebookOauthService = $facebookOauthService;
    }

    /**
     * Create a new instances of Set
     *
     * @param array $inputs
     * @return bool|Set
     */
    public function show($user_id)
    {
        if (! $user_id) {
            $user_id = 1;
        }

        $settings = Setting::where('user_id', $user_id)->orderBy('category')->get();
        return $this->transformSettings($settings);
    }

    /**
     * Show all of a rep's settings
     *
     * @param array $inputs
     * @return bool|Set
     */
    public function showRepSettings()
    {
        $settings = Setting::where('category', 'rep')->get();
        return $this->transformSettings($settings);
    }

    public function showInventorySettings()
    {
        return $this->transformSettings(Setting::where('category', 'inventory')->get());
    }

    /**
     * Show all of a rep's settings
     *
     * @param array $inputs
     * @return bool|Set
     */
    public function showRegistrationSettings()
    {
        $settings = Setting::where('category', 'registration')->get();
        return $this->transformSettings($settings);
    }

    /**
     * Show all login settings
     *
     * @return bool|Set
     */
    public function showLoginSettings()
    {
        $settings = Setting::where('key', 'LIKE', '%_login')->get();
        return $this->transformSettings($settings);
    }

    /**
     * Show all login settings
     *
     * @return bool|Set
     */
    public function showTaxSettings()
    {
        $settings = Setting::where('category', 'taxes')->get();
        return $this->transformSettings($settings);
    }

    /**
     * Show shipping settings
     *
     * @return bool|Set
     */
    public function showShippingSettings()
    {
        $settings = Setting::where('category', 'shipping')->get();
        return $this->transformSettings($settings);
    }

    /**
     * Show store category settings
     *
     * @return bool|Set
     */
    public function showGeneralStoreSettings()
    {
        $settings = Setting::where('category', 'store')->get();
        return $this->transformSettings($settings);
    }

    /**
     * Show settings by category
     *
     * @return bool|Set
     */
    public function showSettingsCategory($category)
    {
        $settings = Setting::where('category', $category)->get();
        return $this->transformSettings($settings);
    }

    /**
     * Show events category settings
     *
     * @return bool|Set
     */
    public function showEventSettings()
    {
        $settings = Setting::where('category', 'events')->get();
        return $this->transformSettings($settings);
    }

    /**
     * Transform settings to expected front end format
     *
     * @param array $inputs
     * @return bool|Set
     */
    public function transformSettings($settings)
    {
        $transformedSettings = [];
        foreach ($settings as $setting) {
            $setting->value = json_decode($setting->value);
            $transformedSettings[$setting->key] = $setting->value;
        }
        return $transformedSettings;
    }

    public function indexOrganizedByCategory()
    {
        $settings = Setting::select('value', 'key', 'category')->orderBy('category')->get();
        $categories = [];
        foreach ($settings as $setting) {
            $categories[$setting->category][$setting->key] = json_decode($setting->value);
        }
        return $categories;
    }

    public function index()
    {
        $settings = Setting::select('value', 'key')->orderBy('category')->get();
        foreach ($settings as $setting) {
            $setting->value = json_decode($setting->value);
        }
        $userSetting = [];
        foreach ($settings as $setting) {
            if (isset($setting['value'])) {
                if (isset($setting['value']->show)) {
                    $setting['show'] = $setting['value']->show;
                } else {
                    $setting['show'] = 1;
                }

                if (isset($setting['value']->value)) {
                    $setting['value'] = $setting['value']->value;
                }
            }
            $userSetting[$setting['key']] =  $setting;
            unset($setting['key']);
        }

        // this is dynamically generated for now, since it depends on values in the env
        // down the road, we should consider finding a way of generating this per client
        // and storing it in the database
        $facebookOauthURL = $this->facebookOauthService->generateLoginUrl();
        $userSetting['facebook_oauth_url'] = [
            'value' => $facebookOauthURL,
            'show' => true
        ];
        $userSetting['google_tracking_id'] = [
            'value' => env('GOOGLE_TRACKING_ID'),
            'show' => true
        ];
        $userSetting['rep_url'] = [
            'value' => env('REP_URL'),
            'show' => true
        ];

        return $userSetting;
    }

    public function create(array $inputs = [])
    {
        $set = new Set;
        $fields = [];

        foreach ($fields as $field) {
            $set->$field = array_get($inputs, $field, '');
        }

        if ($set->save()) {
            return $set;
        }
        return false;
    }

    /**
     * Update an instances of Set
     *
     * @param Set $set
     * @param array $inputs
     * @return bool|Set
     */
    public function update($inputs)
    {
        $settings = [];
        foreach ($inputs as $key => $input) {
            $setting = Setting::where('key', $key)->first();
            $input['value'] = json_encode($input);
            unset($input['show']);
            try {
                $setting->update($input);
            } catch (\Throwable $e) {
                // prevent failure with settings that are not in the database
            }
            $settings [] =  $input;
        }
        return $settings;
    }

    public function indexEmail()
    {
        return SettingEmail::get();
    }

    public function createEmail($input)
    {
        $email = new SettingEmail;
        $fields = [
            'user_id',
            'key',
            'value'
        ];
        foreach ($fields as $field) {
            $email->$field = array_get($input, $field, '');
        }
        if ($email->save()) {
            return $email;
        }
        return false;
    }
    public function updateEmail($id, $input)
    {
        $email = SettingEmail::find($id);
        $email->update($input);
        return $email;
    }
    public function showEmail($user_id)
    {
        $email = SettingEmail::where('user_id', $user_id)->get();
        return $email;
    }
    public function showId($id)
    {
        $show = SettingEmail::where('id', $id)->first();
        return $show;
    }
    public function showBlcklisted()
    {
        $blacklisted = Blacklist::where('user_created', 1)->get();
        $name = [];
        foreach ($blacklisted as $listed) {
            $name[] = $listed->name ;
        }
        $names = implode(",", $name);
        return $names;
    }
    public function updateBlacklist($list)
    {
        $names = array_map('trim', explode(',', $list[0]));
        $blacklisted = Blacklist::where('user_created', 1)->get();
        $blackName = [];
        foreach ($blacklisted as $listed) {
            $blackName[] = $listed->name ;
        }
        if (count($names) > count($blackName)) {
            $newlist = collect($names)->diff($blackName);
            foreach ($newlist as $name) {
                if (collect($blackName)->contains($name) === false) {
                    Blacklist::insert([
                        'name' => $name,
                        'category' => 'rep',
                        'user_created' => 1,
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now()->toDateTimeString(),
                    ]);
                }
            }
            return true;
        } else {
            $newlist = collect($blackName)->diff($names);
            foreach ($newlist as $name) {
                Blacklist::where('name', $name)->delete();
            }
            return true;
        }

        foreach ($newlist as $name) {
            Blacklist::insert([
                'name' => $name,
                'category' => 'rep',
                'user_created' => 1,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ]);
        }
        return true;
    }
}
