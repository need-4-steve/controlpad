<?php

namespace App\Http\Controllers\Controlpad\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\UserRepository;
use App\Models\RegistrationToken;
use App\Http\Requests\RegistrationTokenCreateRequest;
use App\Http\Requests\UserCreateRequest;
use Validator;

class UserController extends Controller
{
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Update a user in our system
     * @param token  (matches user we are updating, used to grab id)
     *
     * @param email
     * @param password
     * @param phone
     *                 'number',
     *                 'label' (Business, Shipping, Billing)
     * @param shipping_address
     * @param billing_address
     * @param business_address
     *   All above addresses have:
     *                 'name',
     *                 'address_1',
     *                 'address_2',
     *                 'city',
     *                 'state',
     *                 'zip'
     * @param role
     */
    public function update()
    {
        try {
            $request = request()->all();
            $user = $this->userRepo->findByRegistrationToken($request['token']);
            if (!empty($user)) {
                $user = $this->userRepo->updateUser($request, $user->id);
                return $user;
            }
        } catch (Exception $e) {
            return "Error: " . $e;
        }
    }

    /**
     * Create a new user
     *
     * @param UserCreateRequest $request
     * @return JsonResponse
     */
    public function createFull(Request $request)
    {
        try {
            $rules = [
                'first_name' => 'required',
                'last_name' => 'required',
                'password' => 'required|min:8',
                'role' => 'required',
                'email' => 'required|required|email|unique:users,email',
                'public_id' => 'sometimes|unique:users,public_id',
                'address_1' => 'sometimes',
                'address_2' => 'sometimes',
                'city' => 'sometimes',
                'state' => 'sometimes',
                'zip' => 'sometimes|digits_between:5,10',
                'phone.number' => 'sometimes|min:10|max:11'
            ];
            $val = Validator::make($request->all(), $rules);
            if ($val->fails()) {
                return $val->messages();
            }
            $request = $request->all();
            $messages = $this->userRepo->createNew($request);
            if (is_array($messages) && isset($messages['error'])) {
                return response()->json($messages['error'], HTTP_BAD_REQUEST);
            }
            return response()->json('User created', HTTP_SUCCESS);
        } catch (Exception $e) {
            return "Error: " . $e;
        }
    }
}
