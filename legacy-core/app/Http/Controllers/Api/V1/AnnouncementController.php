<?php namespace App\Http\Controllers\Api\V1;

use Auth;
use Response;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\AnnouncementRepository;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    protected $announcementRepo;

    public function __construct(AnnouncementRepository $announcementRepo)
    {
        $this->announcementRepo = $announcementRepo;
    }
    /**
     * Get all announcements from storage.
     * @param Illuminate\Http\Request
     * @return JSON
     */
    public function getIndex(Request $request)
    {
        $rules = [
            'column' => 'required',
            'order' => 'required',
        ];
        $this->validate($request, $rules);
        return response($this->announcementRepo->getIndex($request->all()), 200);
    }
    /**
     * Update announcement in storage.
     * @param Illuminate\Http\Request
     * @param Integer
     * @return JSON
     */
    public function update(Request $request, $id)
    {
        $announcement = $this->announcementRepo->find($id);
        if (!$announcement) {
            return response()->json(['Record not found.'], 404);
        }
        $this->validate($request, $this->announcementRepo->getRules());
        return response()->json($announcement->update($request->all()), 200);
    }
    /**
     * Store a newly created announcement in storage.
     * @param Illuminate\Http\Request
     * @return JSON
     */
    public function create(Request $request)
    {
        $this->validate($request, $this->announcementRepo->getRules());
        return response()->json($this->announcementRepo->create($request->all()), 200);
    }
    /**
     * Store a newly created announcement in storage.
     * @param Illuminate\Http\Request
     * @return JSON
     */
    public function delete($id)
    {
        return response()->json($this->announcementRepo->delete($id), 200);
    }
}
