<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\Bundle;
use Illuminate\Contracts\Validation\Validator;

class EditBundleRequest extends Request
{
    public function __construct()
    {
        $this->setting = app('globalSettings');
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (auth()->check() && $this->setting->getGlobal('reseller_create_product', 'show') === true
            || auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = request()->get('id');
        $rules = Bundle::$rules;
        $rules['items'] = 'required';
        $rules['name'] = 'required|unique:bundles,name,' . $id;
        $rules['slug'] = 'required|unique:bundles,slug,' . $id;
        $request = request()->all();
        if (isset($request['type_id']) and $request['type_id'] === 2) {
            $rules['duration'] = 'required|int|min:1';
            if ($request['starter_kit'] == true) {
                $rules['starter_kit'] = 'required|boolean|in:false';
            }
        }
        if (isset($request['starter_kit']) and $request['starter_kit'] == true) {
            $rules['wholesale_price'] = 'required|numeric|min:0.00';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'items.required' => 'You must add products to make a pack.',
            'starter_kit.in' => 'A starter kit cannot contain products that are fulfilled by corporate.'
        ];
    }
}
