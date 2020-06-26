<?php

namespace App\Services\Oauth;

use App\Data\FacebookPersistantDataInterface;
use App\Exceptions\OauthUserLoginException;
use App\Repositories\Eloquent\OauthTokenRepository;
use App\Repositories\Eloquent\UserRepository;

use Carbon\Carbon;

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

use stdClass;

class FacebookOauthService implements OauthServiceInferface
{
    /* @var \App\Repositories\Eloquent\OauthTokenRepository */
    protected $oauthTokenRepo;

    /* @var \App\Repositories\Eloquent\UserRepository */
    protected $userRepo;

    /* @var \Facebook\Facebook */
    protected $facebook;

    /* @var \Facebook\Facebook::getRedirectLoginHelper */
    protected $helper;

    /* @var int $driverId \App\Models\OauthDriver ID property */
    protected $driverId;

    public function __construct(OauthTokenRepository $oauthTokenRepo, UserRepository $userRepo)
    {
        $this->oauthTokenRepo = $oauthTokenRepo;
        $this->userRepo = $userRepo;

        $facebook = new Facebook([
            'app_id'                  => env('FACEBOOK_CLIENT_ID'),
            'app_secret'              => env('FACEBOOK_CLIENT_SECRET'),
            'default_graph_version'   => env('FACEBOOK_API_VERSION'),
            'persistent_data_handler' => new FacebookPersistantDataInterface()
        ]);

        $this->facebook = $facebook;
        $this->helper = $facebook->getRedirectLoginHelper();

        $this->setDriverId();
    }

    /**
     * Generate the URL for a "Login Using Facebook" link.
     *
     * @method generateLoginUrl
     * @return string       URL safe address for href property
     */
    public function generateLoginUrl($raw = false)
    {
        $helper = $this->helper;

        $permissions = [
            'email',
            'public_profile',
            'publish_actions',
            'user_actions.video',
            'user_friends',
            'user_posts',
            'user_videos'
        ];

        $facebookAuthUrl = $helper->getLoginUrl(url('/oauth/callback/facebook'), $permissions);
        if ($raw) {
            return $facebookAuthUrl;
        }
        return htmlspecialchars($facebookAuthUrl);
    }

    /**
     * Return the local copy of a token.
     *
     * @method getLocalToken
     * @return bool|\App\Models\OauthToken
     */
    public function getLocalToken()
    {
        return \App\Models\OauthToken::where([
            'driver_id' => $this->driverId,
            'user_id'   => auth()->id()
        ])->first();
    }

    /**
     * Does the heavy lifting of getting the token
     *
     * @param  string $state The 'state' property from Facebook
     * @return object
     */
    public function getToken($state = 'active')
    {
        $helper = $this->helper;
        $helper->getPersistentDataHandler()->set('state', $state);

        try {
            $accessToken = $this->getLocalToken();
            if (! $accessToken) {
                $accessToken = $helper->getAccessToken();
            }
        } catch (FacebookResponseException $e) {
            // When Graph returns an error
            return $this->buildResponse('Graph token', $e->getMessage());
        } catch (FacebookSDKException $e) {
            // When validation fails or other local issues
            return $this->buildResponse('Facebook SDK token', $e->getMessage());
        } catch (\Exception $e) {
            return $this->buildResponse('Generic token', $e->getMessage());
        }

        if (is_null($accessToken)) {
            return false;
        }

        if (is_object($accessToken) && isset($accessToken->access_token)) {
            $accessToken = $accessToken->access_token;
        }
        // setting it to the "short token" for next request
        $this->facebook->setDefaultAccessToken($accessToken);

        $urlParams = 'grant_type=fb_exchange_token';
        $urlParams .= '&client_id='.env('FACEBOOK_CLIENT_ID');
        $urlParams .= '&client_secret='.env('FACEBOOK_CLIENT_SECRET');
        $urlParams .= '&fb_exchange_token='.$accessToken;

        try {
            $longToken = $this->facebook->get('/oauth/access_token?'.$urlParams);
        } catch (FacebookResponseException $e) {
            // When Graph returns an error
            return $this->buildResponse('Graph long token', $e->getMessage());
        } catch (FacebookSDKException $e) {
            // When validation fails or other local issues
            return $this->buildResponse('Facebook SDK long token', $e->getMessage());
        }

        // building expires_at date from Facebook's datetime header
        $facebookTime = Carbon::parse($longToken->getHeaders()['Date']);
        $longToken = $longToken->getDecodedBody();
        $secondsToAdd = $longToken['expires_in'];

        return (object) [
            'access_token' => $longToken['access_token'],
            'issued_at'    => $facebookTime->toDateTimeString(),
            'expires_at'   => $facebookTime->addSeconds($secondsToAdd)->toDateTimeString(),
        ];
    }

    /**
     * Retrieve the user from Facebook
     *
     * @param  object $token
     * @return object
     */
    public function getUser($token = null)
    {
        $helper = $this->helper;

        if (is_null($token)) {
            $token = $this->resolveToken();
        }

        if (! $token or (isset($token->error) and $token->error)) {
            return $token;
        }

        // identical response, couldn't fit it all on one line. Doing
        // a second block for the sake of sanity at the cost of
        // copy-pasted code.
        if (! empty($token) and (isset($token->email) or isset($token->user->email))) {
            return $token;
        }

        if (! isset($token->access_token) and isset($token->user)) {
            $token = $token->user;
        }

        $this->facebook->setDefaultAccessToken($token->access_token);

        try {
            $userNode = $this->facebook->get('/me?fields=id,first_name,last_name,email')->getGraphUser();

            $oauth = [
                'id'            => $userNode['id'],
                'first_name'    => $userNode['first_name'],
                'last_name'     => $userNode['last_name'],
                'service_email' => $userNode['email'],
                'access_token'  => $token->access_token,
                'refresh_token' => null,
                'issued_at'     => $token->issued_at,
                'expires_at'    => $token->expires_at,
            ];

            session()->put('oauth', $oauth);
            $this->saveToken((object) $oauth);

            return (object) $oauth;
        } catch (FacebookResponseException $e) {
            // When Graph returns an error
            return $this->buildResponse('Graph user', $e->getMessage());
        } catch (FacebookSDKException $e) {
            // When validation fails or other local issues
            return $this->buildResponse('Facebook SDK user', $e->getMessage());
        }
    }

    /**
     * Save an Oauth token for a given user.
     *
     * @method saveToken
     * @param  stdClass    $tokenUser
     * @return bool|\App\Models\OauthToken
     */
    public function saveToken(stdClass $tokenUser)
    {
        if (! auth()->check()) {
            $user = $this->userRepo->findByEmail($tokenUser->service_email);
            if (!isset($user)) {
                return response()->json('Could not find matching email', 422);
            }
            $user_id = $user->id;
            $user_email = $user->email;
        } else {
            $user_id = auth()->id();
            $user_email = auth()->user()->email;
        }

        return $this->oauthTokenRepo->updateOrCreate([
            'user_id'         => $user_id,
            'driver_id'       => $this->driverId,
            'email'           => $user_email,
        ], [
            'user_id'         => $user_id,
            'email'           => $user_email,
            'service_email'   => $tokenUser->service_email,
            'service_user_id' => $tokenUser->id,
            'access_token'    => $tokenUser->access_token,
            'refresh_token'   => null,
            'expires_at'      => $tokenUser->expires_at,
            'issued_at'       => $tokenUser->issued_at,
            'updated_at'      => $tokenUser->issued_at,
        ]);
    }

    /**
     * Login a user after verifying that a valid oauth token
     * exists for the given user.
     *
     * @method loginWithUser
     * @param  stdClass        $tempUser
     * @return bool|\App\Models\User
     */
    public function loginWithUser(stdClass $tempUser)
    {
        $oauthToken = $this->oauthTokenRepo->findByEmail($tempUser->email, 'facebook');

        if (! $oauthToken) {
            throw new OauthUserLoginException('Oauth token not found in our database.', HTTP_BAD_REQUEST);
        }

        $user = $oauthToken->user;

        $roleCheck = $this->checkRole($user);
        if ($roleCheck['error']) {
            return ['error' => $roleCheck['message'], 'redirect_url' => $roleCheck['redirect_url']];
        }

        auth()->loginUsingId($user->id);
        return $user;
    }

    private function checkRole($user)
    {
        $error = false;
        $message = '';
        $redirectUrl = '/login';

        if (! isset($user->role)) {
            $error = true;
            $message = 'No valid role found for user';
        }

        $roleName = $user->role->name;
        if ($roleName === 'Customer') {
            $error = true;
            $message = "Customer accounts cannot login via $driver";
        }

        if ($roleName === 'Admin' or $roleName === 'Superadmin') {
            $error = true;
            $message = 'Cannot associate to a user that is not a Rep';
        }

        return [
            'error'        => $error,
            'message'      => $message,
            'redirect_url' => $redirectUrl,
        ];
    }

    /**
     * Set the driver ID once if it hasn't been set already.
     *
     * @method setDriverId
     * @return void
     */
    private function setDriverId()
    {
        if (is_null($this->driverId)) {
            $driverModel = \App\Models\OauthDriver::where('keyname', 'facebook')->first();
            $this->driverId = $driverModel->id;
        }
    }

    /**
     * Build the response on error
     *
     * @return object
     */
    private function buildResponse($whoami = 'Generic Facebook SDK', $exceptionMessage = '')
    {
        $helper = $this->helper;
        $response = [
            'error' => true,
            'data' => [
                'code'        => HTTP_BAD_REQUEST,
                'error'       => 'Bad Request',
                'reason'      => '',
                'description' => $whoami . ' request returned an error: ' . $exceptionMessage,
            ],
        ];
        if ($helper->getError()) {
            $response['data'] = [
                'code'        => $helper->getErrorCode(),
                'error'       => $helper->getError(),
                'reason'      => $helper->getErrorReason(),
                'description' => $helper->getErrorDescription(),
            ];
        }

        return (object) $response;
    }

    /**
     * Get an existing token or retrieve a new one
     *
     * @return object|\App\Models\OauthToken
     */
    private function resolveToken()
    {
        $helper = $this->helper;
        $userToken = session('oauth');

        if (! $userToken) {
            // get a new token
            $tokenResponse = $this->getToken();

            if (! $tokenResponse) {
                return false;
            }

            if (! is_object($tokenResponse) or ! isset($tokenResponse->access_token)) {
                $response['error'] = true;
                $response['data']['code'] = HTTP_BAD_REQUEST;
                $response['data']['description'] = 'Bad Request';
                if ($helper->getError()) {
                    $response['data'] = [
                        'code'        => $helper->getErrorCode(),
                        'error'       => $helper->getError(),
                        'reason'      => $helper->getErrorReason(),
                        'description' => $helper->getErrorDescription(),
                    ];
                }

                return (object)$response;
            }
            session()->put('oauth', $tokenResponse);
            return $tokenResponse;
        }

        return (object) $userToken;
    }
}
