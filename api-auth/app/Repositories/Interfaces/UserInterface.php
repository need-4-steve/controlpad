<?php

namespace App\Repositories\Interfaces;

interface UserInterface {
    public function index();
    public function create($email, $password, $role, $tenant);
    public function update($id, $params);
    public function show($id);
    public function delete($id);

}
