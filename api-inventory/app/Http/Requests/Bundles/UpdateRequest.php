<?php

namespace App\Http\Requests\Bundles;

use App\Http\Requests\FormRequest;
use App\Models\Bundle;

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
            $bundle = Bundle::select('bundles.id')
                ->where('bundles.id', $id)
                ->where('bundles.user_id', $userId)
                ->first();
            if (!isset($bundle)) {
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
        $rules = Bundle::$rules;
        $rules['name'] = 'sometimes|required|unique:bundles,name,'.$id;
        $rules['slug'] = 'sometimes|required|alpha_dash|unique:bundles,slug,'.$id;
        $rules['user_id'] = 'sometimes|required|integer';
        $rules['wholesale_price'] = 'sometimes|required|numeric|min:0.01';
        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages($request)
    {
        return [];
    }
}
