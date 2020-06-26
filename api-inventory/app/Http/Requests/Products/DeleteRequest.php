<?php

namespace App\Http\Requests\Products;

use App\Http\Requests\FormRequest;
use App\Models\Product;
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
            $product = Product::select('products.id')
                ->where('products.id', $id)
                ->where('products.user_id', $request->user->getOwnerID())
                ->first();
            if (!isset($product)) {
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
                Rule::exists('products')->where(function ($query) use ($request) {
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
