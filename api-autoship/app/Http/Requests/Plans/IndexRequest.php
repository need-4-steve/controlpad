<?php

namespace App\Http\Requests\Plans;

use App\Http\Requests\FormRequest;
use App\Models\Plan;
use Illuminate\Http\Request;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Request $request, ?string $pid = null) : bool
    {
        if (!$request->user->hasRole(['Admin', 'Superadmin'])) {
            $request['visibilities'] = [$request->user->role];
            $request['show_disabled'] = false;
        }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request, ?string $pid = null) : array
    {
        $rules = [
            'search_term' => 'string',
            'show_disabled' => 'boolean',
            'sort_by' => 'sometimes|required|in:title,-title,relevance,-relevance',
            'per_page' => 'integer|max:100',
            'visibilities' => 'array',
        ];
        if ($request->input('sort_by') === 'relevance') {
            $rules['search_term'] = 'required';
        }
        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(Request $request) : array
    {
        $messages = [];
        if ($request->input('sort_by') === 'relevance') {
            $messages['search_term.required'] = 'The search_term field is required when sorting by relevance.';
        }
        return $messages;
    }
}
