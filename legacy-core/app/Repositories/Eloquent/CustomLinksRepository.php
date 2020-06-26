<?php

namespace App\Repositories\Eloquent;

use App\Models\CustomLink;
use App\Repositories\Contracts\CustomLinksRepositoryContract;

class CustomLinksRepository implements CustomLinksRepositoryContract
{
    public function getIndexByType($type)
    {
        return CustomLink::where('type', $type)->get();
    }

    public function create($request)
    {
        return CustomLink::create($request);
    }

    public function delete($id)
    {
        return CustomLink::destroy($id);
    }

    public function show($id)
    {
        return CustomLink::where('id', $id)->first();
    }
}
