<?php

namespace App\Http\Requests\Items;

use App\Http\Requests\FormRequest;
use App\Models\Item;
use App\Models\Media;

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
            'user_id' => 'integer',
            'expands' => 'array',
            'expands.*' => 'in:variant,product,variant_images,product_images',
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
