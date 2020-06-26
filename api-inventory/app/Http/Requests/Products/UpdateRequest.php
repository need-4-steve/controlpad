<?php

namespace App\Http\Requests\Products;

use App\Http\Requests\FormRequest;
use App\Models\Product;

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
            $product = Product::select('products.id')
                ->where('products.id', $id)
                ->where('products.user_id', $userId)
                ->first();
            if (!isset($product)) {
                return false;
            }
            $request['user_id'] = $userId;
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
        $rules = Product::$rules;
        $rules['name'] = 'sometimes|required|unique:products,name,'.$id;
        $rules['slug'] = 'sometimes|required|alpha_dash|unique:products,slug,'.$id;
        $rules['user_id'] = 'sometimes|required|integer';
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
            'max.required' => 'The max field cannot be empty',
            'min.required' => 'The min field cannot be empty',
            'images.*.url.regex' => 'File type must be gif, jpg, jpeg or png',
        ];
        return $messages;
    }
}
