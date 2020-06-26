<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CustomOrderTaxRequest extends Request
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
        $rules = [
            'addresses.billing.name' => 'string',
            'addresses.billing.address_1' => 'required|string',
            'addresses.billing.address_2' => 'string',
            'addresses.billing.city' => 'required|string',
            'addresses.billing.state' => 'required|alpha|size:2',
            'addresses.billing.zip' => 'required|min:5',
            'addresses.shipping.name' => 'string',
            'addresses.shipping.address_1' => 'required|string',
            'addresses.shipping.address_2' => 'string',
            'addresses.shipping.city' => 'required|string',
            'addresses.shipping.state' => 'required|alpha|size:2',
            'addresses.shipping.zip' => 'required|min:5',
            'taxable_type'  => 'required|in:retail,wholesale'
        ];
        return $rules;
    }

    public function messages()
    {
        $messages = [
            'addresses.billing.name.string' => 'Address name needs to be a string.',
            'addresses.billing.address_1.required' => 'Address line 1 is required.',
            'addresses.billing.address_1.string' => 'Address line 1 needs to be a string.',
            'addresses.billing.address_2.string' => 'Address line 2 needs to be a string.',
            'addresses.billing.city.required' => 'Address city is required.',
            'addresses.billing.city.string' => 'Address city needs to be a string.',
            'addresses.billing.state.required' => 'Address state is required.',
            'addresses.billing.state.alpha' => 'Address state needs to only use letters.',
            'addresses.billing.state.size' => 'Address state can only be 2 letters long.',
            'addresses.billing.zip.required' => 'Address zip is required.',
            'addresses.billing.zip.min' => 'Address zip needs to be at least 5 numbers long.',
            'addresses.shipping.name.string' => 'Shipping address name needs to be a string.',
            'addresses.shipping.address_1.required' => 'Shipping address line 1 is required.',
            'addresses.shipping.address_1.string' => 'Shipping address line 1 needs to be a string.',
            'addresses.shipping.address_2.string' => 'Shipping address line 2 needs to be a string.',
            'addresses.shipping.city.required' => 'Shipping address city is required.',
            'addresses.shipping.city.string' => 'Shipping address city needs to be a string.',
            'addresses.shipping.state.required' => 'Shipping address state is required.',
            'addresses.shipping.state.alpha' => 'Shipping address state needs to only use letters.',
            'addresses.shipping.state.size' => 'Shipping address state can only be 2 letters long.',
            'addresses.shipping.zip.required' => 'Shipping zip is required.',
            'addresses.shipping.zip.min' => 'Shipping zip needs to be at least 5 numbers long.',
            'addresses.shipping.name.string' => 'Shipping address name needs to be a string.',
        ];
        return $messages;
    }
}
