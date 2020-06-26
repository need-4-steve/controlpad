<?php

namespace App\Services\Authentication;

use App\Models\User;
use App\Repositories\Eloquent\UserStatusRepository;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\SellerType;
use CPCommon\Jwt\Jwt;
use DB;
use Carbon\Carbon;
use App\Models\CustomPage;

class JWTAuthService
{
    private static $header;
    private static $token;
    private static $claims;

    public static function verify($token = false)
    {
        if ($token == false) {
            $header = request()->headers->get('authorization');
            if ($header && preg_match('/bearer\s*(\S+)\b/i', $header, $matches)) {
                $token = $matches[1];
            }
        }
        if (self::$token != $token || self::$token == null) {
            self::$token = $token;
            self::$claims = Jwt::verify(self::$token, env('JWT_SECRET'));
        }
        return self::$claims;
    }

    public static function hasRole($roles)
    {
        $user = auth()->user();
        if (isset($user)) {
            return $user->hasRole($roles);
        }
        $claims = self::verify();
        return in_array($claims['role'], $roles);
    }

    public static function createToken($user, $actualUser = null)
    {
        $tokenUser = clone $user;
        if (!isset($tokenUser->role_name)
            || !isset($tokenUser->seller_type)
            || !isset($tokenUser->subscription_ends_at)
            || !isset($tokenUser->pid)
        ) {
            $query = User::select('users.pid')->where('users.id', $tokenUser->id);
            if (!isset($tokenUser->status)) {
                $query->addSelect('users.status');
            }
            if (!isset($tokenUser->role_name)) {
                $query
                    ->join('roles', 'users.role_id', '=', 'roles.id')
                    ->addSelect('roles.name as role_name');
            }
            if (!isset($tokenUser->seller_type)) {
                $query
                    ->leftJoin('seller_types', 'users.seller_type_id', '=', 'seller_types.id')
                    ->addSelect('seller_types.name as seller_type');
            }
            if (!isset($tokenUser->subscription_ends_at)) {
                $query
                    ->leftJoin('subscription_user', 'users.id', '=', 'subscription_user.user_id')
                    ->addSelect('subscription_user.ends_at as subscription_ends_at');
            }
            $extendedUser = $query->first();
            $tokenUser->pid = $extendedUser->pid;
            if (!isset($tokenUser->status)) {
                $tokenUser->status = $extendedUser->status;
            }
            if (!isset($tokenUser->role_name)) {
                $tokenUser->role_name = $extendedUser->role_name;
            }
            if (!isset($tokenUser->seller_type)) {
                $tokenUser->seller_type = $extendedUser->seller_type;
            }
            if (!isset($tokenUser->subscription_ends_at)) {
                $tokenUser->subscription_ends_at = $extendedUser->subscription_ends_at;
            }
        }

        $userStatus = UserStatusRepository::getUserStatus();

        if (count($userStatus) <= 0) {
            abort(500);
        }

        $claims = [
            'exp' => time() + config('session.lifetime') * 60,
            'iat' => time(),
            'iss' => 'api.controlpad.com',
            'aud' => 'api.controlpad.com',
            'sub' => $tokenUser->id,
            'userPid' => $tokenUser->pid,
            'name' => $tokenUser->first_name,
            'fullName' => $tokenUser->first_name . ' ' . $tokenUser->last_name,
            'repSubdomain' => $tokenUser->public_id,
            'role' => $tokenUser->role_name,
            'sellerType' => $tokenUser->seller_type,
            'perm' => [
                'core:buy' => $userStatus[$tokenUser->status]['buy'],
                'core:sell' => $userStatus[$tokenUser->status]['sell']
            ],
            'acceptedTerms' => JWTAuthService::checkIfAcceptedTerms($user),
            'activeSubscription' => (empty($tokenUser->subscription_ends_at) || $tokenUser->subscription_ends_at > Carbon::now()->subDays(app('globalSettings')->getGlobal('sub_grace_period', 'value'))),
            'orgId' => env('ORG_ID', null),
            'tenant_id' => env('TENANT_ID') // deprecated
        ];

        if (isset($actualUser)) {
            $actualUserId = isset($actualUser['id']) ? $actualUser['id'] : $actualUser['sub'];
            $actualUserRole = isset($actualUser['role_name']) ? $actualUser->role_name : $actualUser['role'];
            if (in_array($actualUserRole, ['Superadmin', 'Admin'])) {
                $claims['actualUserId'] = $actualUserId;
            }
        }

        $token = Jwt::sign($claims, env('JWT_SECRET'));

        ///////////////////////////////////////
        // SESSION STUFF - WILL GO AWAY IN SPA
        if (!isset($user->remember_token)) {
            $user->remember_token = null;
        }
        auth()->login($user, true);
        if (auth()->id() != $user->id) {
            abort(403);
        }
        session()->put('cp_token', $token);
        cache()->put('cp_token_'.$token, $user, config('session.lifetime'));
        // END OF SESSION STUFF
        ///////////////////////////////////////

        return $token;
    }

    public static function getApiJWT()
    {
        $claims = [
            'exp' => time() + 300,
            'iat' => time(),
            'iss' => 'api.controlpad.com',
            'aud' => 'api.controlpad.com',
            'sub' => config('site.apex_user_id'),
            'role' => 'Admin',
            'orgId' => env('ORG_ID', null),
            'userPid' => null
        ];
         $token = Jwt::sign($claims, env('JWT_SECRET'));
         return $token;
    }

    public static function getUserBy($field, $value)
    {
        return User::select(
            [
            'users.status',
            'users.password',
            'users.first_name',
            'users.last_name',
            'users.pid as pid',
            'users.id as id',
            'users.email',
            'users.role_id',
            'users.remember_token',
            'users.public_id',
            'users.disabled_at',
            'roles.name as role_name',
            'seller_types.name as seller_type',
            'subscription_user.ends_at as subscription_ends_at'
            ]
        )
        ->where('users.' . $field, $value)
        ->join('roles', 'users.role_id', '=', 'roles.id')
        ->leftJoin('seller_types', 'users.seller_type_id', '=', 'seller_types.id')
        ->leftJoin('subscription_user', 'users.id', '=', 'subscription_user.user_id')
        ->first();
    }

    public static function isAuthorizedDomain($url)
    {
        $domain = parse_url($url, PHP_URL_HOST);
        $domains = \DB::table('authorized_domains')->select('domain')->get();
        return $domains->contains("domain", $domain);
    }

    ////////////////////////////////////////////////////////////////////////
    // OLD STUFF

    public static function tokenCheck($token = false)
    {
        if ($token === false) {
            $token = session()->get('cp_token');
        }
        $user = cache()->get('cp_token_'.$token);
        if (!$user) {
            auth()->logout();
            return false;
        }
        if (auth()->check() && $user->id != auth()->user()->id && !session()->has('logged_out_from_user_id')) {
            auth()->login($user);
        }
        return $user;
    }

    public static function check()
    {
        return (JWTAuthService::tokenCheck() && auth()->check());
    }

    public static function domainCheck($domain)
    {
        $domain = parse_url($domain, PHP_URL_HOST);
        $domains = cache()->get('authorized_domains');
        if (!$domains) {
            $domains = DB::table('authorized_domains')->select('domain')->get();
            cache()->forever('authorized_domains', $domains);
        }
        return $domains->contains("domain", $domain);
    }

    public static function logInUser($user)
    {
        $token = self::createToken($user);
        session()->put('cp_token', $token);
        cache()->put('cp_token_'.$token, $user, config('session.lifetime'));
        if (!isset($user->remember_token)) {
            $user->remember_token = null;
        }
        $attempt = auth()->login($user, true);
    }

    public static function checkIfAcceptedTerms($user)
    {
        $terms = CustomPage::where('slug', 'rep-terms')->first();
        $userAcceptance = null;

        if (!$terms) {
            return true;
        }

        $userAcceptance = $user->termsAccepted;
        if (!$userAcceptance) {
            return false;
        }

        // we've accepted since it changed
        if ((isset($userAcceptance->updated_at) && isset($terms->revised_at)) && $userAcceptance->updated_at > $terms->revised_at) {
            return true;
        }

        // haven't accepted since change
        return false;
    }
}
