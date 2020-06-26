<?php

namespace App\Http\Requests\Bundles;

use App\Http\Requests\FormRequest;
use App\Models\Media;
use App\Models\Bundle;

class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules($request, $id = null)
    {
        $rules = [
            'bundle_ids' => 'array',
            'search_term' => 'string',
            'available' => 'in:'.false.',false,0,'.true.',true,1',
            'sort_by' => 'sometimes|required|in:name,-name,created_at,-created_at,wholesale_price,-wholesale_price,relevance,-relevance,updated_at,-updated_at',
            'starter_kit' => 'in:'.false.',false,0,'.true.',true,1',
            'user_id' => 'integer',
            'per_page' => 'integer|max:20',
            'expands' => 'array',
            'expands.*' => 'in:categories,bundle_images,items,products,product_images,variant_images,variants,visibilities',
            'visibilities' => 'array',
            'categories' => 'array',
        ];
        if ($request->input('sort_by') === 'relevance') {
            $rules['search_term'] = 'required';
        }
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
        if ($request->input('sort_by') === 'relevance') {
            $messages['search_term.required'] = 'The search_term field is required when sorting by relevance.';
        }
        if ($request->has('available')) {
            $messages['user_id.required_without'] = 'The user_id field is required when the request has the available field and without user_pid.';
            $messages['user_pid.required_without'] = 'The user_pid field is required when the request has the available field and without user_id.';
        }
        return $messages;
    }
}
