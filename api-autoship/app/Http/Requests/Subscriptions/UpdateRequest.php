<?php

namespace App\Http\Requests\Subscriptions;

use App\Http\Requests\FormRequest;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Request $request, ?string $pid = null) : bool
    {
        if ($request->user->hasRole(['Rep'])) {
            $request = $request->only('disable');
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
        $request['pid'] = $pid;
        $rules = [
            'buyer_first_name'      => 'sometimes|required|string',
            'buyer_last_name'       => 'sometimes|required|string',
            'plan_pid'              => 'nullable|exists:autoship_plans,pid',
            'pid'                   => 'exists:autoship_subscriptions,pid',
            'disable'               => 'sometimes|required|in:true,false,1,0,'.true.','.false,
            'cart_pid'              => 'string',
            'next_billing_at'       => 'after:yesterday',
            'duration'              => 'in:Days,Weeks,Months,Quarters,Years',
            'free_shipping'         => 'sometimes|required|in:true,false,1,0,'.true.','.false,
            'frequency'             => 'integer',
        ];
        $rules['plan_pid'] = [
            Rule::exists('autoship_plans', 'pid')->where(function ($query) use ($request) {
                $query->whereNull('deleted_at')
                    ->where('pid', $request->input('plan_pid'));
            }),
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
