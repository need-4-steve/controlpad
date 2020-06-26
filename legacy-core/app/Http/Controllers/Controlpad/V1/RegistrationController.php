<?php

namespace App\Http\Controllers\Controlpad\V1;

use Hash;
use Mail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Events\ApiRegistrationNew;
use App\Models\User;
use App\Models\Blacklist;
use App\Models\Setting;
use App\Models\RegistrationToken;
use Carbon\Carbon;
use App\Repositories\Eloquent\SubscriptionRepository;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class RegistrationController extends Controller
{
    public function __construct(SubscriptionRepository $subscriptionRepo)
    {
        $this->settings = app('globalSettings');
        $this->subscriptionRepo = $subscriptionRepo;
    }

    /**
     * Wraps authentication method for logging in using an existing user in the system
     * through JWTAuth
     *
     * TODO: Refactor authentication model
     */
    public function authenticate(Request $request)
    {
        // grab credentials from the request
        $credentials = $request->only('email', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], HTTP_UNAUTHORIZED);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], HTTP_SERVER_ERROR);
        }

        // all good so return the token
        return response()->json(compact('token'));
    }

    /**
     * Check to see if a given public id has already been taken.
     * @param public_id
     * @return Response
     */
    public function checkPublicId(Request $request, $public_id)
    {
        try {
            $request['public_id'] = $public_id;
            $messages = [
                'public_id.required' => 'A store name is required.',
                'public_id.alpha_dash' => 'A store name may only contain letters, numbers and dashes.',
                'public_id.unique' => 'This store name is unavailable.',
            ];
            $this->validate($request, ['public_id' => 'required|alpha_dash|unique:users,public_id'], $messages);
            // blacklisted
            $blacklistedNames = Blacklist::all();
            foreach ($blacklistedNames as $name) {
                if ($name->name === $public_id) {
                    return response()->json(['public_id' => 'This store name has been blacklisted.'], 422);
                }
            }
            return response()->json('Available.', 200);
        } catch (Exception $e) {
            return "Error: " . $e;
        }
    }

    /**
     * Handle a request to create a new user registration token in our system
     * @param token
     * @param email
     * @param source_id
     * @return token
     */
    public function createRegistrationToken()
    {
        try {
            $request = request()->all();
            $registration = [
                'token' => str_replace('/', '', Hash::make(str_random(42))),
                'email' => $request['email'],
                // setting source to 1 automatically as this api will be deprecated
                'source_id' => 1,
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name']
            ];
            $token = RegistrationToken::create($registration);

            // send email
            event(new ApiRegistrationNew($token));

            return $token;
        } catch (Exception $e) {
            return "Error: " . $e;
        }
    }

    /**
     * Check to see if a given token is valid for joining.
     *
     * @return View
     */
    public function registerWithToken($token)
    {
        try {
            $token = RegistrationToken::where('token', $token)->first();
            $subscription = $this->subscriptionRepo->indexSignUp();
            $sponsor = config('site.apex_user_id');
            if (!empty($token)) {
                return view('registration.signup', compact('token', 'subscription', 'sponsor'));
            }
        } catch (Exception $e) {
            return "Error: " . $e;
        }
    }

    /**
     * Check to see if a given email address has already been taken.
     * @param Request $request
     * @return Response
     */
    public function checkEmail(Request $request)
    {
        try {
            $messages = [
                'email.required' => 'An email address is required.',
                'email.unique' => 'This email address is unavailable.'
            ];
            $this->validate($request, ['email' => 'required|email|unique:users,email'], $messages);
            return response()->json('Available.', 200);
        } catch (Exception $e) {
            return "Error: " . $e;
        }
    }
}
