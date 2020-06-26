<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class SubscriptionPlanRequest extends Request
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
      if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
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
      if ($this->setting->getGlobal('tax_subscription', 'show') === true) {
          $rules = [
            'title' => 'required',
            'price' => 'required',
            'renewable' => 'required',
            'seller_type_id' => 'required|integer',
            'free_trial_time' => 'required',
            'on_sign_up' => 'required',
            'tax_class' => 'required|size:8'
          ];
      } else {
          $rules = [
            'title' => 'required',
            'price' => 'required',
            'renewable' => 'required',
            'seller_type_id' => 'required|integer',
            'free_trial_time' => 'required',
            'on_sign_up' => 'required',
            'tax_class' => 'sometimes|size:8'
          ];
      }
        return $rules;
    }
}
