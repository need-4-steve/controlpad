<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UserEditRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = request()->all();
        if ($user['id'] === auth()->user()->id or
            auth()->user()->id === 1 and $user['id'] !== 109 or
            auth()->user()->id === 109 or
            auth()->user()->hasRole('Superadmin') and $user['id'] !== 109 or
            auth()->user()->hasRole('Admin') and $user['role_id'] === 5 or
            auth()->user()->hasRole('Admin') and $user['role_id'] === 3) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $request = request()->all();
        // ensure public id is validated as string to lower
        if (isset($request['public_id'])) {
            $request['public_id'] = strtolower($request['public_id']);
        }
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'sometimes|required|email|unique:users,email,' . $request['id'],
            'public_id' => 'sometimes|alpha_dash|required|unique:users,public_id,' . $request['id'],
            'phone.number' => 'sometimes|min:7|max:10',
        ];

        if (isset($request['shipping_address'])) {
            $rules['shipping_address.address_1'] = 'required';
            $rules['shipping_address.address_2'] = 'sometimes';
            $rules['shipping_address.state'] = 'required|alpha|between:2,2';
            $rules['shipping_address.city'] = "required|regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/";
            $rules['shipping_address.zip'] = 'required|regex:/^[0-9]{5}([- ]?[0-9]{4})?$/';
        }
        if (isset($request['billing_address'])) {
            $rules['billing_address.address_1'] = 'required';
            $rules['billing_address.address_2'] = 'sometimes';
            $rules['billing_address.state'] = 'required|alpha|between:2,2';
            $rules['billing_address.city'] = "required|regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/";
            $rules['billing_address.zip'] = 'required|regex:/^[0-9]{5}([- ]?[0-9]{4})?$/';
        }
        if (isset($request['business_address'])) {
            $rules['business_address.address_1'] = 'required';
            $rules['business_address.address_2'] = 'sometimes';
            $rules['business_address.state'] = 'required|alpha|between:2,2';
            $rules['business_address.city'] = "required|regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/";
            $rules['business_address.zip'] = 'required|regex:/^[0-9]{5}([- ]?[0-9]{4})?$/';
        }

        if (isset($request['password']) && $request['password'] !== '') {
            $rules['password'] = 'sometimes|min:8|confirmed';
        }
        if ($request['role']['id'] !== 5 && $request['public_id'] === null) {
            unset($request['public_id']);
            $rules['public_id'] = 'sometimes';
        }

        return $rules;
    }
}
