<?php

use Illuminate\Database\Seeder;
use App\Repositories\Eloquent\V0\ApiKeyRepository;
use App\Utilities\V0\ApiKeyUtilities;
use App\ApiKey;

class AddApiKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $apiKeyRepo = new ApiKeyRepository;
        $app_keys = ApiKeyUtilities::generateKey("Orders", 1);
        $key = $apiKeyRepo->create(1, "Orders", $app_keys['app_id'], $app_keys['secret']);
        $apiKeyRepo->addKeyService($key->id, 1);
        echo "Application Key Created using secret key ".$key
            ->secret;
    }
}
