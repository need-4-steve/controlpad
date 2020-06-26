<?php

namespace App\Listeners;

use App\Events\AttachImageUrl;
use App\Services\Media\MediaService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AttachImageUrlListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->mediaService = new MediaService;
    }

    /**
     * Handle the event.
     *
     * @param  AttachImageUrl  $event
     * @return void
     */
    public function handle(AttachImageUrl $event)
    {
        $media = $this->mediaService->uploadUrl($event->imageUrl, $event->userId);
        $event->model->images()->attach($media->id);
    }
}
