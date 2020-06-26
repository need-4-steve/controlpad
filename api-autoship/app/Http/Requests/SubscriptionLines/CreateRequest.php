<?php

namespace App\Http\Requests\SubscriptionLines;

use App\Http\Requests\FormRequest;
use App\Models\SubscriptionLine;
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
        $rules = SubscriptionLine::$rules;
        $rules['subscription_pid'] = [
            Rule::exists('autoship_subscriptions', 'pid')->where(function ($query) use ($request) {
                $query->whereNull('deleted_at')
                    ->where('pid', $request->input('subscription_pid'));
            }),
            'required'
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
