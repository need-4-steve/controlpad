<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustomEmail extends Mailable
{
    use Queueable, SerializesModels;
    protected $request;

    /**
     * Create a new message for the user to update card info.
     *
     * @return void
     */
    public function __construct($request = null)
    {
        $this->request = $request;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->request['from'])
                          ->subject($this->request['subject'])
                          ->view('standard')
                          ->with(['body' => $this->request['body']]);
    }
}
