<?php

namespace App\Http\Requests\Variants;

use App\Http\Requests\FormRequest;
use App\Models\Product;
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
            if ($request->has('product_id')) {
                $userId = $request->user->getOwnerID();
                $product = Product::select('products.id')
                    ->where('products.id', $request->input('product_id'))
                    ->where('products.user_id', $userId)
                    ->first();
                if (!isset($product)) {
                    return false;
                }
            }
            $request['user_id'] = $request->user->getOwnerID();
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
        $rules = Variant::$rules;
        $rules['name'] = [
            'required',
            Rule::unique('variants', 'name')->where(function ($query) use ($request) {
                if ($request->has('product_id')) {
                    $query->where('product_id', $request['product_id'])
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
