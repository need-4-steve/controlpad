<?php

use App\Announcement;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\DbUnit\TestCaseTrait;

class AnnouncementsTest extends TestCase
{
    public function testAnnouncementIndex()
    {
        factory(Announcement::class, 3)->create();
        $response = $this->basicRequest('GET', 'api/v0/announcements');
        $response->assertResponseStatus(200);
        $response->seeJson([
            "id" => 1,
        ]);
    }

    public function testAnnouncementShow()
    {
        factory(Announcement::class, 3)->create();
        $response = $this->basicRequest('GET', 'api/v0/announcements/1');
        $response->assertResponseStatus(200);
        $response->seeJson([
            "id" => 1
        ]);
    }

    public function testAnnouncementCreate()
    {
        $params = [
           'title' => 'Title 5',
           'description' => 'this is a description',
           'body' => 'this is the body'
        ];
        $response = $this->basicRequest('POST', 'api/v0/announcements', $params);
        $response->assertResponseStatus(200);
        $response->seeJson([
            'title' => 'Title 5',
            'description' => 'this is a description',
            'body' => 'this is the body'
        ]);
    }

    public function testAnnouncementUpdate()
    {
        factory(Announcement::class, 3)->create();
        $params = [
            'title' => 'Title 5',
        ];
        $response = $this->basicRequest('PATCH', 'api/v0/announcements/1', $params);
        $response->assertResponseStatus(200);
        $this->assertTrue(true);
    }

    public function testAnnouncementDelete()
    {
        factory(Announcement::class, 3)->create();
        $response = $this->basicRequest('DELETE', 'api/v0/announcements/1');
        $response->assertResponseStatus(200);
        $this->assertTrue(true);
    }
}
