<?php

namespace App\Http\Requests\Bundles;

use App\Http\Requests\FormRequest;
use App\Models\Media;
use App\Models\Bundle;

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
            'available' => 'in:'.false.',false,0,'.true.',true,1',
            'user_id' => 'integer|nullable',
            'starter_kit' => 'in:'.false.',false,0,'.true.',true,1',
            'expands' => 'array',
            'expands.*' => 'in:categories,bundle_images,items,products,product_images,variant_images,variants,visibilities',
            'visibilities' => 'array',
            'categories' => 'array',
        ];
        if ($request->has('available')) {
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
        if ($request->has('available')) {
            $messages['user_id.required_without'] = 'The user_id field is required when the request has the available field and without user_pid.';
            $messages['user_pid.required_without'] = 'The user_pid field is required when the request has the available field and without user_id';
        }
        return $messages;
    }
}
