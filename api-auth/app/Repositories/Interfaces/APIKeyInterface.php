<?php

namespace App\Repositories\Interfaces;

interface APIKeyInterface {
    public function index($tenant, $admin);
    public function create($tenant_id, $app_name, $app_id, $secret);
    public function update($app_id, $tenant, $admin);
    public function show($app_id, $tenant, $admin);
    public function delete($id, $tenant);
    public function authenticate($secret, $service_id);
    public function addKeyService($key_id, $service_id);
}
