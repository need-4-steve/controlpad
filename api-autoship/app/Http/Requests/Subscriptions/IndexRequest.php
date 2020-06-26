<?php

namespace App\Http\Requests\Subscriptions;

use App\Http\Requests\FormRequest;
use App\Models\Subscription;
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
            $request->buyer_pid = $request->user->pid;
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
            'date_column' => 'in:created_at,updated_at,next_billing_at',
            'end_date' => 'sometimes|required|date',
            'expands' => 'array',
            'expands.*' => 'in:attempts,cycle_attempts,last_attempt,lines',
            'filter' => 'in:all,active,failed,renewing_soon,disabled',
            'search_term' => 'string',
            'start_date' => 'sometimes|required|date',
            'sort_by' => 'sometimes|required|in:created_at,-created_at,next_billing_at,-next_billing_at,updated_at,-updated_at,relevance,-relevance,buyer_first_name,-buyer_first_name,buyer_last_name,-buyer_last_name,disabled_at,-disabled_at',
            'per_page' => 'integer|max:100',
            'show_disabled' => 'in:true,false,1,0'.true.','.false
        ];
        if ($request->input('sort_by') === 'relevance') {
            $rules['search_term'] .= '|required';
        }
        if ($request->has('start_date') || $request->has('end_date')) {
            $rules['date_column'] .= '|required';
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
        $messages['date_column.required'] = 'The date_column field is required when start_date or end_date is in the request.';
        return $messages;
    }
}
