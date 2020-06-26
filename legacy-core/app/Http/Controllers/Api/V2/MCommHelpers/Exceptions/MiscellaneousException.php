<?php
namespace App\Http\Controllers\Api\V2\MCommHelpers\Exceptions;

class MiscellaneousException extends HttpException
{
    protected $codes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing'
    );
}