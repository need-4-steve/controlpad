<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

/*
| Intended for use with purely api requests
| This will assume our authorization is being done elsewhere and will not redirect the response on failure
| Natural function is to redirect when a non ajax request (ex. mobile)
*/
class ApiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function response(array $errors)
    {
        return new JsonResponse($errors, 422);
    }
}
