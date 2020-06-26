<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\Product;

class CreateProductRequest extends Request
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
        $request = request()->all();
        $rules = Product::$rules;
        if (isset($request['type_id']) && $request['type_id'] == 5) {
            $rules += ['roles.*' => 'not_in:3,5|nullable'];
        }
        if ($this->setting->getGlobal('tax_classes_required', 'show')) {
            $rules += ['tax_class' => 'required'];
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'items.required' => 'You must add an item to create a product.',
            'roles.*' => 'Creating new fulfilled by corporate products can not be sold directly until a rep purchases it as a pack.'
        ];
    }
}
