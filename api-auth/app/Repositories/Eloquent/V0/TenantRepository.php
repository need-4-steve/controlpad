<?php

namespace App\Repositories\Eloquent\V0;

use App\Repositories\Interfaces\TenantInterface;
use App\Tenant;
use CPCommon\Pid\Pid;

class TenantRepository implements TenantInterface {

    public function index()
    {
        return Tenant::simplePaginate(200);
    }

    public function show($org_id)
    {
        return Tenant::where('org_id', $org_id)->first();
    }

    public function showByDomain($domain)
    {
        return Tenant::where('domain', $domain)->first();
    }

    public function create($name, $read_host, $write_host, $db_name, $domain)
    {
        $tenant = new Tenant;
        $tenant->name = $name;
        $tenant->read_host = $read_host;
        $tenant->write_host = $write_host;
        $tenant->db_name = $db_name;
        $tenant->status = 'Paid';
        $tenant->org_id = Pid::create();
        $tenant->domain = $domain;
        $tenant->save();
        return $tenant;
    }

    public function update($org_id, $params)
    {
        return Tenant::where('org_id', $org_id)->update($params);
    }

    public function delete($org_id)
    {
        return Tenant::where('org_id', $org_id)->delete();
    }
}
