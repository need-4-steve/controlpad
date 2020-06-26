<?php

namespace CPCommon\Events;

use Illuminate\Queue\SerializesModels;

class GenericEvent
{
    use SerializesModels;

    public $event;
    public $data;
    public $orgId;
    public $v;

    public function __construct($event, $data, $orgId, $v)
    {
        $this->event = $event;
        $this->data = $data;
        $this->orgId = $orgId;
        $this->v = $v;
    }

    public static function fromObject($message)
    {
        return new GenericEvent(
            $message->event,
            $message->data,
            $message->orgId,
            $message->v
        );
    }
}
