<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/api/v1/commission/update-user-ranks',
        '/api/v1/shippingInvoice/*',
        '/api/v1/cart',
        '/api/v1/categories/*',
        '/api/v1/inventory/*',
        '/api/v1/store',
        '/api/v1/store/*',
        '/cp/v1/*',
        '/api/v1/express/*',
        '/api/external/*'
    ];
}
