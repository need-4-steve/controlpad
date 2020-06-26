<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\CustomLinksRepository;
use App\Models\CustomLink;

class CustomLinksController extends Controller
{
    protected $customLinksRepo;

    public function __construct(CustomLinksRepository $customLinksRepo)
    {
        $this->customLinksRepo = $customLinksRepo;
    }

    public function getIndexByType(Request $request)
    {
        $this->validate($request, ['type' => 'required|string']);
        $type = $request->only('type');
        $links = $this->customLinksRepo->getIndexByType($type);
        return response()->json($links);
    }

    public function create(Request $request)
    {
        $rules = CustomLink::$rules;
        $fields = CustomLink::$updateFields;
        $this->validate($request, $rules);
        $inputs = $request->only($fields);
        // hard coded defaults for now.
        $inputs['type'] = 'corporate_rep_site_links';
        $inputs['user_id'] = 1;
        $created = $this->customLinksRepo->create($inputs);
        cache()->forget('custom_links');
        return response()->json($created);
    }

    public function delete($id)
    {
        $deleted = $this->customLinksRepo->delete($id);
        if (!$deleted) {
            return response()->json('The ID provided maybe invalid.', 404);
        }
        cache()->forget('custom_links');
        return response()->json('Success');
    }
}
