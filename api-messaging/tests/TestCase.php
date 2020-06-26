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
        // To Test using the API Key
        return $this->json($verb, $endpoint, $params, ['Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ0ZW5hbnRfaWQiOiIyIiwicm9sZSI6IlN1cGVyYWRtaW4iLCJzdWIiOjEwOSwiaXNzIjoiaHR0cDovL2NvcmUubG9jYWxob3N0L2FwaS9leHRlcm5hbC9hdXRoZW50aWNhdGUiLCJpYXQiOjE1MjEyMzU2MzYsImV4cCI6MTUyMzgyNzYzNiwibmJmIjoxNTIxMjM1NjM2LCJqdGkiOiJSb1V4Zm9VRmtmaFNxREI1In0.cRxKlWrhI7eDAN5czOpvgx8F1wdeb14xMot1vL8DeVI']);
    }
}
