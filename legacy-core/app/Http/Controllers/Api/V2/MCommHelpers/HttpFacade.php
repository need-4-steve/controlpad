<?php
namespace App\Http\Controllers\Api\V2\MCommHelpers;

use Illuminate\Support\Facades\Facade;

class Http extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'http';
    }
}