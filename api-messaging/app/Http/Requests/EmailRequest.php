<?php

namespace App\Http\Requests;

use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rule;

class EmailRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules($request, $id = null)
    {
      $rules = ['title' =>'sometimes',
                'subject' => 'required',
                'body' => 'required',
                'send_email' => 'required',
                'display_name' => 'required'
                ];

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages($request)
    {
        $messages = [];
        return $messages;
    }
}
