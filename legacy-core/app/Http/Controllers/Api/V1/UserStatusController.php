<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserStatus;
use App\Repositories\Eloquent\UserStatusRepository;
use Validator;

class UserStatusController extends Controller
{
    protected $userStatusRepo;
    
    public function __construct(
        UserStatusRepository $userStatusRepo
    ) {
        $this->userStatusRepo = $userStatusRepo;
    }

    public function index()
    {
        $userStatus = $this->userStatusRepo->index();
        return response()->json($userStatus, HTTP_SUCCESS);
    }

    public function create()
    {
        $request = request()->all();
        $request['name'] = isset($request['name']) && is_string($request['name']) ? strtolower($request['name']) : null;
        $validator = Validator::make($request, UserStatus::$rules);
        if ($validator->fails()) {
            return response()->json($validator->messages(), HTTP_BAD_REQUEST);
        }
        $userStatus = $this->userStatusRepo->create($request);
        return response()->json($userStatus, HTTP_SUCCESS);
    }

    public function update($id)
    {
        $request = request()->all();
        $request['name'] = isset($request['name']) && is_string($request['name']) ? strtolower($request['name']) : null;
        $rules = UserStatus::$rules;
        $rules['name'] .= ','.$id;
        $validator = Validator::make($request, $rules);
        if ($validator->fails()) {
            return response()->json($validator->messages(), HTTP_BAD_REQUEST);
        }
        $status = $this->userStatusRepo->find($id);
        if ($request['name'] !== $status['name'] && $status['default'] != false) {
            return response()->json('Cannot change name on default statuses', HTTP_BAD_REQUEST);
        }
        $users = $this->userStatusRepo->userStatusCheck($status['name']);
        if ($status->name != $request['name'] && count($this->userStatusRepo->userStatusCheck($status->name)) > 0) {
            return response()->json('Cannot change name on status in use', HTTP_BAD_REQUEST);
        }
        $userStatus = $this->userStatusRepo->update($request, $id);
        $this->userStatusRepo->index(); // this is to immediatly cache it after update
        return response()->json($userStatus, HTTP_SUCCESS);
    }

    public function delete($id)
    {
        if ($id === 1) {
            return response()->json('Cannot delete active status', HTTP_BAD_REQUEST);
        }
        $status = $this->userStatusRepo->find($id);
        $users = $this->userStatusRepo->userStatusCheck($status->name);
        if (count($users) > 0) {
            return response()->json('Cannot delete a status that is in use', HTTP_BAD_REQUEST);
        }
        $this->userStatusRepo->delete($id);
        return response()->json('Successfully deleted', HTTP_SUCCESS);
    }

    public function updateUserStatus()
    {
        $request = request()->all();
        $userStatus = $this->userStatusRepo->updateUserStatus($request['users'], $request['status']);
        return response()->json($userStatus, HTTP_SUCCESS);
    }
}
