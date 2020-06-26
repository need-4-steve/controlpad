<?php

namespace App\Repositories\Eloquent;

use Hash;
use DB;
use Carbon\Carbon;
use App\Events\WelcomeEvent;
use App\Events\PasswordNewEvent;
use App\Mail\SponsorNotice;
use App\Models\Blacklist;
use App\Models\CompanyInfo;
use App\Models\RegistrationToken;
use App\Models\SubscriptionUser;
use App\Models\User;
use App\Models\Order;
use App\Models\CustomEmail;
use App\Services\Commission\CommissionService;
use App\Repositories\Eloquent\AddressRepository;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\CustomPageRepository;
use App\Repositories\Eloquent\PhoneRepository;
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Repositories\Eloquent\UserSettingsRepository;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use App\Services\PayMan\DirectDepositService;

class UserRepository implements UserRepositoryContract
{
    use CommonCrudTrait;

    public function __construct(
        AddressRepository $addressRepo,
        AuthRepository $authRepo,
        CommissionService $commissionService,
        PhoneRepository $phoneRepo,
        SubscriptionRepository $subRepo,
        UserSettingsRepository $userSettings,
        DirectDepositService $directDepositService,
        CustomPageRepository $customPageRepo
    ) {
        $this->subRepo = $subRepo;
        $this->addressRepo = $addressRepo;
        $this->authRepo = $authRepo;
        $this->commissionService = $commissionService;
        $this->phoneRepo = $phoneRepo;
        $this->userSettings = $userSettings;
        $this->settingsService = app()->make('globalSettings');
        $this->directDepositService = $directDepositService;
        $this->customPages = $customPageRepo;
    }

    public function getIndex($request, $userCsvRequest = false)
    {
        $input['search'] = isset($request['search_term']) ? $request['search_term'] : '';
        $input['column'] = isset($request['column']) ? $request['column'] : 'last_name';
        $input['order'] = isset($request['order']) ? $request['order'] : 'ASC';
        $input['limit'] = isset($request['per_page']) ? $request['per_page'] : 15;
        $input['role'] = isset($request['role']) ? $request['role'] : 'All';
        $input['status'] = isset($request['status']) ? $request['status'] : '';

        $users = User::with('role', 'settings')->select('users.*', 'addresses.state');
        $users = $users->leftJoin('addresses', function ($join) {
            $join->on('users.id', '=', 'addresses.addressable_id')
                ->where('addressable_type', 'App\Models\User')
                ->where('label', 'Shipping');
        });
        if ($userCsvRequest == true) {
            $users = $users->with('sponsor', 'billingAddress', 'shippingAddress');
        }
        if (!empty($input['search']) && $input['search'] != 'null') {
            $users->search($input['search'], ['role.name', 'first_name', 'last_name', 'id']);
        }
        if (!empty($input['role'])) {
            // numeric, check id
            if (is_numeric($input['role'])) {
                if ($input['role'] == 7) {
                    $users->whereIn('role_id', [7, 8]);
                } else {
                    $users->where('role_id', $input['role']);
                }
            }
        }

        if ($input['status'] !== '') {
            $users->where('status', $input['status']);
        }

        if ($input['column'] == 'role_name') {
            $users->select('*', 'users.id as id')
                ->join('roles', 'users.role_id', '=', 'roles.id')
                ->orderBy('roles.name', $input['order']);
        } elseif ($input['column'] == 'rep_type') {
            $users->orderBy('seller_type_id', $input['order']);
        } else {
            $users->orderBy($input['column'], $input['order']);
        }

        if ($userCsvRequest == false) {
            $users = $users->paginate($input['limit']);
            if ($input['role'] == 5) {
                $verifiedIds = $this->directDepositService->getValidatedUsers();
                foreach ($users as $user) {
                    $user->verified = in_array($user->id, $verifiedIds);
                }
            }
            return $users;
        } else {
            return $users->get();
        }
    }

    /**
     * Check to see if a user has accepted the terms and conditions since last updated.
     *
     * @method checkIfAcceptedTerms
     * @return bool
     */
    public function checkIfAcceptedTerms()
    {
        if ($terms = $this->customPages->show('rep-terms')) {
            $termsUpdatedDate = $terms->revised_at;
        } else {
            // terms haven't been setup, pass on terms
            return true;
        }


        if (auth()->user()->termsAccepted != null) {
            $acceptedDate = auth()->user()->termsAccepted->updated_at;
        } else {
            // no acceptance date set
            return false;
        }

        // we've accepted since it changed
        if ((isset($acceptedDate) && isset($termsUpdatedDate)) && $acceptedDate > $termsUpdatedDate) {
            return true;
        }

        // haven't accepted since change
        return false;
    }

    /**
     * Find a user by their mobile key
     *
     * @param string $key
     * @return mixed
     */
    public function findByKey($key)
    {
        $user = User::where('mobile_key', $key)->first();
        return $this->filterBySettings($user);
    }

    /**
     * Find a user by registration token
     *
     * @param token
     * @return User
     */
    public function findByRegistrationToken($token)
    {
        $registrationToken = RegistrationToken::with('user')->where('token', $token)->first();
        if (!empty($registrationToken->user)) {
            return $registrationToken->user;
        }
        return null;
    }

    /**
     * Find a registration token by user id
     *
     * @param User
     * @return token
     */
    public function findRegistrationTokenId($userId)
    {
        $registrationToken = RegistrationToken::where('user_id', $userId)->first();
        if (!empty($registrationToken)) {
            return $registrationToken->token;
        }
        return null;
    }

    public function getPidForUserId($id)
    {
        $user = User::select('pid')->where('id', $id)->first();
        return ($user == null ? null : $user->pid);
    }

    public function updateUser($request, $id)
    {
        $rules = [];
        $data = request()->all();
        $original_record = User::where('id', $id)->first();
        $user = $original_record;

        // Convert email to lowercase
        if (isset($data['email']) and $data['email'] !== $user->email) {
            $data['email'] = strtolower($data['email']);
        }

        if (isset($data['public_id'])) {
            $data['public_id'] = strtolower($data['public_id']);
        }

        if (isset($data['public_id'])) {
            $blacklistedNames = Blacklist::all();
            foreach ($blacklistedNames as $name) {
                if (isset($data['public_id']) && $name->name === $data['public_id']) {
                    return ['error' => 'Cannot use ' . $data['public_id'] . ' as subdomain.  This subdomain is blacklisted.', HTTP_UNPROCESSABLE];
                }
            }
        }

        // Hash password
        if (isset($data['password']) && $data['password'] !== '') {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        if (isset($data['password'])) {
             event(new PasswordNewEvent($user));
        }

        // Don't allow user to update their own status
        if (auth()->user()->id == $original_record->id) {
            if (isset($data['status'])) {
                unset($data['status']);
            }
        }

        // phone
        if (isset($data['phone'])) {
            $data['phone_number'] = $data['phone']['number'];
            $this->phoneRepo->createOrUpdate($data['phone'], $user->id);
        }

        // update user
        $user->update($data);

        // update or create addresses
        if (isset($data['shipping_address'])) {
            $data['shipping_address']['addressable_id'] = $user->id;
            $data['shipping_address']['addressable_type'] = 'App\Models\User';
            $data['shipping_address']['label'] = 'Shipping';
            $this->addressRepo->create($data['shipping_address']);
        }
        if (isset($data['billing_address'])) {
            $data['billing_address']['addressable_id'] = $user->id;
            $data['billing_address']['addressable_type'] = 'App\Models\User';
            $data['billing_address']['label'] = 'Billing';
            $this->addressRepo->create($data['billing_address']);
        }
        if (isset($data['business_address'])) {
            $data['business_address']['addressable_id'] = $user->id;
            $data['business_address']['addressable_type'] = 'App\Models\User';
            $data['business_address']['label'] = 'Business';
            $this->addressRepo->create($data['business_address']);
        }

        // role
        if (isset($data['role']) && auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            $user->role_id = $data['role']['id'];
            $user->save();
        }
        $this->commissionService->queUser($user);
        return $user;
    }

    public function createRep($data, ?Order $order = null)
    {
        // check to see if we have a blacklisted subdomain
        $blacklistedNames = Blacklist::all();
        foreach ($blacklistedNames as $name) {
            if (isset($data['user']['public_id']) && $name->name === $data['user']['public_id']) {
                return ['error' => 'Cannot use ' . $data['user']['public_id'] . ' as subdomain.  This subdomain is blacklisted.',HTTP_UNPROCESSABLE];
            }
        }
        // don't set a public id for users that are not rep
        if ($data['user']['role_id'] !== 5) {
            unset($data['user']['public_id']);
        }
        $subscription = $this->subRepo->find($data['subscription_id']);
        $data['user']['seller_type_id'] = $subscription->seller_type_id;

        // this user has been soft deleted already, allow a new user to create with same email
        $user = User::where('email', $data['user']['email'])->first();
        if (!empty($user) && $user->trashed()) {
            $user->forceDelete();
        }

        $user = User::firstOrCreate(['email' => strtolower($data['user']['email'])]);
        if (isset($user->role_id) and $user->role_id !== 3) {
            return ['error' => 'User already exists with email: '.$data['user']['email']];
        }

        if (!isset($data['user']['role_id'])) {
            $data['user']['role_id'] = 5;
        }

        if ($data['user']['role_id'] === 3) {
            unset($data['user']['password']);
        } else {
            $data['user']['password'] = Hash::make($data['user']['password']);
        }
        $user->join_date = Carbon::now('UTC');
        unset($data['user']['id']);

        // phone
        if (isset($data['phone'])) {
            $data['user']['phone_number'] = $data['phone']['number'];
            $this->phoneRepo->createOrUpdate($data['phone'], $user->id);
        }
        $user->update($data['user']);
        session()->forget('auth-role-'.$user->id);
        // TODO: remove email event trigger out of repository, this logic shouldn't go here
        if ($this->settingsService->getGlobal('collect_sponsor_id', 'show')) {
            $fromEmail = $this->settingsService->getGlobal('from_email', 'value');
            try {
                // TODO: This need move into an event or job.
                // TODO: remove email event trigger out of repository, this logic shouldn't go here
                $sendEmail = CustomEmail::where('title', 'sponsor_notice')->first()->send_email;
                if ($sendEmail) {
                    Mail::to($user->sponsor->email)
                    ->send(new SponsorNotice($user, $fromEmail));
                }
            } catch (\Exception $e) {
                logger()->error($e);
            }
        }

        // billing address
        if (isset($data['addresses']['billing']['address_1'])) {
            $data['addresses']['billing']['addressable_id'] = $user->id;
            $data['addresses']['billing']['addressable_type'] = User::class;
            $data['addresses']['billing']['label'] = 'Billing';
            if (!isset($data['addresses']['billing']['name'])) {
                $data['addresses']['billing']['name'] = $user->full_name;
            }
            $billing = $this->addressRepo->create($data['addresses']['billing']);
        }

        // shipping address
        if (isset($data['addresses']['shipping'])) {
            $data['addresses']['shipping']['addressable_id'] = $user->id;
            $data['addresses']['shipping']['addressable_type'] = User::class;
            $data['addresses']['shipping']['label'] = 'Shipping';
            if (!isset($data['addresses']['shipping']['name'])) {
                $data['addresses']['shipping']['name'] = $user->full_name;
            }
            $shipping = $this->addressRepo->create($data['addresses']['shipping']);
            // set business address to the shipping address
            $businessAddress = $data['addresses']['shipping'];
            $businessAddress['label'] = 'Business';
            $this->addressRepo->create($businessAddress);
        }

        // rep subscription and settings
        if ($data['user']['role_id'] === 5 and isset($data['subscription_id'])) {
            $timezone = $this->userSettings->getUserTimeZone(config('site.apex_user_id'));
            $this->userSettings->newUser($user->id, $user->pid, $timezone);
        }
        event(new WelcomeEvent($user, $order));
        $this->commissionService->queUser($user);
        return $user;
    }

    public function createCustomer(array $input)
    {
        $user = User::firstOrNew(['email' => $input['email']]);
        if ($user->first_name) {
            return $user;
        }
        $user->first_name = $input['first_name'];
        $user->last_name = $input['last_name'];
        $user->join_date = Carbon::now('UTC');
        $user->save();
        $this->commissionService->queUser($user);
        return $user;
    }

    public function createNew(array $data)
    {
        // check to see if we have a blacklisted subdomain
        $blacklistedNames = Blacklist::all();
        foreach ($blacklistedNames as $name) {
            if (isset($data['public_id']) && $name->name === $data['public_id'] && $data['public_id'] !== "") {
                return ['error' => 'Cannot use ' . $data['public_id'] . ' as subdomain.  This subdomain is blacklisted.',HTTP_UNPROCESSABLE];
            }
        }
        // don't set a public id for admin users
        if (isset($data['role']['id']) == 7 || isset($data['role']['id']) == 8) {
            if ($data['public_id'] == null or $data['public_id'] == "") {
                unset($data['public_id']);
            }
        }
        /* start a transaction, commit at end; this should stop user being created
         * if there is an error or a bug
         */
        DB::beginTransaction();
        $data['password'] = Hash::make($data['password']);
        $data['email'] = strtolower($data['email']);
        if (isset($data['role']['id'])) {
            $data['role_id'] = $data['role']['id'];
        } else {
            $data['role_id'] = $data['role'];
        }
        $data['join_date'] = Carbon::now('UTC');
        if (isset($data['phone'])) {
            $data['phone_number'] = $data['phone']['number']; // Workaround to plug number into new column
        }
        $user = User::create($data);
        // send email only if Rep - express creates admins and needs a different email sent out
        if ($user->role_id == 5) {
            // TODO: remove email event trigger out of repository, this logic shouldn't go here
            event(new WelcomeEvent($user));
        }
        // set address data
        $data['addressable_id'] = $user->id;
        $data['addressable_type'] = 'App\Models\User';
        // create billing
        $data['label']  = 'Billing';
        $address  = $this->addressRepo->create($data);
        // create shipping
        $address->label = 'Shipping';
        $this->addressRepo->create($address->toArray());
        // create business
        if ($user->role_id == 5) {
            $address->label = 'Business';
            $this->addressRepo->create($address->toArray());
        }
        // phone
        if (isset($data['phone'])) {
            $this->phoneRepo->createOrUpdate($data['phone'], $user->id);
        }
        $this->userSettings->newUser($user->id, $user->pid);
        if ($data['role_id'] === 5) {
            $this->subRepo->newUser($user, $data['subscriptionId']);
        }
        $this->commissionService->queUser($user);
        DB::commit();
        return $user;
    }

    /**
     * find user by public_id
     *
     * @param int public_id
     * @return User
     */
    public function findByPublicId($publicId, $relationships = [])
    {
        $relationships[] = 'settings';
        $user = User::with($relationships)
                    ->where(['public_id' => $publicId, 'disabled_at' => null])
                    ->first();
        return $this->filterBySettings($user);
    }

    public function attachCustomer($storeOwner, $customer)
    {
        // Checks to see if the customer has already bought from the store owner.
        $check = User::where('id', $storeOwner->id)->whereHas('customers', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        })->first();

        // If the customer has not previously bought, it associates them to the store owwner.
        if (!$check) {
            $storeOwner->customers()->attach($customer);
        }
        return $storeOwner;
    }

    public function search($searchTerm, $role, $userId)
    {
        $users = User::with('billingAddress', 'shippingAddress', 'settings')
                    ->where('phone_number', 'LIKE', '%'.$searchTerm.'%')
                    ->orWhere('email', 'LIKE', '%'.$searchTerm.'%')
                    ->orWhereRaw("(CONCAT(first_name, ' ', last_name) LIKE '%".$searchTerm."%')");

        // If the person searching is not an Admin it will only return customers that have bought from them.
        if ($role !== 'Admin' and $role !== 'Superadmin') {
            $users->select('*', 'users.id as id')
                ->join('customers', function ($join) use ($userId) {
                    $join->on('customers.customer_id', '=', 'users.id')
                        ->where('customers.user_id', '=', $userId);
                });
        }

        $users = $users->get();
        foreach ($users as $key => $user) {
            $users[$key] = $this->filterBySettings($user);
        }

        return $users;
    }

    public function searchReps($searchTerm, $role)
    {
        return User::select('id', 'first_name', 'last_name')
                    ->where(function ($query) use ($searchTerm) {
                        $query->where('id', $searchTerm)
                            ->orWhere('email', 'LIKE', '%'.$searchTerm.'%')
                            ->orWhereRaw("(CONCAT(first_name, ' ', last_name) LIKE '%".$searchTerm."%')");
                    })
                    ->where('role_id', 5)
                    ->get();
    }

    public function searchSponsors($searchTerm)
    {
        return User::select('id', 'first_name', 'last_name')
                    ->where(function ($query) use ($searchTerm) {
                        $query->where('id', $searchTerm)
                            ->orWhere('email', 'LIKE', '%'.$searchTerm.'%')
                            ->orWhereRaw("(CONCAT(first_name, ' ', last_name) LIKE '%".$searchTerm."%')");
                    })
                    ->where('role_id', 5)
                    ->orWhere('id', Config('site.apex_user_id'))
                    ->get();
    }

    public function filterBySettings(User $user = null)
    {
        // if no user or settings passed, return
        if (empty($user) || empty($user->settings)) {
            return $user;
        }

        // if these are our settings or we are an admin, skip filter
        if (($this->authRepo->getOwnerId() == $user->id)
            || $this->authRepo->isOwnerAdmin()) {
            return $user;
        }

        if (! $user->settings->show_address) {
            // have to unset each relationship and then set
            // a null attribute on the base object
            if (isset($user->billingAddress)) {
                unset($user->billingAddress);
                $user->billing_address = null;
            }
            if (isset($user->shippingAddress)) {
                unset($user->shippingAddress);
                $user->shipping_address = null;
            }
            if (isset($user->businessAddress)) {
                unset($user->businessAddress);
                $user->business_address = null;
            }
            if (isset($user->addresses)) {
                unset($user->addresses);
                $user->addresses = null;
            }
        }
        if (! $user->settings->show_phone) {
            if (isset($user->phone_number)) {
                unset($user->phone_number);
                $user->phone_number = null;
            }
        }
        if (! $user->settings->show_email) {
            if (isset($user->email)) {
                unset($user->email);
                $user->email = null;
            }
        }

        return $user;
    }

    public function find($id, array $eagerLoad = [], $trashed = false)
    {
        $query = User::with($eagerLoad)->with('settings');
        if ($trashed) {
            $query->withTrashed();
        }

        $result = $query->where('id', $id)->first();
        if ($result === null) {
            return null;
        } else {
            return $this->filterBySettings($result);
        }
    }

    public function findById($id, $relationships = [])
    {
        return User::with($relationships)->where('id', $id)->first();
    }

    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function delete($id)
    {
        if ($id !== $this->authRepo->getOwnerId() && $id > 109 || $id === 106) {
            $user = User::with('lastSubscription')->find($id);
            if (!$user->hasRole(['Admin', 'Superadmin'])
            || $user->hasRole(['Admin', 'Superadmin'])
            && $this->authRepo->isOwnerSuperadmin()) {
                $user->update([
                    'email' => $user->email.' '.date("Ymdhis"),
                    'public_id' => null
                ]);
                User::destroy($id);
                $subscription = $user->subscriptions()->first();
                // delete any subscriptions associated with the user
                if (isset($subscription)) {
                    SubscriptionUser::destroy($subscription->id);
                }
                return ['error' => false];
            }
        }
        return ['error' => true];
    }

    public function createOrUpdateCompanyInfo($request)
    {
        $user_id = $this->authRepo->getOwnerId();
        $company = CompanyInfo::updateOrCreate([
            'user_id' => $user_id,
        ], [
            'name' => $request['name'],
            'ein' => $request['ein']
        ]);
        return $company;
    }

    public function getUserCompanyInfo()
    {
        $id = auth()->id();
        return CompanyInfo::where('user_id', $id)->first();
    }

    public function getUsersByCommissionEngineStatus($statusKeyId, $paginate = false)
    {
        $users = User::where('comm_engine_status_id', $statusKeyId)->with('subscriptions');
        if ($paginate) {
            return $users->paginate(100);
        }
        return $users->get();
    }
    /**
     * This is to update the users join date
     *
     * @param $request user_id and join_date
     * @return User
     */
    public function joinDate($request)
    {
        $user = User::where('id', $request['user_id'])->first();
        $user->update(['join_date' => $request['join_date']]);
        return $user;
    }
}
