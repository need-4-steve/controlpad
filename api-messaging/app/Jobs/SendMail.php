<?php

namespace App\Jobs;

use App\Mail\CustomEmail;
use App\EmailLog;
use Illuminate\Support\Facades\Mail;

class SendMail extends Job
{
  protected $request;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request = null)
    {
      $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      try {
          if ($this->request['send_email']) {
            Mail::to($this->request['to'])->queue(new CustomEmail($this->request));
            EmailLog::create([
                'to' => $this->request['to'],
                'from' => $this->request['from'],
                'subject' => $this->request['subject'],
                'body' => $this->request['body'],
                'org_id' => $this->request['orgId'],
                'success' => true
            ]);
          }
      } catch (\Exception $e) {
        EmailLog::create([
            'to' => $this->request['to'],
            'from' => $this->request['from'],
            'subject' => $this->request['subject'],
            'body' => $this->request['body'],
            'org_id' => $this->request['orgId'],
            'success' => false,
            'fail_reason' => $e
        ]);
      }
    }
}
