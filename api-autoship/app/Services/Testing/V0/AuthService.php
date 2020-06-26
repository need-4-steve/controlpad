<?php

namespace App\Services\Testing\V0;

use App\Services\Interfaces\V0\AuthServiceInterface;
use GuzzleHttp\Client;

class AuthService implements AuthServiceInterface
{
    public function getTenants()
    {
        return (object) [
            [
                'id' => 1,
                'name' => 'autoship',
                'read_host' => env('DB_READ_HOST'),
                'write_host' => env('DB_WRITE_HOST'),
                'db_name' => env('DB_DATABASE'),
                'status' => 'Paid',
                'created_at' => '2018-01-12 17:39:07',
                'updated_at' => '2018-03-27 20:22:07',
                'org_id' => '1234567890123456789012345',
                'domain' => ''
            ]
        ];
    }
}
