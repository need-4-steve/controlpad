<?php

use App\Models\TaxConnection;

class TaxConnectionValidationTest extends TestCase
{

    public function testCreateValidation()
    {
        // Post an empty body
        $response = $this->basicRequest('POST', '/api/v0/tax-connections', []);
        // Check that merchant_id, type, active, credentials is required
        $response->seeStatusCode('422');
        $response->seeJsonStructure(array_keys(TaxConnection::$createRules));

        // Check fake type name rejected
        $newConnection = ['merchant_id' => 'default', 'active' => true, 'type' => 'notrealtype', 'credentials' => ['apikey' => 'somefakekey']];
        $response = $this->basicRequest('POST', '/api/v0/tax-connections', $newConnection);
        $response->seeStatusCode('422');
        $response->seeJson(['type' => ['The selected type is invalid.']]);

        // Test mock credentials validation
        $newConnection['credentials'] = ['username' => 'derp'];
        $newConnection['type'] = 'mock';
        $newConnection['sandbox'] = true;
        $response = $this->basicRequest('POST', '/api/v0/tax-connections', $newConnection);
        $response->seeStatusCode('422');
        $response->seeJson(['credentials.api_key' => ['The credentials.api key field is required.']]);
    }

    public function testUpdateValidation()
    {
        // Attempt to change account
        $connection = factory(App\Models\TaxConnection::class)
            ->create(['active' => true]);

        $response = $this->basicRequest(
            'PATCH',
            '/api/v0/tax-connections/' . $connection->id,
            ['credentials' => ['api_key' => 'differentaccount']]
        );
        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'Account must be the same for update']);

        // Attempt to add invalid credentials
        $response = $this->basicRequest(
            'PATCH',
            '/api/v0/tax-connections/' . $connection->id,
            ['credentials' => ['api_key' => 'invalidkey']]
        );
        $response->seeStatusCode(422);
        $response->seeJson(['credentials' => ['Invalid']]);

        // Attempt to activate an invalid account
        $invalidConnection = factory(App\Models\TaxConnection::class)
            ->create(['credentials' => ['api_key' => 'invalidkey']]);

        $response = $this->basicRequest(
            'PATCH',
            '/api/v0/tax-connections/' . $invalidConnection->id,
            ['active' => true]
        );

        $response->seeStatusCode(422);
        $response->seeJson(['credentials' => ['Invalid']]);
    }
}
