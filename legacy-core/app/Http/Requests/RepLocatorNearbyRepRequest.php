<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RepLocatorNearbyRepRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $request = request()->all();
        if (empty($request['repName'])) {
            return [
                'state' => 'required|string',
                'miles' => 'required|numeric',
                'address' => 'required|string',
                'city' => 'required|string',
                'zip' => 'required|numeric'
            ];
        }
        return [];
    }
}
