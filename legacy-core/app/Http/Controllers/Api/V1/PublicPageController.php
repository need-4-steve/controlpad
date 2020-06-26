<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DocPage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\CustomPageRepository;

class PublicPageController extends Controller
{
    public function __construct(CustomPageRepository $docPageRepo)
    {
        $this->docPageRepo = $docPageRepo;
    }

    public function index()
    {
        return response()->json($this->docPageRepo->index(), HTTP_SUCCESS);
    }

    public function createUpdate(Request $request)
    {
        $this->validate($request, [ 'title' => 'required' ]);
        $data = request()->all();
        $page = $this->docPageRepo->createUpdate($data);
        return response()->json($page, 200);
    }
    public function createRevisedUpdate(Request $request)
    {
        $this->validate($request, [ 'title' => 'required' ]);
        $data = request()->all();
        $page = $this->docPageRepo->createRevisedUpdate($data);
        return response()->json($page, 200);
    }

    public function show($slug)
    {
        $page = $this->docPageRepo->show($slug);
        return $page;
    }
}
