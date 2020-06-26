<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\APIKeyInterface;
use App\Repositories\Interfaces\TenantInterface;
use App\Utilities\V0\ApiKeyUtilities;
use Tymon\JWTAuth\JWTAuth;
use App\ApiKey;

class ApiKeyController extends Controller
{
    protected $tenantRepo;

    public function __construct(APIKeyInterface $ApiKeyRepo, JWTAuth $jwt, TenantInterface $tenantRepo)
    {
        $this->ApiKeyRepo = $ApiKeyRepo;
        $this->tenantRepo = $tenantRepo;
        $this->jwt = $jwt;
    }

    public function index()
    {
        $user = $this->jwt->parseToken()->toUser();
        if ($user) {
            $admin = ($user->role == 'admin') ? true : false;
            return $this->ApiKeyRepo->index($user->tenant_id, $admin);
        }
        return response()->json('Could not find tenant information. Please contact your account manager', 401);
    }

    public function show($app_id)
    {
        $user = $this->jwt->parseToken()->toUser();
        if ($user) {
            $admin = ($user->role == 'admin') ? true : false;
            $key = $this->ApiKeyRepo->show($app_id, $user->tenant_id, $admin);
            if (is_null($key)) {
                return response()->json('Could not find that api key', 404);
            }
            return response()->json($key);
        }
        return response()->json('Could not find tenant information. Please contact your account manager', 401);
    }

    public function create(Request $request)
    {
        $this->validate($request, ['app_name' => 'required', 'services' => 'sometimes|array', 'services.*' => 'integer']);
        $user = $this->jwt->parseToken()->toUser();
        if ($user) {
            $tenant = ($user->role == 'admin' &&  $request->has('tenant_id')) ? $request->input('tenant_id') : $user->tenant_id;
            $app_keys = ApiKeyUtilities::generateKey($request->input('app_name'), $tenant);
            $apiKey = $this->ApiKeyRepo->create($tenant, $request->input('app_name'), $app_keys['app_id'], $app_keys['secret']);
            if ($request->has('services')) {
                foreach ($request->input('services') as $service) {
                    try {
                        $this->ApiKeyRepo->addKeyService($apiKey->id, $service);
                    } catch (\Exception $e) {
                        // TODO :: add rollbar for failed keyservice insert
                    }
                }
            }
            return ['key' => $apiKey, 'services' => $this->ApiKeyRepo->getKeyServices($apiKey->id), 'secret' => $apiKey->secret];
        }

        return response()->json('Could not find tenant information. Please contact your account manager', 401);
    }

    public function update($app_id, Request $request)
    {
        $this->validate($request, ApiKey::$updateRules);
        $user = $this->jwt->parseToken()->toUser();
        if ($user) {
            $admin = ($user->role == 'admin') ? true : false;
            $params = $request->only(ApiKey::$updateFields);
            if ($request->has('refresh') && $request->input('refresh') == true) {
                $app_keys = ApiKeyUtilities::refreshKey($app_id);
                $params = array_merge($params, $app_keys);
            }
            $updated = $this->ApiKeyRepo->update($app_id, $user->tenant_id, $params, $admin);
            if (!is_null($updated) && $request->has('services')) {
                $this->ApiKeyRepo->resetKeySercices($updated->id);
                foreach ($request->input('services') as $service){
                    try {
                        $this->ApiKeyRepo->addKeyService($updated->id, $service);
                    } catch (\Exception $e) {
                        // TODO :: add rollbar for failed keyservice update
                    }
                }
            }
            if (is_null($updated) < 1) {
                return response()->json("Could not find that api key", 404);
            }
            if ($request->has('refresh') && $request->input('refresh') == true){
                return response()->json(['key' => $updated, 'services' => $this->ApiKeyRepo->getKeyServices($updated->id), 'secret' => $updated->secret]);
            }
            return response()->json(['key' => $updated, 'services' => $this->ApiKeyRepo->getKeyServices($updated->id)]);
        }
        return response()->json('Could not find tenant information. Please contact your account manager', 401);
    }

    public function delete($app_id) {
        $user = $this->jwt->parseToken()->toUser();
        if ($user) {
            $admin = ($user->role == 'admin') ? true : false;
            return $this->ApiKeyRepo->delete($app_id, $user->tenant_id, $admin);
        }
    }


    public function auth(Request $request)
    {
        $this->validate($request, [
            'key' => 'sometimes',
            'service' => 'sometimes',
            'token' => 'sometimes'
        ]);
        if ($encryptedToken = $request->input('token')) {
            $this->jwt->setToken($request->input('token'));
            try {
                $token = $this->jwt->getPayload();
                if (isset($token['orgId']) && $tenant = $this->ApiKeyRepo->authenticateJWT($token['orgId'])) {
                    $tenant['jwtToken'] = $token;
                    return response()->json($tenant, 200);
                }
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $jwtException) {
                // We don't need to log jwt exceptions
                return response()->json($jwtException->getMessage(), 401);
            } catch (\Exception $e) {
                // Unexpected exceptions are a server failure
                \Log::error($e->getMessage());
                return response()->json($e->getMessage(), 500);
            }
        }
        $key = $this->ApiKeyRepo->authenticate($request->input('key'), $request->input('service'));
        if (is_null($key)) {
            return response()->json('Unauthorized', 401);
        }
        //TODO: log app_id
        return response()->json($key);
    }

    private function parseDomain($domain)
    {
        $domain = parse_url($domain);
        $domain = explode(".", $domain['host']);
        $domain = $domain[count($domain)-2] . "." . $domain[count($domain)-1];
        return $domain;
    }

    public function findTenantByDomain(Request $request)
    {
        $this->validate(
            $request,
            ['domain' => 'required|url'],
            ['domain' => 'A domain name is required.']
        );
        $domain = $this->parseDomain($request->get('domain'));
        $tenant = $this->tenantRepo->showByDomain($domain);
        if (!$tenant) {
            abort(403);
        }
        return response()->json($tenant);
    }
}
