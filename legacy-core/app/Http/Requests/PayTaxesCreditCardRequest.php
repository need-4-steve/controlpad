<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayTaxesCreditCardRequest extends FormRequest
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
            'payment.name' => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"], // Allows letters, numbers, periods, apostrophes and hyphens.
            'payment.card_number' =>  'required|digits_between:12,19',
            'payment.security' => 'required|digits_between:3,4',
            'payment.month' => 'required|digits:2',
            'payment.year' => 'required|digits:4',
            'addresses.billing.address_1' => 'required',
            'addresses.billing.address_2' => 'sometimes',
            'addresses.billing.state' => 'required|alpha|between:2,2',
            'addresses.billing.city' => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'addresses.billing.zip' => 'required|regex:/^[0-9]{5}([- ]?[0-9]{4})?$/',
            'total' => 'required|min:0.01'
        ];
    }
}
