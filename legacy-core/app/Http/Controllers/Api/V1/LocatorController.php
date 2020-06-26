<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\Locator\RepLocatorService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LocatorRequest;
use GuzzleHttp\Client;
use Response;
use DB;

class LocatorController extends Controller
{
    /*
     * Constructor
     *
     * @author Joshua Burdick
     * @date 2/3/17
     *
     * @param GeocodeRepository $geoRepo
     * @param ProductRepository $productRepo
     */
    public function __construct(
        RepLocatorService $repLocatorService
    ) {

        $this->repLocatorService = $repLocatorService;
        $this->settingsService = app('globalSettings');
    }



    /*
     * Search for reps within radius of zip code
     *
     * @author Dane Hixson
     * @date 09/11/2017
     *
     * @param integer $zip
     * @param integer $radius
     * @return array of users
     */
    public function searchRep(LocatorRequest $request)
    {
        $enabled = $this->settingsService->getGlobal('rep_locator_enable', 'value');
        if ($enabled) {
            //search user by name
            if (isset($request->name)) {
                $users = $this->repLocatorService->searchUsersByName($request->name);
            //search user by zip
            } elseif (isset($request->zip)) {
                $radius = $this->settingsService->getGlobal('rep_locator_radius', 'value');
                $users = $this->repLocatorService->searchUsersByZipCode(substr($request->zip, 0, 5), $radius);
            }
            //gather random users as last option
            if (!isset($users) || count($users) == 0) {
                $random_results = $this->repLocatorService->getRandomUsers();
            }
            return json_encode([
                'distance_results' => isset($users) ? $users : [],
                'random_results' => isset($random_results) ? $random_results : []
            ]);
        } else {
            return response()->json(['Feature disabled.'], 403);
        }
    }
}
