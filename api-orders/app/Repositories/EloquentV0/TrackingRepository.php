<?php

namespace App\Repositories\EloquentV0;

use App\Tracking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

class TrackingRepository
{
    public function create(array $params)
    {
        $created = Tracking::create($params);
        return $created;
    }

    public function delete($id)
    {
        $deleted = Tracking::where('id', $id)->delete();
        return $deleted;
    }
}
