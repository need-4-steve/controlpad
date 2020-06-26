<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CustomOrderPayRequest extends Request
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
            'order_type' => 'required',
        ];

        if (isset($request['order_type']) && $request['order_type'] ==='credit_card') {
            $rules += [
                'payment.name' => 'required',
                'payment.card_number' => 'required|numeric',
                'payment.security' => 'required|alpha_num',
                'payment.month' => 'required',
                'payment.year' => 'required'
            ];
        } elseif (isset($request['order_type']) && $request['order_type'] ==='invoice') {
            $rules = [
                'user.email' => 'required|email',
            ];
        } elseif (isset($request['order_type']) && $request['order_type'] === 'cash') {
              $rules = [
                  'cashType' => 'required'
              ];
        } else {
            $rules += [
                'user.first_name' => 'required|string',
                'user.last_name' => 'required|string',
                'user.email' => 'required|email',
                'addresses.shipping.name' => 'string',
                'addresses.shipping.address_1' => 'string',
                'addresses.shipping.address_2' => 'string',
                'addresses.shipping.city' => 'string',
                'addresses.shipping.state' => 'alpha|size:2',
                'addresses.shipping.zip' => 'min:5',
            ];
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'user.first_name.required' => 'First name is required.',
            'user.last_name.required' => 'Last name is required.',
            'user.email.required' => 'Email is required.',
            'user.email.email' => 'Email must be a valid email.',
            'payment.card_number.required' => 'Credit card number is required.',
            'payment.card_number.numeric' => 'Credit card number must be numeric.',
            'payment.security.required' => 'Security code is required.',
            'payment.security.alpha_num' => 'Security code cannot have special characters.',
            'payment.month.required' => 'Credit card expiration month is required.',
            'payment.year.required' => 'Credit card expiration year must be a whole number.',
            'addresses.billing.name.string' => 'Billing address name needs to be a string.',
            'addresses.billing.address_1.required' => 'Billing address line 1 is required.',
            'addresses.billing.address_1.string' => 'Billing address line 1 needs to be a string.',
            'addresses.billing.address_2.string' => 'Billing address line 2 needs to be a string.',
            'addresses.billing.city.required' => 'Billing address city is required.',
            'addresses.billing.city.string' => 'Billing address city needs to be a string.',
            'addresses.billing.state.required' => 'Billing address state is required.',
            'addresses.billing.state.alpha' => 'Billing address state needs to only use letters.',
            'addresses.billing.state.size' => 'Billing address state can only be 2 letters long.',
            'addresses.billing.zip.required' => 'Billing zip is required.',
            'addresses.billing.zip.min' => 'Billing zip needs to be at least 5 numbers long.',
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
            'invoice.required' => 'Must send a token to pay invoice.',
            'invoice.string' => 'Token must be a string.',
            'cashType' => 'Cash type is required.'
        ];
    }
}
