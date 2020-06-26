<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\ServiceInterface;
use Tymon\JWTAuth\JWTAuth;
use App\Service;

class ServiceController extends Controller
{
    public function __construct(ServiceInterface $ServiceRepo, JWTAuth $jwt) {
        $this->ServiceRepo = $ServiceRepo;
        $this->jwt = $jwt;
    }


    public function index() {
        $user = $this->jwt->parseToken()->toUser();
        $admin = ($user->role == 'admin');
        return response()->json($this->ServiceRepo->index($admin));
    }

    public function show($id) {
        $user = $this->jwt->parseToken()->toUser();
        $admin = ($user->role == 'admin');
        return response()->json($this->ServiceRepo->show($id, $admin));
    }

    public function create(Request $request) {
        $this->validate($request, [
            'name' => 'required',
        ]);
        return response()->json($this->ServiceRepo->create(
                $request->input('name')
            ));
    }

    public function update($id, Request $request) {
        $this->validate($request, Service::$updateRules);
        $updated = $this->ServiceRepo->update($id, $request->only(Service::$updateFields));
        if (!$updated) {
            return response()->json(['error' => 'Could not find a service with an ID of ' . $id], 404);
        }
        return response()->json($this->ServiceRepo->show($id));
    }

    public function delete($id) {
        $deleted = $this->ServiceRepo->delete($id);
        if (!$deleted) {
            return response()->json(['error' => 'Could not find a service with an ID of ' . $id], 404);
        }
        return response()->json('Success.');
    }
}
