<?php

namespace App\Services\LiveVideo;

interface LiveVideoContract
{
    public function getAllVideos();
    public function getLiveVideos();
    public function getVideo(int $video, string $service);
    public function create(array $inputs);
}
