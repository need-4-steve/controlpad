<?php

namespace App\Listeners;

// use App\Events\EmailLogs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSending;
use DB;
use Carbon\Carbon;

class EmailLoggerListener
{

    /**
     * Handle the event.
     *
     * @param  EmailLogs  $event
     * @return void
     */
    public function handle(MessageSending $event)
    {
        $message = $event->message;
        DB::table('email_log')->insert([
            'from' => $this->formatAddressField($message, 'From'),
            'to' => $this->formatAddressField($message, 'To'),
            'subject' => $message->getSubject(),
            'body' => $message->getBody(),
            'headers' => (string)$message->getHeaders(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
    /**
    * Format address strings for sender, to, cc, bcc.
    *
    * @param $message
    * @param $field
    * @return null|string
    */
    public function formatAddressField($message, $field)
    {
        $headers = $message->getHeaders();
        if (!$headers->has($field)) {
            return null;
        }
        $mailboxes = $headers->get($field)->getFieldBodyModel();
        $strings = [];
        foreach ($mailboxes as $email => $name) {
            $mailboxStr = $email;
            $strings[] = $mailboxStr;
        }
        return implode(', ', $strings);
    }
}
