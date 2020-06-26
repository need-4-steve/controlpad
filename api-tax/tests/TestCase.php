<?php

use Guzzle\Http\Client;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
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

    public function setUp()
    {
        parent::setUp();
    }

    public function basicRequest($method, $path, $params = [])
    {
        return $this->json($method, $path, $params, ['APIKey' => 'Superadmin']);
    }
}
