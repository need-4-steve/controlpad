<?php namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\RepLocatorNearbyRepRequest;
use App\Repositories\Eloquent\GeocodeRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Http\Controllers\Controller;
use App\Models\RepLocator;
use App\Models\Address;
use App\Models\User;
use App\Models\Item;
use GuzzleHttp\Client;
use Response;
use DB;

class RepLocatorController extends Controller
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
        GeocodeRepository $geoRepo,
        ProductRepository $productRepo
    ) {
        $this->geoRepo = $geoRepo;
        $this->productRepo = $productRepo;
    }

    /*
     * Search for reps within radius of zip code
     *
     * @author Joshua Burdick
     * @date 2/3/17
     *
     * @param integer $zip
     * @param float $radius
     * @return AddressCollection $code
     */
    public function search($zip, $radius = 50)
    {
        $radius = 500;
        // $apiKey = '38wfLVsf5xOW5xJMjSlSI93K0KqkLGmPPS4BNwQS3EP8cyUvqfo8prTpckKY16NR';
        $apiKey = env('ZIPCODE_API_KEY');

        $reps = [];
        $attemptedZipCodes = [];
        $url = 'https://www.zipcodeapi.com/rest/' . $apiKey . '/radius.json/'
                    . $zip . '/' . $radius . '/mile';
        $client = new Client(['timeout'  => 10.0, ]);
        $data = $client->get($url);
        $zipCodes = json_decode($data->getBody(), true);
        $zipCodes = $zipCodes['zip_codes'];

        // make an array of zips
        $zips = array_column($zipCodes, 'zip_code');

        // filter out zip codes that have already been tried
        $zips = array_diff($zips, $attemptedZipCodes);

        // sort the remaining
        $zipCodes = array_sort($zipCodes, function ($value) {
            return $value['distance'];
        });

        // store attempted codes
        $attemptedZipCodes = array_merge($attemptedZipCodes, $zips);

        $indexedCodes = [];
        foreach ($zipCodes as $code) {
            $indexedCodes[$code['zip_code']] = $code;
        }

        // grab addresses with appropriate relationships
        $addresses = Address::with('addressable', 'geoLocation')
                        ->has('geoLocation')
                        ->where('addressable_type', 'App\Models\User')
                        ->where('label', 'Business')
                        ->whereIn('zip', $zips)
                        ->get();
        // grab rep ids
        $repIds =[];
        foreach ($addresses as $address) {
            $repIds[] = $address->addressable_id;
        }

        // pull fresh instance of reps
        $reps = User::whereIn('id', $repIds)
                    ->with(
                        'addresses.geoLocation',
                        'phone',
                        'shipRates'
                    )->get();

        return $reps;
    }

    /*
     * Pull rep inventory (this is for the mobile app)
     *
     * @author Joshua Burdick
     * @date 2/3/17
     *
     * @param integer $id
     * @return User $users
     */
    public function repInventory($id)
    {
        if ($id == config('site.apex_user_id')) {
            return response()->json([$this->messages['Unauthorized']], 403);
        }

        return User::with('inventories.item.product', 'inventories.price')->find($id);
    }

    /*
     * Pull reps near a location based on a request object containing address
     *
     * @author Joshua Burdick
     * @date 2/3/17
     *
     * @return User $reps
     * @return array $center
     * @return array $markers
     */
    public function nearbyReps(RepLocatorNearbyRepRequest $request)
    {
        if (!empty($request['repName'])) {
            $reps = User::where(
                DB::raw('concat(users.first_name, " ", users.last_name)'),
                'LIKE',
                '%' .$request['repName'] . '%'
            )
            ->where('role_id', '!=', 3)
            ->with(
                'addresses.geolocation',
                'phone',
                'shipRates'
            )->get();
            $newOrder = collect();
            foreach ($reps as $rep) {
                if ($rep->state === $request['state']) {
                    $newOrder = $newOrder->prepend($rep);
                } else {
                    $newOrder = $newOrder->push($rep);
                }
            }
            $reps = (object)$newOrder;

            if (!empty($reps) && $reps->count() > 0) {
                if (!empty($reps->first()->businessAddress)) {
                    $latitude = $reps->first()->businessAddress->geoLocation->latitude;
                    $longitude = $reps->first()->businessAddress->geoLocation->longitude;
                } elseif (!empty($reps->first()->billingAddress)) {
                    $latitude = $reps->first()->billingAddress->geoLocation->latitude;
                    $longitude = $reps->first()->billingAddress->geoLocation->longitude;
                } else {
                    $latitude = $reps->first()->shippingAddress->geoLocation->latitude;
                    $longitude = $reps->first()->shippingAddress->geoLocation->longitude;
                }
                $center = null;
                $reps = $this->geoRepo->addDistance($reps, $latitude, $longitude, 9000);
                $markers = $this->geoRepo->markerize($reps);
                $center = $this->geoRepo->setCenter(
                    $markers[0]['position']['lat'] + .01,
                    $markers[0]['position']['lng'] - .01
                );
                unset($center['icon']);
                $center['visible'] = false;
                return response()->json(['reps' => $reps, 'center' => $center, 'markers' => $markers]);
            }
            return response()->json(
                'No reps found by that name.',
                400
            );
        }

        $request = request()->all();
        // geocode searched location
        $code = $this->geoRepo->geocodeString(
            $request['address'] . ', '
            . $request['city'] . ', '
            . $request['state'] . ', '
            . $request['zip']
        )->first();
        $userLatitude = $code->getLatitude();
        $userLongitude = $code->getLongitude();
        // set our map's center
        $center = $this->geoRepo->setCenter($userLatitude, $userLongitude);
        $reps = $this->search($request['zip']);
        // add distances to reps
        $reps = $this->geoRepo->addDistance(
            $reps,
            $userLatitude,
            $userLongitude,
            $request['miles']
        );
        // create markers from rep locations
        $markers = $this->geoRepo->markerize($reps);
        return response()->json(['reps' => $reps, 'center' => $center, 'markers' => $markers]);
    }

    /*
     * Reverse geocode a latitude/longitude pair to zip code
     *
     * @author Joshua Burdick
     * @date 2/3/17
     *
     * @param float $latitude
     * @param float $longitude
     * @return integer $zip
     */
    public function geocodeCoords($latitude, $longitude)
    {
        $zip = null;
        $reversed = $this->geoRepo->geocodeCoords($latitude, $longitude);
        foreach ($reversed as $rev) {
            if (!empty($code = $rev->getPostalCode())) {
                $zip = $code;
            }
        }
        return $zip;
    }

    /*
     * Get a list of products, for use with typeahead
     *
     * @author Joshua Burdick
     * @date 2/3/17
     *
     * @return Item $items
     */
    public function productList()
    {
        return $this->productRepo->all(request()->all());
    }

    /*
     * Search for reps that have a product by location
     *
     * @author Joshua Burdick
     * @date 2/3/17
     *
     * @param integer $id
     * @return User $reps
     * @return array $center
     * @return array $markers
     */
    public function searchProduct($id)
    {
        $request = request()->all();

        // attempt to geocode provided address
        if (is_array($request['state'])) {
            $request['state'] = '';
        }
        $code = $this->geoRepo->geocodeString(
            $request['address'] . ', '
            . $request['city'] . ', '
            . $request['state'] . ', '
            . $request['zip']
        )->first();
        $userLatitude = $code->getLatitude();
        $userLongitude = $code->getLongitude();

        $reps = $this->search($request['zip'], $request['miles']);
        // throw error if empty
        if (empty($reps)) {
            return response()->json(
                'No reps found within ' . $request['miles']
                . ' miles with that product in stock.',
                400
            );
        }
        $reps->load('inventories.price', 'addresses.geolocation', 'inventories.item');
        $itemIds = Item::where('product_id', $id)->select('id')->pluck('id')->toArray();
        $repsWithCorrectInventory = [];
        $markers = [];
        foreach ($reps as $rep) {
            $repInventory = $rep->inventories;
            $filteredInventory = $repInventory->filter(function ($inventory) use ($itemIds) {
                if (in_array($inventory->item_id, $itemIds)) {
                    return true;
                };
                return false;
            });
            if (!empty($filteredInventory) && count($filteredInventory) > 0) {
                // If we need additional inventory for a feature, remove these lines
                unset($rep->inventories);
                $rep->inventories = $filteredInventory;
                // Filter out the correct address
                $addresses = $rep->addresses;
                unset($rep->addresses);
                $rep->address = $addresses->where('label', 'Business')->first();
                $repsWithCorrectInventory[] = $rep;
            }
        }
        // add distances to reps
        $repsWithCorrectInventory = $this->geoRepo->addDistance(
            $repsWithCorrectInventory,
            $userLatitude,
            $userLongitude,
            $request['miles']
        );
        // throw error if empty
        if (empty($repsWithCorrectInventory)) {
            return response()->json(
                'No reps found within ' . $request['miles']
                . ' miles with that product in stock.',
                400
            );
        }
        // set the map center
        $center = $this->geoRepo->setCenter($userLatitude, $userLongitude);
        // sort by distance
        usort($repsWithCorrectInventory, function ($a, $b) {
            return $a->distance > $b->distance;
        });
        // slice 'er up
        if (!empty($request['nearest']) && is_numeric($request['nearest'])) {
            $numberOfReps = $request['nearest'] > 10 ? 10 : $request['nearest'];
            $repsWithCorrectInventory = array_slice($repsWithCorrectInventory, 0, (int) $numberOfReps);
        }

        $markers = $this->geoRepo->markerize($repsWithCorrectInventory);

        return response()->json([
            'reps' => $repsWithCorrectInventory,
            'markers' => $markers,
            'center' => $center
        ]);
    }
}
