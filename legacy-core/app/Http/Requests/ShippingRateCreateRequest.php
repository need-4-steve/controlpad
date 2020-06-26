<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ShippingRateCreateRequest extends Request
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
            'ranges.*.amount' => 'required|numeric',
            'ranges.*.max'    => 'required|nullable',
            'ranges.*.name'   => 'required|string'
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'amount.required'   => 'The amount field is required',
            'max.required'      => 'The price point field is required',
            'name.required'     => 'The name field is required',
        ];
    }
}
