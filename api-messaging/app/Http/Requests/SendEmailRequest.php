<?php

namespace App\Http\Requests;

use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rule;

class SendEmailRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules($request, $id = null)
    {
      $rules = ['to' => 'required',
                'from' => 'required',
                'type' => 'required',
                'title' =>'sometimes',
                'subject' => 'required',
                'body' => 'required',
                'send_email' => 'required',
                'user.first_name' => 'sometimes',
                'user.last_name' => 'sometimes',
                'company_name' =>'required',
                'back_office_logo' => 'required',
                'company_address' =>'required'

                ];
      if($request->input('type') !== null &&
         $request->input('type') === 'preset' &&
         $request->input('title') !== null) {
          switch ($request['title']) {
              case 'sponsor_notice':
                  $rules = array_merge($rules, ['sponsor.first_name' => 'required',
                                                'sponsor.last_name' => 'required',
                                                'user.email' => 'required|email',
                                                'user.phone' => 'required']);
              break;
              case 'invoice':
                  $rules = array_merge($rules, ['invoice.amount' => 'required',
                                                'invoice.url' => 'required',
                                                'invoice.note' => 'required']);
              break;
              case 'fulfilled':
                  $rules =  array_merge($rules, ['orderlines' => 'required',
                                                'order.receipt_id' => 'required',
                                                'order.subtotal' => 'required',
                                                'order.tax' => 'required',
                                                'order.shipping' => 'required',
                                                'order.discount' => 'required',
                                                'order.total' => 'required']);
              break;
              case 'expire_notice':
                  $rules[] = '[billing_date]';
                    return $rules;
              case 'new_order_received':
                  $rules = array_merge($rules, ['customer_first_name' => 'required',
                                                'customer_last_name' => 'required',
                                                'customer_email' => 'required',
                                                'orderlines' => 'required',
                                                'order_receipt_id' => 'required']);
              break;
              case 'order_receipt':
                  $rules = array_merge($rules, ['orderlines' => 'required',
                                                'order_receipt_id' => 'required',
                                                'order_subtotal' => 'required',
                                                'order_tax' => 'required',
                                                'order_shipping' => 'required',
                                                'order_discount' => 'required',
                                                'order_total' => 'required']);
              break;
              case 'renew_fail':
                  $rules = array_merge($rules, ['billing_date',
                                                'reason']);
              break;
            }
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages($request)
    {
        $messages = [];
        return $messages;
    }
}
