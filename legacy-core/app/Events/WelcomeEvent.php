<?php

namespace App\Events;

use App\Models\User;
use App\Events\Event;
use App\Models\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class WelcomeEvent extends Event
{
    use SerializesModels;

    public $user;
    public $order;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, ?Order $order = null)
    {
        $this->user = $user;
        $this->order = $order;
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
