<?php

namespace App\Http\Controllers\V0;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\AnnouncementsRepositoryInterface;
use App\Announcement;

class AnnouncementsController extends Controller
{
    private $announcmentRepo;

    private $sortableColumns = [

    ];
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(AnnouncementsRepositoryInterface $announcmentRepo)
    {
        $this->announcmentRepo = $announcmentRepo;
    }

    public function index()
    {
        return $this->announcmentRepo->index();
    }

    public function show($id)
    {
        $announcement = $this->announcmentRepo->show($id);
        if ($announcement) {
            return $announcement;
        } else {
            return response()->json(['error' => 'Unable to find an announcement'], 404);
        }
    }

    public function create(Request $request)
    {
        $request->user->assertAnyRole(['Superadmin', 'Admin']);
        $rules = Announcement::$rules;
        $this->validate($request, $rules);
        return $this->announcmentRepo->create($request);
    }

    public function edit(Request $request, $id)
    {
        $request->user->assertAnyRole(['Superadmin', 'Admin']);
        $rules = Announcement::$updateRules;
        $this->validate($request, $rules);
        $announcement = $this->announcmentRepo->show($id);
        if ($announcement !== null) {
            return $this->announcmentRepo->edit($id, $request->only(Announcement::$updateFields));
        } else {
            return response()->json(['error' => 'Unable to find an announcement'], 404);
        }


    }

    public function delete(Request $request, $id)
    {
        $request->user->assertAnyRole(['Superadmin', 'Admin']);
        $announcement = $this->announcmentRepo->show($id);
        if ($announcement) {
            return $this->announcmentRepo->delete($id);
        } else {
            return response()->json(['error' => 'Unable to find an announcement'], 404);
        }

    }


}
