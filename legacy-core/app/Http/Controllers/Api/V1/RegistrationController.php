<?php namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\SetupPaymentProviderRequest;
use Illuminate\Http\Request;
use App\Models\Blacklist;
use App\Models\RegistrationToken;
use App\Repositories\Eloquent\BundleRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Repositories\Eloquent\StoreSettingRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Services\Registration\RegistrationService;
use App\Services\Settings\SettingsService;

class RegistrationController extends Controller
{
    protected $registrationService;
    protected $bundleRepo;
    protected $userRepo;
    protected $roleRepo;
    protected $settingsService;
    protected $storeSettingRepo;
    protected $subscriptionRepo;
    protected $register_with_code;
    protected $registration_code;
    protected $collect_sponsor_id;
    protected $require_sponsor_id;

    /**
     * Create a new controller instance.
     *
     * @param  BundleRepository $bundleRepo
     * @param  RegistrationService $registrationService
     * @param  RoleRepository $roleRepo
     * @param  SubscriptionRepository $subscriptionRepo
     * @return void
     */
    public function __construct(
        BundleRepository $bundleRepo,
        RegistrationService $registrationService,
        RoleRepository $roleRepo,
        UserRepository $userRepo,
        StoreSettingRepository $storeSettingRepo,
        SubscriptionRepository $subscriptionRepo
    ) {
        $this->bundleRepo = $bundleRepo;
        $this->registrationService = $registrationService;
        $this->roleRepo = $roleRepo;
        $this->userRepo = $userRepo;
        $this->settingsService = app('globalSettings');
        $this->storeSettingRepo = $storeSettingRepo;
        $this->subscriptionRepo = $subscriptionRepo;
        $this->register_with_code = $this->settingsService->getGlobal('require_registration_code', 'show');
        $this->registration_code = $this->settingsService->getGlobal('require_registration_code', 'value');
        $this->collect_sponsor_id = $this->settingsService->getGlobal('collect_sponsor_id', 'show');
        $this->require_sponsor_id = $this->settingsService->getGlobal('require_sponsor_id_on_registration', 'show');
    }

    /**
     * Pay for a rep's subscription plan.
     *
     * @return Response
     */
    public function register(RegisterRequest $registerRequest)
    {
        $request = $registerRequest->all();
        $user = $this->userRepo->findByEmail($request['user']['email']);
        if (isset($user) && $user->role_id !== 3) {
            // If the user is already in the system and they are not a customer throw validation for unique email.
            return response()->json(['user.email' => ['Email already signed up']], 422);
        }

        if (isset($request['payment']['card_number'])) {
            // refactor payment info to match new structure
            $request['payment']['type'] = 'card';
            $request['payment']['card'] = [
                'number' => $request['payment']['card_number'],
                'month' => $request['payment']['month'],
                'year' => $request['payment']['year'],
                'code' => $request['payment']['security'],
                'name' => $request['payment']['name']
            ];
            unset($request['payment']['card_number']);
            unset($request['payment']['name']);
            unset($request['payment']['security']);
            unset($request['payment']['year']);
            unset($request['payment']['month']);
        }

        $request['user']['public_id'] = strtolower($request['user']['public_id']);
        // make sure registration code matches the settings code value
        if ($this->register_with_code && $request['registration_code'] !== $this->registration_code) {
            return response()->json(['registration_code' => 'Invalid registration code.'], 422);
        }

        if ($this->collect_sponsor_id && isset($request['sponsor_id']) && $request['sponsor_id'] !== '') {
            // find sponsor by public id
            $sponsor = $this->userRepo->findByPublicId($request['sponsor_id']);
            if ($sponsor === null) {
                return response()->json(['sponsor_id' => ['A sponsor with that ID could not be found.']], 422);
            }
            $request['user']['sponsor_id'] = $sponsor->id;
        }

        $request['user']['role_id'] = $this->roleRepo->findIdByName('Rep');

        // check to find subscription
        $subscription = $this->subscriptionRepo->find($request['subscription_id'], ['price']);
        if (!$subscription) {
            return response()->json('Could not find subscription plan', HTTP_BAD_REQUEST);
        }
        if (isset($request['subscription_bill']) && !$request['subscription_bill'] && $subscription->price->price > 0) {
            return response()->json('Could not match request info with subscription plan', HTTP_BAD_REQUEST);
        }

        // check to find starter kit and if it has enough inventory
        if (isset($request['starter_kit_id'])) {
            $starterKit = $this->bundleRepo->starterKits()->where('id', $request['starter_kit_id'])->first();
            if (!$starterKit) {
                return response()->json('Could not find starter kit or starter kit is out of inventory.', HTTP_BAD_REQUEST);
            }
        }

        // check to see if the public id is blacklisted
        $blacklistedNames = Blacklist::all();
        foreach ($blacklistedNames as $name) {
            if (isset($request['user']['public_id']) && $name->name === $request['user']['public_id']) {
                return response()->json('Cannot use ' . $request['user']['public_id'] . ' as subdomain. This subdomain is blacklisted.', HTTP_UNPROCESSABLE);
            }
        }

        // pay for starter kit if there is one (needs to happen before subscription payment)
        if (isset($request['starter_kit_id'])) {
            $starterKitPayment = $this->registrationService->processStarterKit($request, $starterKit->id);
            if (isset($starterKitPayment['success']) and $starterKitPayment['success'] === false) {
                return response()->json($starterKitPayment['description'], HTTP_BAD_REQUEST);
            }
        }

        // pay for subscription, create new rep and adds subscription duration to the new rep
        $order = isset($starterKitPayment) ? $starterKitPayment : null;
        $result = $this->registrationService->processNewSubscription($request, $subscription, $order);
        // Check if a decline happened
        if (isset($result['resultCode']) and $result['resultCode'] > 1) {
            if ($order != null) {
                // cancel order if the subscription failed
                dispatch(new \App\Jobs\CancelOrder($order));
            }
            return response()->json($result['result'], HTTP_BAD_REQUEST);
        }
        $subscriptionPayment = $result['userSubscription'];
        $jwtToken = $result['jwtToken'];


        $fullName = $request['user']['first_name'] . ' ' . $request['user']['last_name'];
        $storeSetting = $this->storeSettingRepo->createSettings($subscriptionPayment['user_id'], $fullName);
        $request['user']['id'] = $subscriptionPayment->user_id;
        // Clone retail shipping settings from client to rep
        \DB::statement('INSERT INTO shipping_rates(user_id, amount, min, max, type, name, user_pid, created_at, updated_at) (SELECT ?, amount, min, max, type, name, ?,NOW(), NOW() FROM shipping_rates WHERE user_id = 1 AND type = "retail")', [$subscriptionPayment->user_id, $subscriptionPayment->user_pid]);

        if (isset($request['user']) && isset($request['user']['token'])) {
            $token = RegistrationToken::where('token', $request['user']['token'])
                        ->first();
            $token->user_id = $request['user']['id'];
            $token->save();
        }

        return response()->json(['auth' => true, 'activeSubscription' => true, 'role' => 'Rep', 'jwtToken' => $jwtToken], HTTP_SUCCESS);
    }
    /**
     * validates a new user
     *
     * For step 1 on rep registration process, validates user information
     *
     * @param Request $request
     * @return return JSON
     */
    public function validateUser(Request $request)
    {
        // ensure public id is validated as string to lower
        if (isset($requst['user']['public_id'])) {
            $request['user']['public_id'] = strtolower($request['user']['public_id']);
        }
        // NOTE: subdomain or storeName has it's own validation...
        // ...method in controlpad api registration controller
        $rules = [
            'user.first_name' => 'required',
            'user.last_name' => 'required',
            'user.email' => 'required|email',
            'user.password' => 'required|min:8',
            'user.public_id' => 'required|no_underscores|alpha_dash|unique:users,public_id'
        ];

        if ($this->settingsService->getGlobal('collect_phone_on_registration', 'show') && $this->settingsService->getGlobal('require_phone_on_registration', 'show')) {
            $rules['user.phone'] = 'required|min:7';
        }

        $user = $this->userRepo->findByEmail($request->input('user.email'));
        if ($user and $user->role_id !== 3) {
            $rules['user.email'] = 'required|email|unique:users,email';
        }

        if ($this->register_with_code) {
            $rules['registration_code'] = 'required';
        }
        if ($this->collect_sponsor_id && $this->require_sponsor_id) {
            $rules['sponsor_id'] = 'required|alpha_dash';
        } elseif ($this->collect_sponsor_id) {
            $rules['sponsor_id'] = 'alpha_dash|nullable';
        }

        $blacklistedNames = Blacklist::all();
        foreach ($blacklistedNames as $name) {
            if ($name->name === $request['user']['public_id']) {
                return response()->json(['user.public_id' => 'This store name has been blacklisted.'], 422);
            }
        }
        $messages = [
            'user.public_id.no_underscores' => 'This field cannot contain underscores.'
        ];

        $this->validate($request, $rules, $messages);
        // make sure registration code matches the settings code value
        if ($this->register_with_code && $request['registration_code'] !== $this->registration_code) {
            return response()->json(['registration_code' => 'Invalid registration code.'], 422);
        }
        if ($this->collect_sponsor_id && $request['sponsor_id']) {
            // find sponsor by public id
            $sponsor = $this->userRepo->findByPublicId($request['sponsor_id']);
            if (!$sponsor) {
                return response()->json(['sponsor_id' => ['A sponsor with that ID could not be found.']], 422);
            }
        }
        return response()->json('The user information is valid.', 200);
    }
    /**
     * This is for rep that are in the system before the payment processing has been set up.
     *
     *
     * @param User $user
     * @param $ppa payment process account info
     * @return return JSON
     */
    public function createPaymentAccount(SetupPaymentProviderRequest $request)
    {
        $paymentAccount = $this->registrationService->createPaymentAccount(auth()->user(), $request->all());
        return response()->json($paymentAccount['body'], $paymentAccount['status']);
    }

    public function checkUserAccount($user_id)
    {
        $userAccount = $this->registrationService->checkUserAccount($user_id);
    }

    /**
     * Check to see if a given public id has already been taken.
     * @param public_id
     * @return Response
     */
    public function checkPublicId(Request $request, $public_id)
    {
        // ensure public id is validated as string to lower
        $request['public_id'] = strtolower($public_id);
        $messages = [
            'public_id.required' => 'A store name is required.',
            'public_id.alpha_dash' => 'A store name may only contain letters, numbers and dashes.',
            'public_id.unique' => 'This store name is unavailable.',
        ];
        $this->validate($request, ['public_id' => 'required|alpha_dash|unique:users,public_id'], $messages);
        // blacklisted
        $blacklistedNames = Blacklist::all();
        foreach ($blacklistedNames as $name) {
            if ($name->name === $request['public_id']) {
                return response()->json(['public_id' => 'This store name has been blacklisted.'], 422);
            }
        }

        return response()->json('Available.', 200);
    }

    public function fixRepsWithoutSubscription()
    {
        $response = $this->registrationService->fixRepsWithoutSubscription();
        return response()->json($response, 200);
    }

    public function getIndexOnSignUp()
    {
        $subscription = $this->subscriptionRepo->indexSignUp();
        return $subscription;
    }
    public function getUserToken($token)
    {
        $token = RegistrationToken::where('token', $token)->first();
        return response()->json($token);
    }
}
