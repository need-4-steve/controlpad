<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Http\Requests\FormRequest;
use Illuminate\Http\Request;
use Validator;

class Controller extends BaseController
{
    public function validateRequest(FormRequest $formRequest, Request $request, ?string $pid = null) : void
    {
        $authorize = $formRequest->authorize($request, $pid);
        if (!$authorize) {
            abort(403, "Forbidden");
        }
        parent::validate($request, $formRequest->rules($request, $pid), $formRequest->messages($request));
    }
}
