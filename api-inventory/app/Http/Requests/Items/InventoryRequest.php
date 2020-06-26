<?php

namespace App\Http\Requests\Items;

use App\Http\Requests\FormRequest;
use App\Models\Inventory;

class InventoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize($request, $id = null) : bool
    {
        if ($request->user->hasRole(['Rep'])) {
            $request['user_id'] = $request->user->getOwnerId();
        }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules($request, $id = null)
    {
        $request->request->add(['item_id' => $id]);
        $rules = [
            'item_id' => 'exists:items,id',
            'user_id' => 'required|integer',
            'quantity' => 'integer',
            'inventory_price' => [
                'nullable',
                'numeric',
                'between:0.00,999999.99',
                'regex:/^\d*(\.\d{2})?$/',
                'no_spaces'
            ],
            'disable' => 'in:'.false.',false,0,'.true.',true,1',
        ];
        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages($request)
    {
        $messages = [
            'inventory_price.regex' => 'inventory_price must be formated to two decimal places.'
        ];
        return $messages;
    }
}
