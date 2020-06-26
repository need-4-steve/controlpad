<?php

namespace App\Repositories\Eloquent;

use App\Models\Address;

class GeocodeRepository
{
    /*
     * Geocode an address object,
     *
     * @author Joshua Burdick  2/3/17
     *
     * @param Address $address
     * @return AddressCollection $code
     */
    public function geocode(Address $address)
    {
        $code = app('geocoder')->geocode(
            $address->address_1 . ', '
            . $address->city . ', '
            . $address->state
        )
        ->get();
        return $code;
    }

    /*
     * Geocode an address object without injecting repository,
     * used as a trait method with GeocodeTrait
     *
     * @author Joshua Burdick  2/3/17
     *
     * @param Address $address
     * @return AddressCollection $code
     */
    public static function geocodeStatic(Address $address)
    {
        try {
            $code = app('geocoder')->geocode(
                $address->address_1 . ', '
                . $address->city . ', '
                . $address->state
            )
            ->get();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

        return $code;
    }

    /*
     * Geocode an address string
     *
     * @author Joshua Burdick
     * @date 2/3/17
     *
     * @param string $address
     * @return AddressCollection $code
     */
    public function geocodeString($address)
    {
        try {
            $code = app('geocoder')->geocode($address)->get();
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 422)->send();
        }
        return $code;
    }

    /*
     * Reverse geocode address coordinates
     *
     * @author Joshua Burdick
     * @date 2/3/17
     *
     * @param float $lat
     * @param float $long
     * @return AddressCollection $code
     */
    public function geocodeCoords($lat, $long)
    {
        $code = app('geocoder')->reverse($lat, $long)->get();
        return $code;
    }

    /*
     * Perform haversine straight line distance calculations
     *
     * @author Joshua Burdick
     * @date 2/3/17
     *
     * @param float $latitude
     * @param float $longitude
     * @param float $targetLatitude
     * @param float $targetLongitude
     * @return float $distance
     */
    public function haversine($latitude, $longitude, $targetLatitude, $targetLongitude)
    {
        $latitude = deg2rad($latitude);
        $longitude = deg2rad($longitude);
        $targetLatitude = deg2rad($targetLatitude);
        $targetLongitude = deg2rad($targetLongitude);
        $radiusOfEarth = 3961;// Earth's radius in miles.
        $diffLatitude = $targetLatitude - $latitude;
        $diffLongitude = $targetLongitude - $longitude;
        $a = sin($diffLatitude / 2) * sin($diffLatitude / 2) +
            cos($latitude) * cos($targetLatitude) *
            sin($diffLongitude / 2) * sin($diffLongitude / 2);
        $c = 2 * asin(sqrt($a));
        $distance = $radiusOfEarth * $c;
        return $distance;
    }

    /*
     * Create map markers for a collection of User with geolocation relationship
     *
     * @author Joshua Burdick
     * @date 2/3/17
     *
     * @param User $reps
     * @return array $markers
     */
    public function markerize($reps)
    {
        $markers = [];
        // make em into markers
        foreach ($reps as $rep) {
            $rep->url = sprintf(env('REP_URL'), $rep->public_id);
            $markers[] = [
                'id' => $rep->id,
                'name' => $rep->full_name,
                'position' => [
                    'lat' => $rep->address->geolocation->latitude,
                    'lng' => $rep->address->geolocation->longitude
                ],
                'public_id' => $rep->public_id,
                'url' => $rep->url,
                'icon' => [
                    // small person icon
                    // TODO: create settings that allow custom map icons
                    'url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/'
                                . 'cp_a3c65c2974270fd093ee8a9bf8ae7d0b/229f63f336b6a16fa496470423546c65.png'
                ]
            ];
        }
        return $markers;
    }

    /*
     * Create map markers for a collection of Order with shippingAddress geolocation relationship
     *
     * @author Joshua Burdick
     * @date 2/3/17
     *
     * @param User $reps
     * @return array $markers
     */
    public function markerizeOrders($orders)
    {
        $markers = [];
        // make em into markers
        foreach ($orders as $order) {
            if (isset($order->shippingAddress) && isset($order->shippingAddress->geolocation)) {
                $marker = [
                    'id' => $order->id,
                    'name' => $order->shippingAddress->name,
                    'total_price' => $order->total_price,
                    'subtotal_price' => $order->subtotal_price,
                    'total_tax' => $order->total_tax,
                    'total_shipping' => $order->total_shipping,
                    'receipt_id' => $order->receipt_id,
                    'position' => [
                        'lat' => $order->shippingAddress->geolocation->latitude,
                        'lng' => $order->shippingAddress->geolocation->longitude
                    ]
                ];
                $markers[] = $marker;
            }
        }
        return $markers;
    }

    /*
     * Add distance to a set location to a collection of User
     *
     * @author Joshua Burdick
     * @date 2/3/17
     *
     * @param User $reps
     * @param float $targetLat
     * @param float $targetLong
     * @param float $maxMiles
     * @return User $repsWithDistance
     */
    public function addDistance($reps, $targetLat, $targetLong, $maxMiles = 10)
    {
        $repsWithDistance = [];
        foreach ($reps as $rep) {
            // Filter out the correct address
            $addresses = $rep->addresses;
            unset($rep->addresses);
            $rep->address = $addresses->where('label', 'Business')->first();
            if (empty($rep->address)) {
                $rep->address = $addresses->where('label', 'Billing')->first();
            }
            if (empty($rep->address)) {
                $rep->address = $addresses->where('label', 'Shipping')->first();
            }
            // Run distance calculation
            $rep->distance = round(
                $this->haversine(
                    $targetLat,
                    $targetLong,
                    $rep->address->geolocation->latitude,
                    $rep->address->geolocation->longitude
                ),
                2
            );
            // distance okay, add to array
            if ($rep->distance <= $maxMiles) {
                $repsWithDistance[] = $rep;
            }
        }
        return $repsWithDistance;
    }

    /*
     * Create a map center array from latitude and longitude
     *
     * @author Joshua Burdick
     * @date 2/3/17
     *
     * @param float $userLatitude
     * @param float $userLongitude
     * @return array $center
     */
    public function setCenter($userLatitude, $userLongitude)
    {
        $center = [
            'lat' => $userLatitude,
            'lng' => $userLongitude,
            'icon' => [
                'url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/'
                        . 'cp_a3c65c2974270fd093ee8a9bf8ae7d0b/37b750946593535082ce0b68c40aa4df.png'
            ]
        ];
        return $center;
    }
}
