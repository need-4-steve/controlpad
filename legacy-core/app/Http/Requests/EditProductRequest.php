<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Http\Requests\Request;
use App\Services\Settings\SettingsService;

class EditProductRequest extends Request
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

        $rules['name'] = 'required|unique:products,name,' . $request['id'];
        $rules['slug'] = 'required|unique:products,name,' . $request['id'];

        foreach ($request['items'] as $key => $item) {
            if (isset($item['id'])) {
                $rules['items.' . $key . '.manufacturer_sku'] =
                'required|distinct|unique:items,manufacturer_sku,'
                . $request['items'][$key]['id'];
            } else {
                $rules['items.' . $key . '.manufacturer_sku'] = 'required|distinct|unique:items,manufacturer_sku';
            }
        }
        if ($this->setting->getGlobal('tax_classes_required', 'show')) {
            $rules += ['tax_class' => 'required'];
        }

        return $rules;
    }
    public function messages()
    {
        return [
            //item custom messages
            'items.*.size.required' => 'All items require a size.',
            'items.*.weight.required' => 'All items require a weight.',
            'items.*.length.required' => 'All items require a length.',
            'items.*.height.required' => 'All items require a height.',
            'items.*.wholesale_price.price.required' => 'All items require a wholesale price.',
            'items.*.msrp.price.required' => 'All items require a retail price.',
            'items.*.premium_price.price.required' => 'All items require a premium price.',
            'items.*.is_default.price.required' => 'Setting a default item for the product is required.',
            'items.*.manufacturer_sku.required' => 'All items require a manufacturer sku.',
        ];
    }
}
