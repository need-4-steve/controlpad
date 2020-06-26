<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\Address;
use App\Repositories\Contracts\AuthRepositoryContract;

class AuthRepository implements AuthRepositoryContract
{
    /**
     * Get an owner based on logged in user; for admin users this will always
     * return the apex user
     *
     * @return App\Models\User;
     */
    public function getOwner()
    {
        if (empty(auth()->user())) {
            return null;
        }

        if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            return User::find(config('site.apex_user_id'));
        }
        return auth()->user();
    }

    /**
     * Get an owner based on logged in user; for admin users this will always
     * return the apex user id
     *
     * @return int
     */
    public function getOwnerId()
    {
        if (empty(auth()->user())) {
            return null;
        }

        if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            return config('site.apex_user_id');
        }
        return auth()->user()->id;
    }

    /**
     * Get an owner seller type based on logged in user.
     *
     * @return int
     */
    public function getSellerType()
    {
        if (empty(auth()->user())) {
            return null;
        }

        if (isset(auth()->user()->sellerType) and auth()->user()->hasRole(['Rep'])) {
            return auth()->user()->sellerType->name;
        }
        return null;
    }

    /**
     * Determine if the user has adminstrative access
     *
     * @return int
     */
    public function isAdmin()
    {
        if (empty(auth()->user())) {
            return false;
        }
        if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            return true;
        }
        return false;
    }

    /**
     * Get the store owner in session
     *
     * @return App\Models\User;
     */
    public function getStoreOwner()
    {
        if (session()->has('store_owner')) {
            return session('store_owner');
        }
        return User::find(config('site.apex_user_id'));
    }

    /**
     * Get the store owner ID in session
     *
     * @return bool
     */
    public function getStoreOwnerId()
    {
        if (session()->has('store_owner')) {
            return session('store_owner')->id;
        }
        return config('site.apex_user_id');
    }

    /**
     * Check an object's owner user ID to see if the auth user owns it
     *
     * @param int $object_owner_id
     * @return bool
     */
    public function getOwnedByAuthUser($object_owner_id)
    {
        $owner_id = $this->getOwnerId();
        if (! ($owner_id == $object_owner_id || auth()->user()->hasRole(['Admin', 'Superadmin']))) {
            return false;
        }
        return true;
    }

    /**
     * Attempt to login
     *
     * @param int $object_owner_id
     * @return bool
     */
    public function login(array $request) : bool
    {
        $attempt = auth()->attempt([
            'email' => $request['email'],
            'password' => $request['password']
        ], false);

        return $attempt;
    }

    /**
     * Check to see if the logged in user is an admin
     *
     * @return bool
     */
    public function isOwnerAdmin()
    {
        if (empty(auth()->user())) {
            return null;
        }

        if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            return true;
        }

        return false;
    }

    /**
     * Check to see if the logged in user is a superadmin
     *
     * @return bool
     */
    public function isOwnerSuperadmin()
    {
        if (empty(auth()->user())) {
            return null;
        }

        if (auth()->user()->hasRole(['Superadmin'])) {
            return true;
        }

        return false;
    }

    /**
     * Check to see if the logged in user is a rep
     *
     * @return bool
     */
    public function isOwnerRep()
    {
        if (empty(auth()->user())) {
            return null;
        }

        if (auth()->user()->hasRole(['Rep'])) {
            return true;
        }

        return false;
    }

    public function getCorporateBusinessAddress()
    {
        return Address::where('addressable_id', config('site.apex_user_id'))
            ->where('addressable_type', User::class)
            ->where('label', 'Business')
            ->first();
    }
}
