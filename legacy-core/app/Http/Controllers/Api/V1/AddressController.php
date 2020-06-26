<?php namespace App\Http\Controllers\Api\V1;

use Response;
use Input;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Repositories\Eloquent\AddressRepository;
use App\Repositories\Eloquent\AuthRepository;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;

class AddressController extends Controller
{
    public function __construct(AddressRepository $addressRepo, AuthRepository $authRepo)
    {
        $this->addressRepo = $addressRepo;
        $this->authRepo = $authRepo;
    }

    /**
    * This is to create an new address.
    */
    public function postCreate()
    {
        $inputs = request()->all();
        if (!isset($inputs['addressable_id'])) {
            $inputs['addressable_id'] = auth()->id();
        }
        return $this->addressRepo->create($inputs);
    }

    /**
     * Update an address or create a new one.
     * User needs to be logged in for function to work.
     *
     * @param AddressRequest $request
     * @return Address $address
     */
    public function postCreateOrUpdate(AddressRequest $addressRequest)
    {
        $request = $addressRequest->all();
        $ownerId = $this->authRepo->getOwnerId();

        if (!isset($request['addressable_id'])) {
            $request['addressable_id'] = null;
        }

        $address = $this->addressRepo->findByAddressable($request['addressable_id'], $request['label'], $request['addressable_type']);

        // Checks to see if the auth user owns the address to edit it.
        if ($address and $address->addressable_type === User::class and $address->addressable_id === $ownerId) {
            $address = $this->addressRepo->update($address, $request);
            return response()->json($address, 200);
        }

        // Checks to see if the store owner owns the order to edit the ship to address.
        if ($address and $address->addressable_type === Order::class) {
            $order = $address->addressable()->first();
            if ($order->store_owner_user_id === $ownerId and $address->label === 'Shipping') {
                $address = $this->addressRepo->update($address, $request);
                return response()->json($address, 200);
            }
        }

        // If an address was found but the user doesn't have authorization to change it.
        if (isset($address)) {
            return response()->json($this->messages['Unauthorized'], 403);
        }

        // if no address was found it creates a new address for the auth user.
        $request['addressable_id'] = $ownerId;
        $request['addressable_type'] = User::class;
        $address = $this->addressRepo->create($request);
        return response()->json($address, 200);
    }

    /**
    * This is to show an new address.
    */
    public function getShow()
    {
        $request = request()->all();
        if (!isset($request['addressable_id'])) {
            $request['addressable_id'] = $this->authRepo->getOwnerId();
        }

        $address = $this->addressRepo->findByAddressable($request['addressable_id'], $request['label'], $request['addressable_type']);

        if (!$address) {
            return response()->json('No address Found.', 404);
        }

        return response()->json($address, 200);
    }
}
