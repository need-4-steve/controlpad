<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface AnnouncementRepositoryContract
{
    public function getIndex($request);
    public function findByUrl($url);
}
