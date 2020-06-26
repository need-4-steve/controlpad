<?php

namespace App\Http\Requests\Variants;

use App\Http\Requests\FormRequest;
use App\Models\Variant;

class FindRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules($request, $id = null)
    {
        $rules = [
            'price' => 'in:wholesale,premium,retail,inventory',
            'available' => 'in:'.false.',false,0,'.true.',true,1',
            'user_id' => 'integer',
            'expands' => 'array',
            'expands.*' => 'in:variant_images,visibilities',
            'visibilities' => 'array',
        ];
        if ($request->input('price') === 'inventory' || $request->has('available')) {
            $rules['user_id'] .= '|required_without:user_pid';
            $rules['user_pid'] = 'required_without:user_id';
        }
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
        if ($request->input('price') === 'inventory') {
            $messages['user_id.required_without'] = 'The user_id field is required when price is inventory and without user_pid.';
            $messages['user_pid.required_without'] = 'The user_pid field is required when price is inventory and without user_id.';
        } elseif (!$request->has('user_id') && $request->has('available')) {
            $messages['user_id.required_without'] = 'The user_id field is required when the request has the available field and without user_pid.';
            $messages['user_pid.required_without'] = 'The user_pid field is required when the request has the available field and without user_id.';
        }
        return $messages;
    }
}
