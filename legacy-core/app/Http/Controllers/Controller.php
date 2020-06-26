<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $messages = [
        'Unauthorized' => 'You do not have the necessary permissions to access this resource.',
        'InvalidToken' => 'The token passed is invalid.',
        'Success' => 'Success',
        'feature_disabled' => 'This feature has been disabled by admin.'
    ];

    /**
     * A common response format to use when returning JSON
     *
     * @param $errorCode
     * @param $statusCode
     * @param $message
     * @param null $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function createResponse($errorCode, $statusCode, $message, $data = null)
    {
        return response()->json(
            [
                'error'      => $errorCode,  // true or false
                'statusCode' => $statusCode, // http status code
                'message'    => $message,    // custom response message
                'data'       => $data        // response data
            ],
            $statusCode      // set status code on http response
        );
    }
}
