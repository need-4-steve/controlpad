<?php

namespace App\Http\Requests\Subscriptions;

use App\Http\Requests\FormRequest;
use App\Models\Subscription;
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
        if ($request->user->hasRole(['Rep'])) {
            $subscription = Subscription::select('autoship_subscriptions.pid')
                ->where('autoship_subscriptions.pid', $pid)
                ->where(function ($query) use ($request) {
                    $query->where('autoship_subscriptions.buyer_pid', $request->user->pid)
                        ->orWhere('autoship_subscriptions.seller_pid', $request->user->pid);
                })
                ->first();
            if (!isset($subscription)) {
                return false;
            }
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
            'expands' => 'array',
            'expands.*' => 'in:attempts,cycle_attempts,last_attempt,lines'
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
