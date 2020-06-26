<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\UserInterface;
use Tymon\JWTAuth\JWTAuth;
use App\User;

class UserController extends Controller
{
    public function __construct(UserInterface $UserRepo, JWTAuth $jwt) {
        $this->UserRepo = $UserRepo;
        $this->jwt = $jwt;
    }

    public function index() {
        return $this->UserRepo->index();
    }

    public function show($id) {
        $user = $this->jwt->parseToken()->toUser();
        if ($user->role != 'admin' && $user->id != $id) {
            return response()->json('Unauthorized', 401);
        }
        return $this->UserRepo->show($id);
    }

    public function create(Request $request) {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|confirmed',
            'role' => 'required',
            'tenant_id' => 'required|integer'
        ]);
        try {
            $user = $this->UserRepo->create($request->input('email'),
                                        $request->input('password'),
                                        $request->input('role'),
                                        $request->input('tenant_id'));
            return $user;
        } catch (\Exception $e) {
            return response()->json('Invalid Input', 422);
        }

    }

    public function update($id, Request $request) {
        $user = $this->jwt->parseToken()->toUser();
        if ($user->role != 'admin' && $user->id != $id) {
            return response()->json('Unauthorized', 401);
        }
        $this->validate($request, User::$updateRules);
        $params = $request->only(User::$updateFields);
        if (isset($params['password'])) {
            $params['password'] = app('hash')->make($request->password);
        }
        return $this->UserRepo->update($id, $params);
    }

    public function delete($id) {
        $user = $this->jwt->parseToken()->toUser();
        if ($user->role != 'admin' && $user->id != $id) {
            return response()->json('Unauthorized', 401);
        }
        return $this->UserRepo->delete($id);
    }
}
