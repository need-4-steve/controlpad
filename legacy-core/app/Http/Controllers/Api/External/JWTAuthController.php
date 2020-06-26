<?php

namespace App\Http\Controllers\Api\External;

use DB;
use App\Models\User;
use App\Services\Authentication\JWTAuthService;
use App\Repositories\Eloquent\UserRepository;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Illuminate\Hashing\BcryptHasher;
use CPCommon\Jwt\Jwt;
use App\Services\UserStatus\UserStatusService;

class JWTAuthController
{
    public function __construct(UserRepository $userRepo, BcryptHasher $hasher)
    {
        $this->hasher = $hasher;
        $this->userRepo = $userRepo;
        $this->userStatusService = new UserStatusService;
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $redirectUrl = $request->input('redirect');

        if (!empty($redirectUrl) && !JWTAuthService::isAuthorizedDomain($redirectUrl)) {
            abort(403);
        }

        $user = JWTAuthService::getUserBy('email', $credentials['email']);

        if (isset($user) && (
            !$this->userStatusService->checkPermission($user, 'login')
            || $user->disabled_at
        )) {
            return response()->json([
                "message" => "Your account has been disabled."
            ], 403);
        }

        if ($user == null || !$this->hasher->check($credentials['password'], $user->getAuthPassword())) {
            return response()->json([
                "message" => "Invalid credentials."
            ], 403);
        }

        unset($user['password']);

        $token = JWTAuthService::createToken($user);

        return $this->respond($token, $user);
    }

    public function assertAuthorizedDomain(Request $request)
    {
        $url = $request->input('url');
        logger($url);
        if (!JWTAuthService::isAuthorizedDomain($url)) {
            abort(403);
        }
        return response()->json([
            'domainIsAuthorized' => true
        ]);
    }

    public function logout(Request $request)
    {
        cache()->pull('cp_token_'.session()->get('cp_token'));
        // Delete all carts that are in session to release any reserved inventory.
        $cartTypes = ['cart', 'custom_corp', 'custom_personal'];
        DB::beginTransaction();
        foreach ($cartTypes as $cartType) {
            if (session()->has($cartType)) {
                session($cartType)->delete();
            }
        }
        DB::commit();
        session()->flush();
        auth()->logout();
        return response()->json('success');
    }

    public function refreshToken(Request $request)
    {
        $token = $this->getAuthToken();
        $claims = JWTAuthService::verify($token);
        $user = JWTAuthService::getUserBy('id', $claims['sub']);
        $actualUser = null;
        if (isset($claims['actualUserId'])) {
            $actualUser = JWTAuthService::getUserBy('id', $claims['actualUserId']);
        }
        $newToken = JWTAuthService::createToken($user, $actualUser);
        $newClaims = JWTAuthService::verify($newToken);
        return $this->respond($newToken, $user);
    }

    public function loginAs($userId)
    {
        if (!isset($userId)) {
            abort(422);
        }

        $token = $this->getAuthToken();
        if ($token == null) {
            abort(401);
        }

        $claims;
        try {
            $claims = JWTAuthService::verify();
        } catch (\Exception $e) {
            abort(401);
        }

        if (!in_array($claims['role'], ['Superadmin', 'Admin'])) {
            abort(403);
        }

        $impersonatedUser = JWTAuthService::getUserBy('id', $userId);

        $impersonationToken = JWTAuthService::createToken($impersonatedUser, $claims);

        return $this->respond($impersonationToken, $impersonatedUser);
    }

    public function revertLoginAs(Request $request)
    {
        $token = $this->getAuthToken();
        if ($token == null) {
            abort(401);
        }

        $claims;
        try {
            $claims = JWTAuthService::verify();
        } catch (\Exception $e) {
            abort(401);
        }

        if (!isset($claims['actualUserId'])) {
            abort(403);
        }

        $userId = $claims['actualUserId'];

        $user = JWTAuthService::getUserBy('id', $userId);

        $userToken = JWTAuthService::createToken($user);

        return $this->respond($userToken, $user);
    }


    private function respond($token, $user)
    {
        if (!$this->checkIfValidSubscription($user)) {
            return response()->json([
                'auth' => true,
                'activeSubscription' => false,
                'cp_token' => $token
            ]);
        }

        $termsAccepted = $this->userRepo->checkIfAcceptedTerms();
        session()->forget(['cart', 'custom_corp', 'custom_personal']);

        return response()->json([
            'auth' => true,
            'activeSubscription' => true,
            'termsAccepted' => $termsAccepted,
            'cp_token' => $token
        ]);
    }

    private function getAuthToken()
    {
        $request = request();
        $header = $request->headers->get('authorization');
        if ($header && preg_match('/bearer\s*(\S+)\b/i', $header, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function checkIfValidSubscription($user)
    {
        // subscription_ends_at is null when having one-time subscription, if a registration error happens a subscription might be null
        // for now we are just ignoring screwed up subscriptions
        return (empty($user->subscription_ends_at) || Carbon::parse($user->subscription_ends_at)->gt(Carbon::now()));
    }

    ////////////////////////////////////////////////////////////////////////
    // OLD STUFF

    public function loginAsOld($id = null)
    {
        if (auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            $user = User::find($id);

            if (! $user) {
                return redirect()->back()->with('message_danger', 'Could not find a user with ID ' . $id . '.');
            }

            if ($user->hasRole(['Superadmin', 'Admin'])) {
                return redirect()->back()->with('message_danger', 'Not authorized to login as this user.');
            }

            if ($user->hasRole(['Customer'])) {
                return redirect()->back()->with('message_danger', 'Cannot sign in as a customer.');
            }

            session()->put('logged_out_from_user_id', auth()->id());
            session()->forget(['cart', 'custom_corp', 'custom_personal']);
            auth()->logout();
            auth()->loginUsingId($id);
            return response()->json([
                'auth' => true,
                'activeSubscription' => true,
                'role' => $user->role->name,
                'jwtToken' => $this->getImpersonationToken($user)
            ], 200);
        } elseif (session()->get('logged_out_from_user_id') !== null) {
            $user = User::find($id);
            auth()->logout();
            auth()->loginUsingId($id);
            session()->forget('logged_out_from_user_id');
            session()->forget(['cart', 'custom_corp', 'custom_personal']);
            return response()->json([
                'auth' => true,
                'activeSubscription' => true,
                'role' => $user->role->name,
                'jwtToken' => $this->revertImpersonationToken($user)
            ], 200);
        }
    }

    // temporary
    private function getImpersonationToken($user)
    {
        $token = $this->getAuthToken();
        if ($token == null) {
            abort(401);
        }

        $claims;
        try {
            $claims = JWTAuthService::verify();
        } catch (\Exception $e) {
            abort(401);
        }

        if (!in_array($claims['role'], ['Superadmin', 'Admin'])) {
            abort(403);
        }

        return JWTAuthService::createToken($user, $claims);
    }

    // temporary
    public function revertImpersonationToken($user)
    {
        $token = $this->getAuthToken();
        if ($token == null) {
            abort(401);
        }

        $claims;
        try {
            $claims = JWTAuthService::verify();
        } catch (\Exception $e) {
            abort(401);
        }

        if (!isset($claims['actualUserId'])) {
            abort(403);
        }

        if ($user->id !== $claims['actualUserId']) {
            abort(403);
        }

        return JWTAuthService::createToken($user);
    }
}
