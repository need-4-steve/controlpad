<?php

namespace App\Http\Requests;

class BatchShipmentUpdateRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'required|integer',
            'weight' => 'numeric',
            'mass_unit' => 'in:g,oz,lb,kg',
            'carrier_id' => 'integer|nullable',
            'service_level_id' => 'integer|nullable',
            'parcel_template_id' => 'integer|nullable'
        ];
    }

    public function messages()
    {
        return [
            'mass_unit.in' => 'Invalid mass unit. Valid mass units are: g, oz, lb and kg.'
        ];
    }
}
