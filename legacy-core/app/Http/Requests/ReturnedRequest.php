<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ReturnModel;

class ReturnedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

     /**
      * Get the validation rules that apply to the request.
      *
      * @return array
      */
    public function rules()
    {
        $rules = [
            'return_items' => 'required|min:1',
            'return_items.*.reason_id' => 'required',
            'return_items.*.return_quantity' => 'required',
            'returnitems.*.orderline_id' => 'required'
        ];
        return $rules;
    }


    public function messages()
    {
         return [
             'return_items' => 'You must add check box.',
             'reason_id' => 'You must have a reason for the return.',
             'return_quantity' => 'You need a request quantity',
             'orderline_id' => 'Something when wrong please try again.'
         ];
    }
}
