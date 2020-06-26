<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required',
        ]);

        try {
            if (! $token = $this->jwt->attempt($request->only('email', 'password'))) {
                return response()->json(['user_not_found'], 401);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent' => $e->getMessage()], 401);
        }
        $user = User::select('id', 'email', 'role')
            ->where('email', $request->input('email'))->first();
        return response()->json(compact('token', 'user'));
    }
}
