<?php

namespace App\Repositories\Eloquent;

use App\Models\Address;
use App\Repositories\Contracts\AddressRepositoryContract;

class AddressRepository implements AddressRepositoryContract
{
    public function create(array $inputs = [])
    {
        $fields = [
                    'name',
                    'address_1',
                    'address_2',
                    'city',
                    'state',
                    'addressable_id',
                    'zip',
                    'addressable_type',
                    'label'
                ];

        $address = Address::firstOrCreate(
            [
                'addressable_type' => $inputs['addressable_type'],
                'addressable_id' => $inputs['addressable_id'],
                'label' => $inputs['label']
            ]
        );

        foreach ($fields as $field) {
            $address->$field = array_get($inputs, $field);
        }
        $address->save();
        return $address;
    }

    public function update(Address $address, array $inputs = [])
    {
        $fields = [
                    'name',
                    'address_1',
                    'address_2',
                    'city',
                    'state',
                    'addressable_id',
                    'zip',
                    'addressable_type',
                    'label'
                ];

        foreach ($fields as $field) {
            $address->$field = $inputs[$field];
        }

        $address->update();
        return $address;
    }

    public function findByAddressable($addressableId, $label, $addressableType = 'App\Models\User')
    {
        return $address = Address::where('addressable_id', $addressableId)
                        ->where('addressable_type', $addressableType)
                        ->where('label', $label)
                        ->first();
    }

    public function show($id)
    {
         return Address::findOrFail($id);
    }

    public function businessAddress($user_id)
    {
        return Address::where('addressable_id', $user_id)->where('addressable_type', 'App\Models\User')->where('label', 'business')->first();
    }
}
