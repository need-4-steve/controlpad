<?php

namespace App\Repositories\EloquentV0;

use App\Repositories\Interfaces\AnnouncementsRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use App\Announcement;

class AnnouncementsRepository implements AnnouncementsRepositoryInterface
{
    public function index()
    {
        return Announcement::all();
    }

    public function show($id)
    {
        return Announcement::where('id', $id)->first();
    }

    public function create($request)
    {
        return Announcement::create([
            'body' => $request->has('body')? $request->body : " ",
            'description' => $request->has('description')? $request->description : " ",
            'title' => $request->title,
        ]);
    }

    public function edit($id, $request)
    {
        return Announcement::where('id', $id)->update($request);
    }

    public function delete($id)
    {
        return Announcement::where('id', $id)->delete();
    }

}
