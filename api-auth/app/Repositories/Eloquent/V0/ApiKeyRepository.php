<?php

namespace App\Repositories\Eloquent\V0;

use App\Repositories\Interfaces\APIKeyInterface;
use App\ApiKey;
use App\KeyService;
use App\Tenant;

class ApiKeyRepository implements APIKeyInterface {

    public function index($tenant, $admin=false) {
        if (!$admin) {
            return ApiKey::where('tenant_id', $tenant)->with('keyServices')->simplePaginate(200);
        }
        return ApiKey::with('keyServices')->simplePaginate(200);
    }

    public function create($tenant_id, $app_name, $app_id, $secret){
        $api_key = new ApiKey;
        $api_key->secret = $secret;
        $api_key->app_id = $app_id;
        $api_key->app_name = $app_name;
        $api_key->tenant_id = $tenant_id;
        $api_key->expires_at = date("Y-m-d", strtotime("+6 month"));

        $api_key->save();
        return $api_key;
    }

    public function update($app_id, $tenant, $params, $admin=false) {
        $key = ApiKey::where('app_id', $app_id);
        if (!$admin) {
            $key->where('tenant_id', $tenant);
        }
        $key = $key->update($params);
        return ApiKey::where('app_id', $app_id)->first();
    }

    public function show($app_id, $tenant, $admin=false) {
        $key = ApiKey::where('app_id', $app_id);
        if (!$admin) {
            $key = $key->where('tenant_id', $tenant)->with('keyServices');
        }
        return $key->with('keyServices')->first();
    }

    public function delete($app_id, $tenant, $admin=false) {
        $key = ApiKey::where('app_id', $app_id);
        if(!$admin) {
            $key = $key->where('tenant_id', $tenant);
        }
        return $key->delete();
    }

    public function authenticate($secret, $service_id)
    {
        return ApiKey::select('api_keys.id', 'api_keys.app_id', 'tenants.org_id', 'tenants.name', 'tenants.read_host', 'tenants.write_host', 'tenants.db_name')
            ->join('tenants', 'tenants.id', '=', 'api_keys.tenant_id')
            ->join('key_services', 'key_services.api_key', '=', 'api_keys.id')
            ->join('services', 'services.id', '=', 'key_services.service_id')
            ->where('api_keys.secret', $secret)
            ->whereNull('api_keys.deleted_at')
            ->where('tenants.status', 'Paid')
            ->where('services.id', $service_id)
            ->first();
    }

    public function authenticateJWT($orgId)
    {
        return Tenant::select('tenants.id', 'tenants.read_host', 'tenants.write_host', 'tenants.db_name')
            ->where('tenants.org_id', $orgId)
            ->where('tenants.status', 'Paid')
            ->first();
    }

    public function addKeyService($key_id, $service_id)
    {
        $key_service = new KeyService;
        $key_service->api_key = $key_id;
        $key_service->service_id = $service_id;
        $key_service->save();
    }

    public function resetKeySercices($key_id)
    {
        KeyService::where('api_key', $key_id)->delete();
    }

    public function getKeyServices($key_id) {
        return KeyService::select('services.id', 'services.name')
            ->where('api_key', $key_id)
            ->join('services', 'key_services.service_id', '=', 'services.id')
            ->get();
    }


}
