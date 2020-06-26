<?php

namespace App\Events;

use App\Models\RegistrationToken;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ApiRegistrationNew extends Event
{
    use SerializesModels;

    public $token;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(RegistrationToken $token)
    {
        $this->token = $token;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
