<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class BankUpdateRequest extends Request
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
            'bankName'      => 'required',
            'name'          => 'required|string',
            'number'        => 'required',
            'routing'       => 'required',
            'type'          => 'required|string',
            'validated'     => 'sometimes|boolean',
            'authorization' => 'required|accepted'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'routing.required' => 'Invalid or no routing number supplied.',
            'authorization.accepted' => 'You must agree to the banking authorization to add new bank information.'
        ];
    }
}
