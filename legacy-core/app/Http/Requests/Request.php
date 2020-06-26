<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

abstract class Request extends FormRequest
{
    protected function formatErrors(Validator $validator)
    {
        $errors = $validator->errors()->all();
        return $validator->getMessageBag()->toArray();
    }
}
