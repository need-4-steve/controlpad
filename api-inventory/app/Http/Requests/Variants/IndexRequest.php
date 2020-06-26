<?php

namespace App\Http\Requests\Variants;

use App\Http\Requests\FormRequest;
use App\Models\Media;

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
            'search_term' => 'string',
            'available' => 'in:'.false.',false,0,'.true.',true,1',
            'sort_by' => 'sometimes|required|in:name,-name,price,-price,relevance,-relevance,created_at,-created_at,updated_at,-updated_at',
            'price' => 'in:wholesale,premium,retail,inventory',
            'user_id' => 'integer',
            'per_page' => 'integer|max:100',
            'expands' => 'array',
            'expands.*' => 'in:product,product_images,variant_images,visibilities',
            'visibilities' => 'array',
            'variant_ids' => 'array',
            'item_ids' => 'array'
        ];
        if ($request->input('sort_by') === 'price' || $request->input('sort_by') === '-price') {
            $rules['price'] .= '|required';
        }
        if ($request->input('sort_by') === 'relevance') {
            $rules['search_term'] = 'required';
        }
        if ($request->input('price') === 'inventory' ||
            ($request->has('available'))) {
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
        if ($request->input('sort_by') === 'price' || $request->input('sort_by') === '-price') {
            $messages['price.required'] = 'The price field is required when sorting by price.';
        }
        if ($request->input('sort_by') === 'relevance') {
            $messages['search_term.required'] = 'The search_term field is required when sorting by relevance.';
        }
        if ($request->input('price') === 'inventory') {
            $messages['user_id.required_without'] = 'The user_id field is required when price is inventory and without user_pid.';
            $messages['user_pid.required_without'] = 'The user_pid field is required when price is inventory and without user_id.';
        } elseif ($request->has('available')) {
            $messages['user_id.required_without'] = 'The user_id field is required when the request has the available field and without user_pid.';
            $messages['user_pid.required_without'] = 'The user_pid field is required when the request has the available field and without user_id.';
        }
        return $messages;
    }
}
