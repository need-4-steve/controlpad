<?php

use Guzzle\Http\Client;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    public function basicRequest($verb, $endpoint, $params = [])
    {
        return $this->json($verb, $endpoint, $params, ['APIKey' => env('TEST_CLIENT_API_KEY')]);
        // return $this->call($verb, $endpoint, $params, [], [], ['HTTP_APIKey' => env('TEST_CLIENT_API_KEY')]);
    }
}
