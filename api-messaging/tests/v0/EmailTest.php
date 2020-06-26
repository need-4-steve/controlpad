<?php

use App\Email;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\DbUnit\TestCaseTrait;

class EmailTest extends TestCase
{

    // use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testEmailIndex()
    {
        $response = $this->basicRequest('GET', '/emails');
        $response->assertResponseStatus(200);
        $response->seeJson([
          "id" => 1,
         "title" => "new_password",
        ]);
    }

    public function testEmailShow()
    {
        $response = $this->basicRequest('GET', '/emails/new_password');
        $response->assertResponseStatus(200);
        $response->seeJson([
          "title" => "new_password"
        ]);
    }

    public function testEmailUpdate()
    {
        $params = ['subject' => 'Test Sponsor Notice',
                    'send_email' => 1];
        $response = $this->basicRequest('PATCH', '/emails/sponsor_notice', $params);
        $response->assertResponseStatus(200);
        $response->seeJson([
          "title" => "sponsor_notice",
          'subject' => "Test Sponsor Notice"
        ]);
         $this->seeInDatabase('emails', ['subject' => 'Test Sponsor Notice']);
    }

    public function testVariablesList()
    {
        $response = $this->basicRequest('GET', '/variables/new_password');
        $response->assertResponseStatus(200);
    }

    public function testExampleEmail()
    {
        $params = ["subject" => "New Password", "body" => "This is a test ['first_name']"];
        $response = $this->basicRequest('GET', 'example/new_password', $params);
        $response->assertResponseStatus(200);
    }

    public function testEmailJobs()
    {
      $this->expectsJobs('App\Jobs\SendMail');
        $params = ["type" => "preset",
                    "to" => "person3@mail.com",
                    "from" => "company@mail.com",
                    "subject" => "Sponsor Notice",
                    "body" => "<p>[sponsor_first_name]</p>
                              <p>Congratulations a new user has registered with [company_name] and you are their sponsor!</p>
                              <p>New Rep's contact info</p>
                              <p>Name: [first_name] [last_name]</p><p>Phone: [phone]</p><p>Email: [email]</p><p><br></p>
                              <p><br></p><p><br></p><p><br></p><p style='text-align: center;'>[back_office_logo]</p><p style='text-align: center;'>This is an important notification from [company_name]</p><p style='text-align: center;'>[company_name] [company_address]</p>",
                    "user" => ["first_name" => "Bob",
                              "last_name" => "Smith",
                              "email" => "user@mail.com",
                              "phone" => "1234567890"],
                    "sponsor" => ["first_name" => "John",
                                  "last_name" => "Jones"],
                    "title" => "sponsor_notice",
                    "send_email" => 1
                  ];
        $response = $this->basicRequest('GET', 'send/', $params);
    }
}
