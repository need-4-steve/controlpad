<?php namespace App\Http\Controllers;

use App\Repositories\Eloquent\UserRepository;
use App\Services\Oauth\FacebookOauthService;
use App\Services\Authentication\JWTAuthService;

class UserSiteController extends Controller
{
    public function __construct(UserRepository $userRepo, FacebookOauthService $facebookOauthService)
    {
        $this->userRepo = $userRepo;
        $this->facebookOauthService = $facebookOauthService;
    }

    public function repStore($subdomain)
    {
        // makes backoffice subdomain dynamic depending what is in .env
        if ($subdomain === strstr(env('APP_URL'), '.', true)) {
            return $this->loginToMyOffice($subdomain);
        }
        $user = $this->userRepo->findByPublicId($subdomain, ['profileImage', 'businessAddress']);
        if (isset($user) && $user->role_id == 5) {
            session()->put('store_owner', $user);
            return redirect('store#');
        } elseif (isset($user)) {
            return redirect('store');
        }
        return redirect('//' . config('app.url'));
    }
    public function loginToMyOffice($subdomain)
    {
        //don't need a client to login twice so if they are logged in it will take them to their default area
        if (JWTAuthService::check()) {
            if (request()->has('cp_redirect')) {
                $redirect = urldecode(request()->input('cp_redirect'));
                if (JWTAuthService::domainCheck($redirect)) {
                    $token = session()->get('cp_token');
                    $redirect = strpos($redirect, '?') ? $redirect.'&cp_token='.$token : $redirect.'?cp_token='.$token;
                    return redirect($redirect);
                }
            }
            return redirect('/dashboard');
        }

        // Facebook Oauth will not work without this, because reasons
        $void = session()->all();

        $facebookOauthURL = $this->facebookOauthService->generateLoginUrl();

        return view('sessions.create')->with(['facebookOauthURL' => $facebookOauthURL]);
    }
}
