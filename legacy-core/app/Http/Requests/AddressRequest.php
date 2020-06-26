<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;

class AddressRequest extends Request
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
            'name'              => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'address_1'         => 'required|string',
            'address_2'         => 'sometimes|string|nullable',
            'state'             => 'required|alpha|between:2,2',
            'city'              => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'zip'               => 'required|regex:/^[0-9]{5}([- ]?[0-9]{4})?$/', // Allows 5 digit zip and a 9 digit zip with a dash.
            'addressable_type'  => "in:App\Models\Order,App\Models\User",
            'addressable_id'    => 'sometimes|int|nullable',
            'label'             => 'required|string'
        ];
    }
}
