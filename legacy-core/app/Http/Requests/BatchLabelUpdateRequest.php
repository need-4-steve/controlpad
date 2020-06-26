<?php

namespace App\Http\Requests;

class BatchLabelUpdateRequest extends ApiRequest
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
            'carrier_id' => 'integer',
            'service_level_id' => 'integer',
            'parcel_template_id' => 'integer'
        ];
    }
}
