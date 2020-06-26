<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    // public function testExample()
    // {
    //     $this->get('/');
    //
    //     $this->assertEquals(
    //         $this->app->version(), $this->response->getContent()
    //     );
    // }

    public function testKey()
    {
        $this->get('/api/v0/testkey', ['HTTP_APIKey', '$2y$10$eKr5N/6JB6h9lYctTnhhBOVBUeE8o2/WXLLNdp6Fd/HnUZi6wlzwe'])
            ->seeJsonEquals([
                'message' => 'Hello API User'
            ]);
    }
}
