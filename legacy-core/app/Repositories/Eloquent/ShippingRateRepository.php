<?php

namespace App\Repositories\Eloquent;

use App\Models\ShippingRate;
use App\Repositories\Contracts\ShippingRateRepositoryContract;

class ShippingRateRepository implements ShippingRateRepositoryContract
{
    /**
     * Index instances of ShippingRateRepository
     *
     * @param array $inputs
     * @return bool|ShippingRateRepository
     */
    public function index($user)
    {
        return ShippingRate::where('user_id', $user->id)->where('type', 'retail')->get();
    }

    public function indexWholesale($user)
    {
        return ShippingRate::where('user_id', $user->id)->where('type', 'wholesale')->get();
    }

    public function sortRatesArray($array)
    {
        // Obtain a list of columns
        foreach ($array as $key => $row) {
            $max[$key] = $row['max'];
        }
        array_multisort($max, SORT_ASC, $array);
        return $array;
    }
    /**
     * deletes all current shipping rates by user
     *
     * @param integer $user_id
     * @return void
     */
    public function deleteCurrentRates($user_id, $type)
    {
        $shippingRates = ShippingRate::where(['user_id' => $user_id, 'type' => $type])->get();
        if ($shippingRates !== null) {
            foreach ($shippingRates as $shippingRate) {
                $shippingRate->delete();
            }
        }
    }
    /**
     * checks for a duplicate rate in an array of rates
     *
     * @param array $rate
     * @param aray $rates
     * @return boolean
     */
    private function checkForDuplicateRate($rate_to_check, $rates)
    {
        $duplicate = false;
        foreach ($rates as $rate) {
            if ($rate['max'] == $rate_to_check['max']) {
                $duplicate = true;
            }
        }
        return $duplicate;
    }
    /**
     * Create a new set of shipping rates.
     *
     * @param array $inputs
     * @param User $user
     * @return bool|ShippingRate
     */
    public function create($inputs, $user)
    {
        $this->deleteCurrentRates($user->id, $inputs['ranges'][0]['type']);
        $type = $inputs['ranges'][0]['type'];
        //sort rates array
        $rates = $this->sortRatesArray($inputs['ranges']);
        // create all new shipping rates
        $min = 0.0;
        $last_amount = 0;
        $created_rates =[];
        foreach ($rates as $key => $rate) {
            $duplicate = $this->checkForDuplicateRate($rate, $created_rates);
            // create if not a duplicate
            if ($rate['max'] !== null && $duplicate === false) {
                $shippingRate = ShippingRate::create([
                    'user_id' => $user->id,
                    'user_pid' => $user->pid,
                    'amount' => $rate['amount'],
                    'min' => $min,
                    'max' => $rate['max'],
                    'type' => $rate['type'],
                    'name' => $rate['name']
                ]);
                $created_rates[] = $rate;
                $min = $rate['max'] + 0.01;
                $last_amount = $rate['amount'];
            }
        }

        $shippingRate = ShippingRate::create([
            'user_id' => $user->id,
            'user_pid' => $user->pid,
            'amount' => $last_amount,
            'min' => $min,
            'max' => null,
            'type' => $type,
            'name' => 'Max Default Rate'
        ]);
        return $shippingRate;
    }

    public function findPriceForUser($id, $totalPrice, $type = null)
    {
        if (!isset($type)) {
            $type = 'retail';
            if (auth()->check() && auth()->user()->hasRole(['Rep']) && $id === config('site.apex_user_id')) {
                $type = 'wholesale';
            }
        }

        $shippingCost = ShippingRate::where('user_id', $id)
            ->where('type', $type)
            ->where('min', '<=', $totalPrice)
            ->where('max', '>=', $totalPrice)
            ->first();
        if ($shippingCost === null) {
            $shippingCost = ShippingRate::where('user_id', $id)
            ->where('type', $type)
            ->where('max', null)->first();
        }
        return $shippingCost;
    }
}
