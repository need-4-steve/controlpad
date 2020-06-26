<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Repositories\Eloquent\UserRepository;

class RegisterRequest extends ApiRequest
{

    public function __construct()
    {
        // TODO maybe add payment validation later, removed it so the old structure still allowed
        $this->rules = [
            'user.first_name' => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'user.last_name' => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'user.email' => 'required|email',
            'user.password' => 'required|min:8',
            'user.public_id' => 'required|alpha_dash|min:3|unique:users,public_id',
            'addresses.shipping.address_1' => 'required_with:starter_kit_id',
            'addresses.shipping.address_2' => 'sometimes',
            'addresses.shipping.state' => 'required_with:addresses.shipping|alpha|between:2,2',
            'addresses.shipping.city' => ['required_with:addresses.shipping', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'addresses.shipping.zip' => 'required_with:addresses.shipping|regex:/^[0-9]{5}([- ]?[0-9]{4})?$/', // Allows 5 digit zip and a 9 digit zip with a dash.
            'addresses.billing.address_1' => 'required_unless:payment.amount,0.00',
            'addresses.billing.address_2' => 'sometimes',
            'addresses.billing.state' => 'required_with:addresses.billing|alpha|between:2,2',
            'addresses.billing.city' => ['required_with:addresses.billing', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'addresses.billing.zip' => 'required_with:addresses.billing|regex:/^[0-9]{5}([- ]?[0-9]{4})?$/',
            'subscription_id' => 'required|int',
            'starter_kit_id' => 'sometimes|int',
            'total_tax' => 'required|numeric|min:0',
            'tax_invoice_pid' => 'required|nullable',
        ];
    }
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
        return $this->rules;
    }
}
