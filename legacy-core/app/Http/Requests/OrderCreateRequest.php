<?php

namespace App\Http\Requests;

class OrderCreateRequest extends ApiRequest
{
    public function __construct()
    {
        $this->rules = [
            'payment.name' => ['required_unless:payment.type,cash', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"], // Allows letters, numbers, periods, apostrophes and hyphens.
            'payment.card_number' =>  'required_unless:payment.type,cash|digits_between:12,19',
            'payment.security' => 'required_unless:payment.type,cash|digits_between:3,4',
            'payment.month' => 'required_unless:payment.type,cash|digits:2',
            'payment.year' => 'required_unless:payment.type,cash|digits:4',
            'user.first_name' => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'user.last_name' => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'user.email' => 'required|email',
            'addresses.shipping.address_1' => 'required',
            'addresses.shipping.address_2' => 'sometimes',
            'addresses.shipping.state' => 'required|alpha|between:2,2',
            'addresses.shipping.city' => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'addresses.shipping.zip' => 'required|regex:/^[0-9]{5}([- ]?[0-9]{4})?$/', // Allows 5 digit zip and a 9 digit zip with a dash.
            'addresses.billing.address_1' => 'required',
            'addresses.billing.address_2' => 'sometimes',
            'addresses.billing.state' => 'required|alpha|between:2,2',
            'addresses.billing.city' => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'addresses.billing.zip' => 'required|regex:/^[0-9]{5}([- ]?[0-9]{4})?$/',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->rules;
    }
}
