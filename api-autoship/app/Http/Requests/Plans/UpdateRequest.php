<?php

namespace App\Http\Requests\Plans;

use App\Http\Requests\FormRequest;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Request $request, ?string $pid = null) : bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request, ?string $pid = null) : array
    {
        $request['pid'] = $pid;
        $rules = Plan::$rules;
        $rules['pid'] = Rule::exists('autoship_plans', 'pid')->where(function ($query) use ($request) {
            $query->whereNull('deleted_at');
        });
        if ($request->has('discounts') && is_array($request->input('discounts'))) {
            $quantity = 1;
            $percentage = 0;
            foreach ($request->input('discounts') as $key => $discount) {
                if (isset($discount['min_quantity']) && isset($discount['percent'])) {
                    $rules['discounts.'.$key.'.min_quantity'] = 'required_with:discounts|integer|min:'.$quantity;
                    $rules['discounts.'.$key.'.percent'] = 'required_with:discounts|numeric|min:'.$percentage;
                    $quantity = $discount['min_quantity'] + 1;
                    $percentage = $discount['percent'] + 0.01;
                }
            }
        }
        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(Request $request) : array
    {
        return [];
    }
}
