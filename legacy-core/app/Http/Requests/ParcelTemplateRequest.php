<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParcelTemplateRequest extends FormRequest
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
            'name' => 'required|string',
            'length' => 'required|numeric',
            'width' => 'required|numeric',
            'height' => 'required|numeric',
            'distance_unit' => 'required|in:in,cm,mm'
        ];
    }
}
