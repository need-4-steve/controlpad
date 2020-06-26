<?php

namespace App\Http\Requests\Subscriptions;

use App\Http\Requests\FormRequest;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
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
        $rules = Subscription::$rules;
        $rules['cart_pid'] = 'required|string';
        $rules['plan_pid'] = [
            'nullable',
            Rule::exists('autoship_plans', 'pid')->where(function ($query) use ($request) {
                $query->whereNull('deleted_at')
                    ->whereNull('disabled_at')
                    ->where('pid', $request->input('plan_pid'));
            }),
        ];
        if ($request->user->hasRole(['Rep', 'Customer'])) {
            array_push($rules['plan_pid'], ['required']);
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
        return [];
    }
}
