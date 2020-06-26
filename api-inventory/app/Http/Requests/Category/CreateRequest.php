<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\FormRequest;
use App\Models\Category;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules($request, $id = null)
    {
        $rules = Category::$rules;
        if ($request->input('parent_id') === "") {
            $error = ['parent_id' => 'parent_id cannot be an empty string.'];
            abort(422, 'parent_id cannot be an empty string');
        }
        $rules['parent_id'] = [
            'nullable',
            'integer',
            Rule::exists('categories', 'id')->where(function ($query) {
                $query->where('level', '=', 0);
            }),
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
        $messages = [
            'parent_id.exists' => 'The selected parent id is invalid. The parent must have a level of 0.'
        ];
        return $messages;
    }
}
