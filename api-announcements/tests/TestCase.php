<?php

use Guzzle\Http\Client;
use Laravel\Lumen\Testing\DatabaseTransactions;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
  use DatabaseTransactions;
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/testapp.php';
    }

    public function basicRequest($verb, $endpoint, $params = [])
    {
        return $this->json($verb, $endpoint, $params, ['APIKey' => 'Superadmin']);
    }
}
