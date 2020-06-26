<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Repositories\EloquentV0\UsersRepository;
use Illuminate\Http\Request;
use App\Customer;
use App\User;

class CustomerController extends Controller
{
    private $usersRepo;

    private $sortableColumns = [
        'id',
        'first_name',
        'last_name',
        'email',
        'role_id',
        'join_date'
    ];

    public function __construct()
    {
        $this->usersRepo = new UsersRepository;
    }

    private function determineSortByOrder(Request $request) : Request
    {
        if ($request->get('sort_by') && strpos($request->get('sort_by'), '-') === 0) {
            $request->merge(['sort_by' => str_replace('-', '', $request['sort_by'])]);
            $request->merge(['in_order' => 'desc']);
        }
        return $request;
    }

    public function create(Request $request)
    {
        $this->validate($request, Customer::$createRules);
        $userId = $request->input('user_id');
        $customerInput = $request->input('customer');
        if (!isset($customerInput['phone_number'])) {
            // Optional field
            $customerInput['phone_number'] = null;
        }
        if (!$request->user->hasRole(['Superadmin', 'Admin']) && (!$request->user->hasRole(['Rep']) || $request->user->id !== $userId)) {
            abort(403, 'Admin or Seller only.');
        }
        // Create customer if needed, we can't create a duplicate email so find trash
        $customer = $this->usersRepo->createOrUpdateCustomer($customerInput);
        // Automatically attach customer to seller
        $this->performAttach($userId, $customer->id);
        return response()->json($customer);
    }

    public function attach(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'customer_id' => 'required'
        ]);
        $userId = $request->input('user_id');
        $customerId = $request->input('customer_id');
        if (!$request->user->hasRole(['Superadmin', 'Admin']) && (!$request->user->hasRole(['Rep']) || $request->user->id !== $userId)) {
            abort(403, 'Admin or Seller only.');
        }
        $customer = $this->usersRepo->find([], $customerId, 'id');

        if (!$customer) {
            return response()->json([
                'error' => 'Customer not found'
            ], 404);
        }
        $this->performAttach($userId, $customerId);

        return response()->json($customer);
    }

    public function attachByPid(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'customer_pid' => 'required'
        ]);
        $userId = $request->input('user_id');
        if (!$request->user->hasRole(['Superadmin', 'Admin']) && (!$request->user->hasRole(['Rep']) || $request->user->id !== $userId)) {
            abort(403, 'Admin or Seller only.');
        }
        $customer = $this->usersRepo->find([], $request->input('customer_pid'), 'pid');

        if (!$customer) {
            return response()->json([
                'error' => 'Customer not found'
            ], 404);
        }
        // Make sure customer is attached
        $this->performAttach($userId, $customer->id);

        return response()->json($customer);
    }

    private function performAttach($userId, $customerId)
    {
        // Checks to see if the customer has already bought from the store owner.
        $attachment = Customer::where('user_id', '=', $userId)->where('customer_id', '=', $customerId)->first();
        // If the customer has not previously bought, it associates them to the seller.
        if (!$attachment) {
            // Table currently isn't constrained as of 2018-11-18!! but going forward this will be race safe
            app('db')->statement('INSERT INTO customers(user_id, customer_id) VALUES(?, ?) ON DUPLICATE KEY UPDATE customer_id = customer_id', [$userId, $customerId]);
        }
    }

    public function search(Request $request)
    {
        if ($request->user->hasRole(['Superadmin', 'Admin'])) {
            $sellerId = null;
        } elseif ($request->user->hasRole(['Rep'])) {
            $sellerId = $request->user->id;
        } else {
            abort(403, 'Sellers only');
        }
        $request['per_page'] = $request->input('per_page', '100');
        $this->validate($request, User::$indexRules);
        return response()->json($this->usersRepo->index($request->all(), $sellerId));
    }
}
