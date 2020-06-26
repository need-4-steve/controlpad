<?php

namespace App\Repositories\Interfaces;

interface TenantInterface {
    public function index();
    public function show($org_id);
    public function showByDomain($domain);
    public function create($name, $read_host, $write_host, $db_name, $domain);
    public function update($org_id, $params);
    public function delete($org_id);
}
