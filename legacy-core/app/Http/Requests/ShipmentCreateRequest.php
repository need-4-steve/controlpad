<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentCreateRequest extends FormRequest
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
            'order_id' => 'sometimes|integer|nullable',
            'rate_id' => 'required|string',
            'payment.card_number' => 'required|digits_between:12,19',
            'payment.security' => 'required|digits_between:3,4',
            'payment.month' => 'required|string|digits_between:2,2',
            'payment.year' => 'required|string|digits_between:4,4',
            'payment.name' => 'required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/", // Allows letters, numbers, periods, apostrophes and hyphens.
            'payment.address_1' => 'required|string',
            'payment.address_2' => 'sometimes|string',
            'payment.city' => 'required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/",
            'payment.state' => 'required|alpha|between:2,2',
            'payment.zip' => 'required|regex:/^[0-9]{5}([- ]?[0-9]{4})?$/', // Allows 5 digit zip and a 9 digit zip with a dash.,
        ];
    }
}
