<?php namespace Test;

use Guzzle\Http\Client;
use Laravel\Lumen\Testing\DatabaseTransactions;

abstract class TestCase extends \Laravel\Lumen\Testing\TestCase
{
    use DatabaseTransactions;

    public function createApplication()
    {
        return require __DIR__.'/testapp.php';
    }

    public function basicRequest($verb, $endpoint, $params = null, $role = 'Superadmin', $userPid = '1', $userId = 1)
    {
        if ($params == null) {
            $params = [];
        }
        $headers = [
            'APIKey' => $role,
            'UserId' => $userId,
            'UserPid' => $userPid
        ];

        return $this->json($verb, $endpoint, $params, $headers);
    }
}
