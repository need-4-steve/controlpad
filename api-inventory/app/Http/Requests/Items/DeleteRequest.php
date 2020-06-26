<?php

namespace App\Http\Requests\Items;

use App\Http\Requests\FormRequest;
use App\Models\Item;
use Illuminate\Validation\Rule;

class DeleteRequest extends FormRequest
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
        $request['id'] = $id;
        $rules = [
            'id' => [
                Rule::exists('items')->where(function ($query) use ($request) {
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
    public function messages($request)
    {
        $messages = [];
        return $messages;
    }
}
