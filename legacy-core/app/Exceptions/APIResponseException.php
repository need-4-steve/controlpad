<?php

namespace App\Exceptions;

use Exception;

/**
*  Handler.php will return this as a response
*  Use as throw new APIResponseException to allow controllers and services to
*  seperate concerns of response structure and flow from errors.
*  This will allow functions to return a set structure of data without having to always pass results.
*  Be mindful that this exception can be caught by try/catch blocks that look for Exception
*/
class APIResponseException extends Exception
{

    protected $httpCode;
    protected $errorCode;
    protected $message;
    protected $data;

    public function __construct($httpCode, $errorCode, $message, $data)
    {
        $this->httpCode = $httpCode;
        $this->errorCode = $errorCode;
        $this->message = $message;
        $this->data = $data;
    }

    public function toResponse()
    {
        return response()->json([
            'errorCode' => $this->errorCode,
            'message' => $this->message,
            'data' => $this->data
        ], $this->httpCode);
    }
}
