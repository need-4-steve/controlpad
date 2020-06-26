<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class InvoiceCreateRequest extends Request
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
            'cart.subtotal_price' => 'required|numeric|min:0',
            'cart.total_shipping' => 'required|numeric|min:0',
            'items' => 'required',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'user.first_name' => 'required|string',
            'user.last_name' => 'required|string',
            'user.email' => 'required|email',
            'user.*.phones.number' => 'numeric',
            'user.*.phones.label' => 'string',
            'user.*.phones.type' => 'string',
            'addresses.shipping.name' => 'sometimes|string',
            'addresses.shipping.address_1' => 'sometimes|string',
            'addresses.shipping.address_2' => 'sometimes|string',
            'addresses.shipping.city' => 'sometimes|string',
            'addresses.shipping.state' => 'sometimes|alpha|size:2',
            'addresses.shipping.zip' => 'sometimes|min:5',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'cart.subtotal_price.required' => 'A subtotal price is required.',
            'cart.subtotal_price.numeric' => 'Subtotal price must be numeric.',
            'cart.subtotal_price.min' => 'Subtotal price must be 0 or over.',
            'cart.total_shipping.required' => 'Shipping total is required.',
            'cart.total_shipping.numeric' => 'Shipping must be numeric.',
            'cart.total_shipping.min' => 'Shipping must be 0 or over.',
            'items.required' => 'Must have items in the cart.',
            'items.*.item_id.required' => 'Item id is required.',
            'items.*.item_id.integer' => 'Item id must be an whole number.',
            'items.*.quantity.required' => 'Item quantity is required.',
            'items.*.quantity.integer' => 'Item quantity must be a whole number.',
            'items.*.quantity.min' => 'Item must have at least one quantity.',
            'user.first_name.required' => 'First name is required.',
            'user.last_name.required' => 'Last name is required.',
            'user.email.required' => 'Email is required.',
            'user.email.email' => 'Email must be a valid email.',
            'addresses.shipping.address_1.string' => 'Mailing address line 1 needs to be a string.',
            'addresses.shipping.address_2.string' => 'Mailing address line 2 needs to be a string.',
            'addresses.shipping.city.string' => 'Mailing address city needs to be a string.',
            'addresses.shipping.state.alpha' => 'Mailing address state needs to only use letters.',
            'addresses.shipping.state.size' => 'Mailing address state can only be 2 letters long.',
            'addresses.shipping.zip.min' => 'Mailing zip needs to be at least 5 numbers long.',
        ];
    }
}
