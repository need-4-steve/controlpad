<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\FormRequest;
use App\Models\Category;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules($request, $id = null)
    {
        $rules = Category::$rules;
        $categoryCount = Category::where('parent_id', $id)->count();
        if ($categoryCount > 0) {
            $error = ['parent_id' => 'Cannot change the parent id of a category that already is a parent.'];
            abort(422, 'Cannot change the parent id of a category that already is a parent.');
        }
        if ($request->input('parent_id') === "") {
            $error = ['parent_id' => 'parent cannot be an empty string.'];
            abort(422, 'parent_id cannot be an empty string');
        }
        $rules['name'] = 'unique:categories,name,'.$id;
        $rules['parent_id'] = [
            'nullable',
            'integer',
            Rule::exists('categories', 'id')->where(function ($query) use ($id) {
                $query->where('level', '=', 0)
                    ->where('id', '!=', $id);
            })
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
