<?php

namespace App\Http\Requests\Plans;

use App\Http\Requests\FormRequest;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Request $request, ?string $pid = null) : bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request, ?string $pid = null) : array
    {
        $request['pid'] = $pid;
        $rules['pid'] = Rule::exists('autoship_plans', 'pid')->where(function ($query) use ($request) {
            $query->whereNull('deleted_at');
        });
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
