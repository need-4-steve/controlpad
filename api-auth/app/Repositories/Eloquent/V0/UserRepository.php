<?php

namespace App\Repositories\Eloquent\V0;

use App\Repositories\Interfaces\UserInterface;
use App\User;

class UserRepository implements UserInterface {

    public function index() {
        return User::simplePaginate(200);
    }

    public function show($id) {
        return User::where('id', $id)->first();
    }

    public function create($email, $password, $role, $tenant_id) {
        $user = new User;
        $user->email = $email;
        $user->password = app('hash')->make($password);
        $user->role = $role;
        $user->tenant_id = $tenant_id;
        $user->save();
        return $user;
    }

    public function update($id, $params) {
        return User::where('id', $id)->update($params);
    }

    public function delete($id) {
        return User::where('id', $id)->delete();
    }


}
