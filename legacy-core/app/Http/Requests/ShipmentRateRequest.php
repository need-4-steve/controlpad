<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentRateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'address_from.object_purpose' => 'in:PURCHASE',
            'address_from.name' => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'address_from.street1' => 'required|string',
            'address_from.street2' => 'sometimes',
            'address_from.city' => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'address_from.state' => 'required|alpha|between:2,2',
            'address_from.zip' => 'required|regex:/^[0-9]{5}([- ]?[0-9]{4})?$/',
            'address_from.country' => 'required|string',
            'address_from.phone' => 'required|string',
            'address_from.email' => 'required|email',
            'address_to.object_purpose' => 'in:PURCHASE',
            'address_to.name' => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'address_to.street1' => 'required|string',
            'address_to.street2' => 'sometimes',
            'address_to.city' => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'address_to.state' => 'required|alpha|between:2,2',
            'address_to.zip' => 'required|regex:/^[0-9]{5}([- ]?[0-9]{4})?$/',
            'address_to.country' => 'required|string',
            'address_to.phone' => 'sometimes|string',
            'address_to.email' => 'required|email',
            'parcel.length'=> 'required|numeric',
            'parcel.width'=> 'required|numeric',
            'parcel.height'=> 'required|numeric',
            'parcel.distance_unit'=> 'required|alpha',
            'parcel.weight'=> 'required|numeric',
            'parcel.mass_unit'=> 'required|alpha',
        ];
    }
}
