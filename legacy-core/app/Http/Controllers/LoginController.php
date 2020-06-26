<?php

namespace App\Http\Controllers;

use App\Exceptions\OauthUserLoginException;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\OauthTokenRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Services\Oauth\FacebookOauthService;
use App\Services\Authentication\JWTAuthService;
use App;
use DB;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Facebook\Facebook;
use Facebook\Authentication\AccessToken;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /* @var \App\Services\Oauth\FacebookOauthService */
    protected $facebookOauthService;

    /* @var \App\Repositories\Eloquent\OauthTokenRepository */
    protected $oauthRepo;

    /* @var \App\Repositories\Eloquent\UserRepository */
    protected $userRepo;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        FacebookOauthService $facebookOauthService,
        OauthTokenRepository $oauthRepo,
        UserRepository $userRepo
    ) {
        $this->facebookOauthService = $facebookOauthService;
        $this->oauthRepo = $oauthRepo;
        $this->userRepo = $userRepo;
        $this->settingsService = App::make('globalSettings');
    }

    /**
     * Show the login page.
     *
     * @method showLoginForm
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
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

    public function logout()
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
        return redirect('/');
    }

    public function handleOauthCallback($driver)
    {
        $oauthService = null;
        $driver = strtolower($driver);
        $redirectUrl = '/dashboard';
        $redirectMessage = 'Logged in via ' . $driver . '!';
        if (auth()->check()) {
            $redirectUrl = '/my-settings';
            $redirectMessage = 'Successfully linked your ' . ucfirst($driver) . ' account!';
        }

        if (! $this->settingsService->getGlobal('rep_' . $driver . '_login', 'show')) {
            return redirect('/login')->with('message_danger', "$driver not enabled.");
        }

        switch ($driver) {
            case 'facebook':
                $oauthUser = $this->setFacebookTokenUser();
                if ($oauthUser instanceof \Illuminate\Http\RedirectResponse) {
                    return $oauthUser;
                }
                $oauthService = $this->facebookOauthService;
                break;
            default:
                return redirect()->back()->with(['error_message' => 'We do not support using ' . ucfirst($driver) . '.']);
                break;
        }

        if (is_null($oauthUser)) {
            return redirect()->back()->with(['error_message' => 'We were unable to authenticate you with ' . ucfirst($driver) . '.']);
        }

        if (isset($oauthUser->access_token) and ! isset($oauthUser->service_email)) {
            session()->put('facebook.oauth.token', $oauthUser);
            $oauthUser = $oauthService->getUser($oauthUser);
        }

        $response = ['error' => 'Unable to complete facebook oauth login.', 'redirect_url' => '/login'];
        try {
            $response = $oauthService->loginWithUser($oauthUser);
        } catch (OauthUserLoginException $e) {
            \Log::error($e->getMessage());
        }

        // if we have an error, report the error
        if (is_array($response) && isset($response['error'])) {
            return redirect($response['redirect_url'])->with('message_danger', $response['error']);
        }

        if (session()->has('oauth_redirect_url')) {
            $redirectUrl = session('oauth_redirect_url');
        }


        return redirect($redirectUrl)->with('message', $redirectMessage);
    }

    /**
     * Fetch and set the new Facebook Oauth token.
     *
     * @method setFacebookToken
     * @return bool|object
     */
    protected function setFacebookTokenUser()
    {
        $state = request()->input('state', '');

        $token = $this->facebookOauthService->getToken($state);
        $socialiteUser = $this->facebookOauthService->getUser($token);
        if (isset($socialiteUser->error) and $socialiteUser->error) {
            return redirect()->back()->with(['error_message' => $socialiteUser->data['description']]);
        }

        $user = $this->userRepo->findByEmail($socialiteUser->service_email);
        if (! $user) {
            $userToken = $this->oauthRepo->findByServiceEmail($socialiteUser->service_email, 'facebook');
            if (! $userToken) {
                return redirect('/join')->with('message_danger', 'Please fill in some information before continuing.');
            }
            $user = $userToken->user;
        }

        $socialiteUser->email = $user->email;
        $socialiteUser->user_id = $user->id;
        $socialiteUser->driver_id = 'facebook';
        $token = $this->facebookOauthService->saveToken($socialiteUser);
        session(['oauth.driver' => 'facebook']);

        return $socialiteUser;
    }
}
