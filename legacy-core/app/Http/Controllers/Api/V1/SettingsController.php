<?php namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\SettingRepository;
use App\Models\CustomEmail;

class SettingsController extends Controller
{
    public $settingRepo;

    public function __construct(SettingRepository $settingRepo)
    {
        $this->settingRepo = $settingRepo;
    }

    /**
     * This method gets all of the settings and returns them as an object.
     *
     */
    public function index(Request $request)
    {
        if ($request->has('category')) {
            $categories = cache()->rememberForever('globalSettingsCategories', function () {
                return $this->settingRepo->indexOrganizedByCategory();
            });
            return response()->json($categories);
        }
        $settings = cache()->rememberForever('globalSettings', function () {
            return $this->settingRepo->index();
        });
        return response()->json($settings);
    }

    /**
     * This method updates all of the settings at one time.
     *
     *@param array[] $input is array of array of all the settings in the
     * database.
     */
    public function update()
    {
        $inputs = request()->all();
        $setting = $this->settingRepo->update($inputs);
        cache()->forget('global-settings');
        cache()->forget('globalSettings');
        cache()->forget('globalSettingsCategories');
        return response()->json(['success'], HTTP_SUCCESS);
    }

    /**
     * This method returns all settings for a chosen user.
     *
     */
    public function show($user_id)
    {
        return response()->json($this->settingRepo->show($user_id), HTTP_SUCCESS);
    }

    public function emailIndex()
    {
        return response()->json($this->settingRepo->indexEmail());
    }

    public function getEmailBySlug($slug)
    {
        $email = CustomEmail::where('title', $slug)->first();
        return response()->json($email);
    }

    public function createEmail()
    {
        $input = request()->all();
        $email = $this->settingRepo->createEmail($input);
        return response()->json($email);
    }

    public function updateEmail($id)
    {
        $input = request()->all();
        $email = $this->settingRepo->updateEmail($id, $input);
        return response()->json($email);
    }

    public function showEmail($user_id)
    {
        $show = $this->settingRepo->showEmail($user_id);
        return response()->json($show);
    }

    public function showId($id)
    {
        $key = $this->settingRepo->showId($id);
        return response()->json($key);
    }

    /*
     * Get all of the current settings that affect all reps
     */
    public function showRepSettings()
    {
        $settings = $this->settingRepo->showRepSettings();
        return response()->json($settings, HTTP_SUCCESS);
    }

    public function showInventorySettings()
    {
        return response()->json($this->settingRepo->showInventorySettings());
    }

    /*
     * Get all of the current settings that affect user registration
     */
    public function showRegistrationSettings()
    {
        $settings = $this->settingRepo->showRegistrationSettings();
        return response()->json($settings, HTTP_SUCCESS);
    }

    /*
     * Get settings related to login
     */
    public function showLoginSettings()
    {
        $settings = $this->settingRepo->showLoginSettings();
        return response()->json($settings, HTTP_SUCCESS);
    }

    /*
     * Get settings related to taxes
     */
    public function showTaxSettings()
    {
        $settings = $this->settingRepo->showTaxSettings();
        return response()->json($settings, HTTP_SUCCESS);
    }

    /*
     * Get settings related to store category
     */
    public function showGeneralStoreSettings()
    {
        $settings = $this->settingRepo->showGeneralStoreSettings();
        return response()->json($settings, HTTP_SUCCESS);
    }

    /*
     * Get settings related to shipping
     */
    public function showShippingSettings()
    {
        $settings = $this->settingRepo->showShippingSettings();
        return response()->json($settings, HTTP_SUCCESS);
    }

    /*
     * Get settings by category
     */
    public function showSettingsCategory($category)
    {
        $settings = $this->settingRepo->showSettingsCategory($category);
        return response()->json($settings, HTTP_SUCCESS);
    }

    public function showBlacklisted()
    {
        $blacklisted = $this->settingRepo->showBlcklisted();
        return response()->json($blacklisted, HTTP_SUCCESS);
    }

    public function updateBlacklist()
    {
        $list = request()->all();
        $updateList = $this->settingRepo->updateBlacklist($list);
        return response()->json($updateList, HTTP_SUCCESS);
    }
    /*
     * Get settings related to events category
     */
    public function showEventSettings()
    {
        $settings = $this->settingRepo->showEventSettings();
        return response()->json($settings, HTTP_SUCCESS);
    }
}
