<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class SubscriptionCardRequest extends Request
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
        $request = request()->all();

        $rules = [
            'payment.name' => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'payment.card_number' => 'required|integer',
            'payment.month' => 'required|numeric',
            'payment.year' => 'required|numeric',
            'payment.code' => 'required|numeric',
            'addresses.billing.address_1' => 'required|string',
            'addresses.billing.address_2' => 'sometimes|string|nullable',
            'addresses.billing.city' => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'addresses.billing.state' => 'required|alpha|between:2,2',
            'addresses.billing.zip' => 'required|regex:/^[0-9]{5}([- ]?[0-9]{4})?$/'
        ];

        return $rules;
    }
}
