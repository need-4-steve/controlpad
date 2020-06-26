<?php

namespace App\Http\Requests\Items;

use App\Http\Requests\FormRequest;
use App\Models\Item;
use App\Models\Variant;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize($request, $id = null) : bool
    {
        if ($request->user->hasRole(['Rep'])) {
            if ($request->has('variant_id')) {
                $userId = $request->user->getOwnerID();
                $product = Variant::select('products.id')
                    ->where('variants.id', $request->input('variant_id'))
                    ->join('products', 'products.id', '=', 'variants.product_id')
                    ->where('products.user_id', $userId)
                    ->first();
                if (!isset($product)) {
                    return false;
                }
            }
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
        $rules = Item::$rules;
        $rules['option'] = [
            'sometimes',
            'required',
            Rule::unique('items', 'size')->where(function ($query) use ($request) {
                $query->where('variant_id', $request['variant_id'])
                    ->whereNull('deleted_at');
            })
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
        $messages = [];
        return $messages;
    }
}
