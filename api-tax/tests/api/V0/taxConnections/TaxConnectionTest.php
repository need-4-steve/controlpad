<?php

use App\Models\TaxConnection;

class TaxConnectionTest extends TestCase
{

    private $indexJsonStructure = [
        'current_page',
        'data',
        'data' => [
            '*' => [
                'id',
                'type',
                'merchant_id',
                'active',
                'sandbox',
                'created_at',
                'updated_at',
            ]
        ],
        'first_page_url',
        'next_page_url',
        'prev_page_url',
        'path',
        'from',
        'to'
    ];

    private $singleStructure = [
        'id',
        'type',
        'merchant_id',
        'active',
        'sandbox',
        'created_at',
        'updated_at',
    ];

    public function testIndexFilter()
    {
        $url = '/api/v0/tax-connections';
        $merchantId = 'connectionIndexFilter';
        $activeConnection = factory(App\Models\TaxConnection::class)
            ->create(['merchant_id' => $merchantId, 'active' => true]);
        $disabledConnection = factory(App\Models\TaxConnection::class)
            ->create(['merchant_id' => $merchantId]);
        $notMockConnection = factory(App\Models\TaxConnection::class)
            ->create(['type' => 'exactor', 'merchant_id' => $merchantId]);
        $notMerchantConnection = factory(App\Models\TaxConnection::class)
            ->create(['merchant_id' => 'not'.$merchantId, 'active' => true]);

        // Find all for merchant and verify structure
        $response = $this->basicRequest('GET', $url, ['page' => 1, 'merchant_id' => $merchantId]);

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->indexJsonStructure);
        $response->seeJson(['current_page' => 1, 'from' => 1, 'to' => 3]);

        // Find just the active connection
        $response = $this->basicRequest('GET', $url, ['page' => 1, 'merchant_id' => $merchantId, 'active' => true]);
        $response->seeJson(['to' => 1, 'id' => $activeConnection->id]);

        // Find the not mock connection
        $response = $this->basicRequest('GET', $url, ['page' => 1, 'merchant_id' => $merchantId, 'type' => 'exactor']);
        $response->seeJson(['to' => 1, 'id' => $notMockConnection->id]);
    }

    public function testIndexPagination()
    {
        $url = '/api/v0/tax-connections';
        $merchantId = 'connectionIndexPaginate';
        $taxConnections = factory(App\Models\TaxConnection::class, 8)
            ->create(['merchant_id' => $merchantId, 'active' => true]);

        $response = $this->basicRequest('GET', $url, ['page' => 2, 'per_page' => 3]);

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->indexJsonStructure);
        $response->seeJson(['current_page' => 2, 'from' => 4, 'to' => 6]);
    }

    public function testCreate()
    {
        // Create an already active connection to override
        $currentConnection = factory(App\Models\TaxConnection::class)->create(['merchant_id' => 'default', 'active' => true]);

        $newConnection = ['merchant_id' => 'default', 'active' => true, 'sandbox' => true, 'type' => 'mock', 'credentials' => ['api_key' => 'somefakekey']];
        $response = $this->basicRequest('POST', '/api/v0/tax-connections', $newConnection);
        $response->seeStatusCode(201);
        $response->seeJsonStructure($this->singleStructure);

        $responseBody = json_decode($response->response->getContent());
        $newConnection['id'] = $responseBody->id;
        $newConnection['type'] = $responseBody->type;
        unset($newConnection['credentials']);  // This is encrypted in the database

        $this->seeInDatabase('tax_connections', $newConnection);
        // Make sure old connection was disabled
        $this->seeInDatabase('tax_connections', ['id' => $currentConnection->id, 'active' => false]);
    }

    public function testShow()
    {
        $taxConnection = factory(App\Models\TaxConnection::class)->create();
        $response = $this->basicRequest('GET', '/api/v0/tax-connections/'.$taxConnection->id);

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->singleStructure);
        $response->seeJson($taxConnection->toArray());
    }

    public function testUpdate()
    {
        $taxConnection = factory(App\Models\TaxConnection::class)->create();
        $newCredentials = ['api_key' => 'adifffakekey'];
        $updateConnection = ['merchant_id' => 'fakeid', 'active' => true, 'type' => 'exactor', 'credentials' => $newCredentials];
        $response = $this->basicRequest('PATCH', '/api/v0/tax-connections/'.$taxConnection->id, $updateConnection);
        // Verify that only active and credentials can change
        $response->seeStatusCode(200);
        $response->seeJson([
            'merchant_id' => $taxConnection->merchant_id,
            'active' => $updateConnection['active'],
            'type' => $taxConnection->type
        ]);
        $savedConnection = App\Models\TaxConnection::where('id', $taxConnection->id)->first();
        assert($savedConnection->credentials->api_key == $newCredentials['api_key']);
        assert($savedConnection->active == true);
    }
}
