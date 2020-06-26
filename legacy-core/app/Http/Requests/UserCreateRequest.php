<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UserCreateRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
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
        if (isset($request['role']['id']) == 7 || isset($request['role']['id']) == 8) {
            if ($request['public_id'] == null or $request['public_id'] == "") {
                unset($request['public_id']);
            }
        }

        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => 'required|min:8|confirmed',
            'role' => 'required',
            'email' => 'required|email|unique:users,email',
            'public_id' => 'sometimes|required|unique:users,public_id',
            'address_1' => 'required',
            'address_2' => 'sometimes',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required|digits_between:5,10',
            'phone.number' => 'sometimes|min:10|max:11'
        ];
        return $rules;
    }
}
