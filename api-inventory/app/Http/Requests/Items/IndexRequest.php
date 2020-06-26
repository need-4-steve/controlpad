<?php

namespace App\Http\Requests\Items;

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
            'sort_by' => 'sometimes|required|in:wholesale_price,-wholesale_price,retail_price,-retail_price,premium_price,-premium_price,location,-location,relevance,-relevance,sku,-sku,created_at,-created_at,updated_at,-updated_at,product_name,-product_name,quantity_available,-quantity_available',
            'user_id' => 'integer',
            'per_page' => 'integer|max:100',
            'expands' => 'array',
            'expands.*' => 'in:variant,product,variant_images,product_images',
            'item_ids' => 'array',
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
            $messages['user_pid.required_without'] = 'The user_pid field is required when the request has the available field and without user_id';
        }
        return $messages;
    }
}
