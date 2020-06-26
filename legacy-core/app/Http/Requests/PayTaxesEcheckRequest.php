<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayTaxesEcheckRequest extends FormRequest
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
            'account_name' => ['required', "regex:/^[a-zA-Z][0-9a-zA-Z .'-]*$/"],
            'account_number' => 'required|numeric',
            'routing_number' => 'required|numeric'
        ];
    }
}
