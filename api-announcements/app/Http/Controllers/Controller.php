<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Http\Requests\FormRequest;
use Illuminate\Http\Request;
use Validator;

class Controller extends BaseController
{
    public function validateRequest(FormRequest $formRequest, Request $request, $id = null)
    {
        $authorize = $formRequest->authorize($request, $id);
        if (!$authorize) {
            abort(401, "Unauthorized");
        }
        Validator::extendImplicit('no_spaces', function ($attribute, $value, $parameters, $validator) {
            if ($value === '' || ctype_space($value)) {
                return false;
            }
            return true;
        }, 'The :attribute cannot contain spaces or be an empty string.');
        parent::validate($request, $formRequest->rules($request, $id), $formRequest->messages($request));
    }
}
