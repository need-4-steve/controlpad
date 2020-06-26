<?php

use Illuminate\Database\Seeder;
use App\Repositories\Eloquent\V0\ApiKeyRepository;
use App\Utilities\V0\ApiKeyUtilities;
use App\Service;
use App\Tenant;
use App\ApiKey;
use CPCommon\Pid\Pid;

class TestServiceKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Create a new tenant
        $tenant = new Tenant;
        $tenant->name = env('TEST_TENANT_NAME', 'Asgard');
        $tenant->org_id = Pid::create();
        $tenant->read_host = env('TEST_TENANT_READ_HOST', 'localhost');
        $tenant->write_host = env('TEST_TENANT_WRITE_HOST', 'localhost');
        $tenant->db_name = env('TEST_TENANT_DB_NAME', 'homestead');
        $tenant->status = "Paid";
        $tenant->domain = env('TEST_DOMAIN', 'core.local');
        $tenant->save();

        //Create a new service
        $service = new Service;
        $service->name = env('TEST_SERVICE_NAME', "Orders");
        $service->save();
        // DB::table('services')->insert([
        //     "name" => "Orders"
        // ]);
        $apiKeyRepo = new ApiKeyRepository;
        $app_keys = ApiKeyUtilities::generateKey(env('TEST_KEY_NAME', 'THOR'), $tenant->id);
        $key = $apiKeyRepo->create($tenant->id, env('TEST_KEY_NAME', 'THOR'), $app_keys['app_id'], $app_keys['secret']);
        $apiKeyRepo->addKeyService($key->id, $service->id);
        echo "Test application created using secret key ".$key->secret."\n";
        echo "Service Id for test application is: ".$service->id."\n";
    }
}
