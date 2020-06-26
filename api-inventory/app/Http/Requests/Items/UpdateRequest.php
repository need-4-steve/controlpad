<?php

namespace App\Http\Requests\Items;

use App\Http\Requests\FormRequest;
use App\Models\Item;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize($request, $id = null) : bool
    {
        if ($request->user->hasRole(['Rep'])) {
            $userId = $request->user->getOwnerID();
            $variant = Item::select('items.id')
                ->where('items.id', $id)
                ->join('variants', 'variants.id', '=', 'items.variant_id')
                ->join('products', 'products.id', '=', 'variants.product_id')
                ->where('products.user_id', $userId)
                ->first();
            if (!isset($variant)) {
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
    public function rules($request, $id = null)
    {
        $rules = Item::$rules;
        $rules['variant_id'] = 'sometimes|required|integer|exists:variants,id';
        $rules['sku'] = 'sometimes|required|string|max:255|unique:items,manufacturer_sku,'.$id;
        $rules['option'] = [
            'sometimes',
            'required',
            'string',
            'max:255',
            Rule::unique('items', 'size')->where(function ($query) use ($request, $id) {
                if ($request->has('variant_id')) {
                    $query->where('variant_id', $request['variant_id'])
                        ->where('items.id', '!=', $id)
                        ->whereNull('deleted_at');
                } else {
                    $variantId = Item::where('id', $id)->pluck('variant_id');
                    $query->where('variant_id', $variantId)
                        ->where('items.id', '!=', $id)
                        ->whereNull('deleted_at');
                }
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
