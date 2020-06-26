<?php

namespace App\Events;

class AttachImageUrl extends Event
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($model, $imageUrl, $userId)
    {
        $this->model = $model;
        $this->imageUrl = $imageUrl;
        $this->userId = $userId;
    }
}
