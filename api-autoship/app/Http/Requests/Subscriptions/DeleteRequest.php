<?php

namespace App\Http\Requests\Subscriptions;

use App\Http\Requests\FormRequest;
use App\Models\Subscription;
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
            $subscription = Subscription::select('autoship_subscriptions.pid')
                ->where('autoship_subscriptions.pid', $pid)
                ->where('autoship_subscriptions.buyer_pid', $request->user->pid)
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
        $request['pid'] = $pid;
        $rules = [
            'pid' => [
                Rule::exists('autoship_subscriptions')->where(function ($query) use ($request) {
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
