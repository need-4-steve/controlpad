<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CouponCreateRequest extends Request
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
            'code'        => 'required|alpha_dash',
            'amount'      => 'required|numeric',
            'is_percent'  => 'required|boolean',
            'title'       => 'required|string',
            'description' => 'string',
            'max_uses'    => 'required|numeric',
            'type'        => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'code.required'        => 'Enter or generate coupon code.',
            'amount.required'      => 'Enter coupon value.',
            'is_percent.boolean'   => 'Select a discount type.',
            'title.required'       => 'Enter a coupon name.',
            'max_uses.required'    => 'Enter number of uses.',
            'type.required'        => 'Enter coupon type'
        ];
    }
}
