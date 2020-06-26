<?php

namespace App\Http\Requests;

class NotifyOrderFulfilledRequest extends ApiRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'event' => 'required|in:order-fulfilled',
            'data.order' => 'required',
            'data.order.buyer_email' => 'required|email',
        ];
    }
}
