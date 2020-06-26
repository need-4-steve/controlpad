<?php

namespace App\Http\Requests\SubscriptionLines;

use App\Http\Requests\FormRequest;
use App\Models\SubscriptionLine;
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
        if ($request->user->hasRole(['Rep'])) {
            $subscriptionLine = SubscriptionLine::select('autoship_subscription_lines.pid')
                ->join('autoship_subscriptions', 'autoship_subscriptions.id', '=', 'autoship_subscription_lines.autoship_subscription_id')
                ->where('autoship_subscription_lines.pid', $pid)
                ->where(function ($query) use ($request) {
                    $query->where('autoship_subscriptions.buyer_pid', $request->user->pid)
                        ->orWhere('autoship_subscriptions.seller_pid', $request->user->pid);
                })
                ->first();
            if (!isset($subscriptionLine)) {
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
        $request['pid'] = $pid;
        $rules = [
            'pid' => [
                Rule::exists('autoship_subscription_lines')->where(function ($query) use ($request) {
                    $query->whereNull('deleted_at');
                }),
            ],
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
