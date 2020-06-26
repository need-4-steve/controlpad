<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Events\PasswordNewEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserEditRequest;
use App\Http\Requests\UserCreateRequest;
use App\Models\User;
use App\Models\SubscriptionUser;
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\AddressRepository;
use App\Repositories\Eloquent\UserSettingsRepository;
use App\Models\TermsAcceptance;
use App\Services\Payman\PayManService;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use DB;

class UserController extends Controller
{
    protected $userRepo;

    protected $paymanService;

    protected $addressRepo;

    protected $subRepo;

    public function __construct(
        UserRepository $userRepo,
        PaymanService $paymanService,
        SubscriptionRepository $subRepo,
        AddressRepository $addressRepo,
        UserSettingsRepository $userSettingsRepo
    ) {
        $this->userRepo = $userRepo;
        $this->paymanService = $paymanService;
        $this->addressRepo = $addressRepo;
        $this->subRepo = $subRepo;
        $this->settingsService = app('globalSettings');
        $this->userSettingsRepo = $userSettingsRepo;
    }

    /**
     * Get an index of user
     *
     * @return Response
     */
    public function index()
    {
        $users = $this->userRepo->getIndex(request()->all());
        return response()->json($users, HTTP_SUCCESS);
    }

    /**
     * Accept most up to date terms and conditions
     *
     * @return Response
     */
    public function acceptTermsAndConditions()
    {
        // acceptances are tracked just by id and timestamps
        TermsAcceptance::updateOrCreate(
            ['user_id' => auth()->user()->id],
            ['updated_at' => Carbon::now()->toDateTimeString()]
        );
    }


    /**
     * Get user names based on auth
     *
     * @return Response
     */
    public function names()
    {
        $user_names = DB::table('users')
            ->selectRaw("id, CONCAT(`first_name`, ' ', `last_name`) as full_name");

        if (auth()->user()->hasRole(['Rep'])) {
            $user_names->where('sponsor_id', auth()->id());
        }

        return response()->json($user_names->get(), HTTP_SUCCESS);
    }

    /**
     * Create a new user
     *
     * @param UserCreateRequest $request
     * @return Response
     */
    public function create(UserCreateRequest $request)
    {
        $request = $request->all();
        if (isset($request['public_id'])) {
            $request['public_id'] = strtolower($request['public_id']);
        }
        if ($request['role']['id'] == 8 && auth()->user()->role_id !== 8) {
              return response()->json('Not authorized ', HTTP_BAD_REQUEST);
        }
        $messages = $this->userRepo->createNew($request);
        if (is_array($messages) && isset($messages['error'])) {
            return response()->json($messages['error'], HTTP_BAD_REQUEST);
        }
        return response()->json('User created', HTTP_SUCCESS);
    }

    /**
     * Show user
     *
     * @param int id
     * @return Response
     */
    public function show($id)
    {
        if (auth()->user()->hasRole(['Admin', 'Superadmin']) || auth()->user()->id == $id) {
            return response()->json($this->userRepo->find($id));
        } elseif (auth()->check()) {
            return $this->createResponse(true, HTTP_FORBIDDEN, 'Unauthorized', null);
        }
        return $this->createResponse(true, HTTP_UNAUTHORIZED, 'Unauthorized', null);
    }

    /**
     * Get an index of rep users
     *
     * @return Response
     */
    public function reps()
    {
        return User::where('role_id', 5)->get();
    }

    /**
     * Search using search term
     *
     * @return Response
     */
    public function search()
    {
        $searchTerm = request()->get('searchTerm');
        $users = $this->userRepo->search($searchTerm, auth()->user()->role->name, auth()->user()->id);
        return response($users, HTTP_SUCCESS);
    }

    /**
     * Search using search term
     *
     * @return Response
     */
    public function searchReps(Request $request)
    {
        $rules = [
            'searchTerm' => 'string'
        ];
        $this->validate($request, $rules);
        $users = $this->userRepo->searchReps($request['searchTerm'], 'Rep');
        return response()->json($users);
    }

    public function searchSponsors(Request $request)
    {
        $rules = [
            'search_term' => 'string'
        ];
        $this->validate($request, $rules);
        $users = $this->userRepo->searchSponsors($request['search_term']);
        return response()->json($users);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(UserEditRequest $request, $id)
    {
        // insure only admins can edit the sponsor_id
        if (! auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            unset($request['sponsor_id']);
        }
        $user = $this->userRepo->updateUser($request, $id);
        return response()->json($user, HTTP_SUCCESS);
    }

    /**
     * Get account details
     *
     * @param  int $id
     * @return Response
     */
    public function myAccount($id)
    {
        $user = $this->userRepo->find($id, [
            'billingAddress',
            'businessAddress',
            'shippingAddress',
            'subscriptions.subscription.price',
            'profileImage',
            'sponsor',
            'cardToken'
        ]);
        $bank = $this->paymanService->getBanking($id);

        if ($bank || $bank !== null) {
            $bank['number'] = '*****'.substr($bank['number'], -4);
        }

        $subscriptionToken = $this->subRepo->subscriptionToken($id);
        $user->bank = $bank;
        $user->editable = false;
        if ($user->id === auth()->user()->id or
            auth()->user()->id === 109 or
            auth()->user()->hasRole('Superadmin') and $user->id != 1 or
            auth()->user()->hasRole('Superdamin') and $user->id !== 109 or
            auth()->user()->hasRole('Admin') and $user->hasRole('Rep', 'Customer')) {
            $user->editable = true;
        }

        return response()->json($user, HTTP_SUCCESS);
    }

    public function auth()
    {
        $user = auth()->user()->load([
            'billingAddress',
            'shippingAddress'
        ]);
        return response()->json($user, HTTP_SUCCESS);
    }

    public function authId()
    {
        return auth()->user()->id;
    }

    /*
     * Delete a user
     *
     * @param array ids
     * @return Response
     */
    public function delete()
    {
        $ids = request()->get('ids');
        foreach ($ids as $key => $id) {
            $response = $this->userRepo->delete($id);
        }
        if ($response['error']) {
            return response()->json('unauthorized', HTTP_UNAUTHORIZED);
        }
        return response()->json('User deleted.', HTTP_SUCCESS);
    }

    /*
     * Download user csv based on passed ids
     */
    public function downloadCsv()
    {
        $filename = 'users.csv';
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header("Cache-control: private");
        header("Content-type: application/force-download");
        header("Content-transfer-encoding: binary\n");

        $out = fopen('php://output', 'w');

        $userData = [
            'id',
            'first_name',
            'last_name',
            'email',
            'phone',
            'role',
            'join_date',
            'billing_address',
            'address',
            'city',
            'state',
            'zip',
            'shipping_address',
            'address',
            'city',
            'state',
            'zip'
        ];
        if ($this->settingsService->getGlobal('collect_sponsor_id', 'show')) {
            array_push($userData, 'sponsor_id', 'sponsor_name');
        }

        $users = $this->userRepo->getIndex(request()->all(), true);
        $maxCount = 0;

        $userList = [];
        foreach ($users as $user) {
            $userLine = [];

            $userLine[] = $user->id;
            $userLine[] = $user->first_name;
            $userLine[] = $user->last_name;
            $userLine[] = $user->email;
            $userLine[] = $user->phone_number;
            $userLine[] = $user->role->name;
            $userLine[] = $user->join_date;

            // to offset the times when a missing address comes through
            if (isset($user->billingAddress)) {
                $userLine[] = $user->billingAddress->address_1;
                $userLine[] = $user->billingAddress->address_2;
                $userLine[] = $user->billingAddress->city;
                $userLine[] = $user->billingAddress->state;
                $userLine[] = $user->billingAddress->zip;
            } else {
                array_push($userLine, '', '', '', '', '');
            }
            if (isset($user->shippingAddress)) {
                $userLine[] = $user->shippingAddress->address_1;
                $userLine[] = $user->shippingAddress->address_2;
                $userLine[] = $user->shippingAddress->city;
                $userLine[] = $user->shippingAddress->state;
                $userLine[] = $user->shippingAddress->zip;
            } else {
                array_push($userLine, '', '', '', '', '');
            }
            if ($this->settingsService->getGlobal('collect_sponsor_id', 'show')) {
                if ($user->sponsor_id != 0 && $user->sponsor) {
                    $userLine[] = $user->sponsor_id;
                    $userLine[] = $user->sponsor->first_name. " ". $user->sponsor->last_name;
                } else {
                    array_push($userLine, '', '');
                }
            }
            $userList[] = $userLine;
        }

        fputcsv($out, $userData);
        foreach ($userList as $currentUser) {
            fputcsv($out, $currentUser);
        }
        fclose($out);
    }

    public function createCompanyInfo(Request $request)
    {
        $this->validate($request, [
            'ein' => 'sometimes|max:10',
        ]);
        return $this->userRepo->createOrUpdateCompanyInfo(request()->all());
    }

    public function getCompanyInfo()
    {
        return $this->userRepo->getUserCompanyInfo();
    }
    /*
     * update user join_date
     *
     * @param array join_date, user_id
     * @return Response
     */
    public function editJoinDate(Request $request)
    {
        $this->validate($request, ['join_date' => 'required|date']);
        $joinDate = $this->userRepo->joinDate($request->all());
        return response()->json($joinDate, HTTP_SUCCESS);
    }

    public function isAffiliate()
    {
        return response()->json(auth()->user()->hasSellerType(['Affiliate']), HTTP_SUCCESS);
    }
}
