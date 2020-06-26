<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Repositories\EloquentV0\PlanRepository;
use App\Repositories\EloquentV0\SettingRepository;
use App\Repositories\EloquentV0\StoreSettingRepository;
use App\Repositories\EloquentV0\SubscriptionRepository;
use App\Repositories\EloquentV0\UsersRepository;
use App\Rules\RepEmail;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UsersController extends Controller
{
    private $usersRepo;

    public function __construct()
    {
        $this->usersRepo = new UsersRepository;
    }

    public function index(Request $request) : JsonResponse
    {
        $request['date_column'] = $request->input('date_column', 'created_at');
        $request['per_page'] = $request->input('per_page', '50');
        $this->validate($request, User::$indexRules);
        $params = $request->all();
        return response()->json($this->usersRepo->index($params));
    }

    public function findById(Request $request, $id) : JsonResponse
    {
        $this->validate($request, User::$findRules);
        $user = $this->usersRepo->find($request->all(), $id, 'id');
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json($user, 200);
    }

    public function findByPid(Request $request, $pid)
    {
        $this->validate($request, User::$findRules);
        $user = $this->usersRepo->find($request->all(), $pid, 'pid');
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json($user, 200);
    }

    public function findByEmail(Request $request, string $email)
    {
        $this->validate($request, User::$findRules);
        $user = $this->usersRepo->find($request->all(), $email, 'email');
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json($user, 200);
    }

    public function findByPublicID(Request $request, string $publicID)
    {
        $this->validate($request, User::$findRules);
        $user = $this->usersRepo->find($request->all(), $publicID, 'public_id');
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json($user, 200);
    }

    public function findByIdAndEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email', 'id' => 'required|numeric|min:1']);
        $user = $this->usersRepo->find([], $request['email'], 'email');
        if (!$user || $user->id != $request['id']) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json($user, 200);
    }

    public function create(Request $request)
    {
        $rules = User::$createRules;
        if ($request->input('role_id') == 5) {
            $rules['email'] = ['required', 'email', new RepEmail];
        }
        if ($request->has('public_id')) {
            $request['public_id'] = strtolower($request->input('public_id'));
        }
        $this->validate($request, $rules, [
            'public_id.unique' => 'The :attribute has already been taken or blacklisted.',
            'password.required_if' => 'The :attribute field is required for this role.'
        ]);
        $request['password'] = $request->has('password') ? app('hash')->make($request->input('password')) : '';
        // If something went wrong in creating the user, rollback so the user can reuse email and public_id.
        try {
            app('db')->beginTransaction();
            $user = $this->usersRepo->createOrUpdate($request->all());
            if ($request->input('role_id') == 5) {
                $planRepo = new PlanRepository;
                $plan = $planRepo->find([], $request->input('plan_pid'));
                $subscriptionRepo = new SubscriptionRepository;
                $subscription = $subscriptionRepo->create($user, $plan);
                $storeSettingRepo = new StoreSettingRepository;
                $storeSettings = $storeSettingRepo->create($user);
                $user->load('rawStoreSettings', 'settings', 'subscription');
                $this->usersRepo->mapStoreSettings([$user]);
            }
            if ($user->role_id !== 3) {
                $settingRepo = new SettingRepository;
                $settings = $settingRepo->create($user);
            }
        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('error creating user', ['stack_trace' => $e, 'finger_print' => 'error creating user']);
            return response()->json($e->getMessage(), 500);
        }
        app('db')->commit();
        return response()->json($user, 200);
    }

    public function update(Request $request, $pid) : JsonResponse
    {
        $this->validate($request, User::updateRules($pid));
        if ($request->has('password')) {
            $request['password'] = app('hash')->make($request->input('password'));
        }
        app('db')->beginTransaction();
        $user = $this->usersRepo->createOrUpdate($request->only(User::$updateFields), $pid);
        app('db')->commit();
        return response()->json($user);
    }

    public function updateStatus(Request $request) : JsonResponse
    {
        $this->validate($request, [
            'status' => 'required|exists:user_status,name',
            'user_pids' => 'required|array',
            'user_pids.*' => 'required|string|exists:users,pid',
        ]);
        $response = $this->usersRepo->updateStatus($request->input('user_pids'), $request->input('status'));
        return response()->json($response, 200);
    }

    public function delete(Request $request, $pid) : JsonResponse
    {
        $deleted = $this->usersRepo->delete($pid);
        return response()->json($deleted);
    }

    public function getCardToken(Request $request, $pid)
    {
        $cardToken = $this->usersRepo->getCardToken($pid);
        if ($cardToken === null) {
            abort(404, 'Token missing');
        }
        return response()->json($cardToken);
    }
}
