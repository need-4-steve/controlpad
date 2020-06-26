<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UserSettingsUpdateRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user_id = request()->get('user_id');

        if ($user_id != auth()->user()->id
            && ! auth()->user()->hasRole(['Admin', 'Superadmin'])
        ) {
            return false;
        }

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
        if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
                    $rules = [];
        } else {
            $rules = [
                'user_id' => 'required|integer',
                'hide_products' => 'sometimes|boolean',
                'show_address' => 'required|boolean',
                'show_phone' => 'required|boolean',
                'show_email' => 'required|boolean',
                'will_deliver' => 'required|boolean'
            ];
        }

        return $rules;
    }
}
