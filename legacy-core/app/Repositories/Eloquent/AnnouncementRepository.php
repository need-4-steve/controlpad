<?php

namespace App\Repositories\Eloquent;

use Hash;
use Carbon\Carbon;
use App\Models\Announcement;
use App\Repositories\Contracts\AnnouncementRepositoryContract;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;

class AnnouncementRepository implements AnnouncementRepositoryContract
{
    use CommonCrudTrait;

    public function getIndex($request)
    {
        if (!isset($request['search_term'])) {
            $request['search_term'] = '';
        }
        if (!isset($request['per_page'])) {
            $request['per_page'] = 15;
        }

        $announcements = Announcement::where('title', 'LIKE', "%" . $request['search_term'] . "%")
            ->orderBy($request['column'], $request['order']);

        if (isset($request['start_date']) && isset($request['end_date'])) {
            $announcements = $announcements->where('created_at', '>=', $request['start_date'])
                                ->where('created_at', '<=', Carbon::parse($request['end_date'])->endOfDay());
        }

        return $announcements->paginate($request['per_page']);
    }

    public function findByUrl($url)
    {
        return Announcement::where('url', $url)->first();
    }

    public function getRules()
    {
        return Announcement::$rules;
    }

    public function create($request)
    {
        return Announcement::create($request);
    }

    public function deleteByID($id)
    {
        return Announcement::destroy($id);
    }
}
