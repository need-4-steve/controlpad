<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetupPaymentProviderRequest extends FormRequest
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
            'provider' => 'required',
            'name' => 'required|alpha_spaces',
            'phone' => 'required|digits_between:10,13',
            'type' => 'required',
            'owner.ssn' => 'required|digits:9',
            'address.address_1' => 'required',
            'address.city' => 'required',
            'address.state' => 'required',
            'address.zip' => 'required',
            'account.routing' => 'required',
            'account.number' => 'required',
            'account.type' => 'required',

        ];
        $rules['owner.first_name'] = 'required';
        $rules['owner.last_name'] = 'required';
        $rules['owner.dob'] = 'required|date';
        $rules['owner.phone'] = 'required|digits_between:10,11';
        $rules['owner.email'] = 'required|email';

        if ($request['provider'] === 'splash_option_2') {
            $rules['established'] = 'required';
            $rules['ein'] = 'required|digits:9';
            $rules['owner.ownership'] = 'required';
            $rules['owner.address.address_1'] = 'required';
            $rules['owner.address.zip'] = 'required';
            $rules['owner.address.state'] = 'required';
            $rules['owner.address.city'] = 'required';
        }
        return $rules;
    }

    public function messages()
    {
        return [ 'name.alpha_spaces' => 'There is a invalid character in your business name.'];
    }
}
