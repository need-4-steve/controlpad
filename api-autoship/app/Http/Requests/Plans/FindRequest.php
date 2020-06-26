<?php

namespace App\Http\Requests\Plans;

use App\Http\Requests\FormRequest;
use App\Models\Plan;
use Illuminate\Http\Request;

class FindRequest extends FormRequest
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
            'show_disabled' => 'boolean',
            'visibilities' => 'array',
        ];
        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(Request $request) : array
    {
        return [];
    }
}
