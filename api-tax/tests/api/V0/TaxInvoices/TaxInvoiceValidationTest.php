<?php

use App\Models\TaxInvoice;

class TaxInvoiceValidationTest extends TestCase
{

    public function testCreateValidation()
    {
        // Post an empty body
        $response = $this->basicRequest('POST', '/api/v0/tax-invoices', []);
        $response->seeStatusCode('422');
        // Verify that minimal rules return as errors
        $response->seeJsonStructure(['to_address', 'type', 'merchant_id', 'line_items', 'commit']);

        // Verify that extended rules apply
        $response = $this->basicRequest(
            'POST',
            '/api/v0/tax-invoices',
            [
                'type' => 'sale',
                'merchant_id' => 'testmerchant',
                'to_address' => [
                    'line_1' => '123 Main st'
                ],
                'from_address' => [
                    'line_1' => '123 Main st'
                ],
                'line_items' => [[]],
                'subtotal' => 10.00,
                'commit' => false
            ]
        );
        $response->seeStatusCode('422');
        $response->seeJsonStructure(
            [
                'to_address.state',
                'from_address.state',
                'to_address.zip',
                'from_address.zip',
                'line_items.0.quantity',
                'line_items.0.subtotal'
            ]
        );

        // Test invalid type
        $requestBody = [
            'to_address' => [
                'state' => 'NY',
                'zip' => '10001'
            ],
            'from_address' => [
                'state' => 'NY',
                'zip' => '10001'
            ],
            'type' => 'notrealtype',
            'merchant_id' => 'SomeMerchant',
            'subtotal' => 10.00,
            'commit' => true
        ];
        $response = $this->basicRequest('POST', '/api/v0/tax-invoices', $requestBody);
        $response->seeStatusCode('422');
        $response->seeJson(['type' => ['The selected type is invalid.']]);
    }

    public function testNoDefaultConnection()
    {
        // Test invalid type
        $requestBody = [
            'single_location' => [
                'state' => 'NY',
                'zip' => '10001'
            ],
            'type' => 'sale',
            'merchant_id' => 'InvalidMerchant',
            'line_items' => [
                [
                    'subtotal' => 10.00,
                    'quantity' => 1
                ]
            ],
            'commit' => true,
            'allow_default_connection' => false
        ];
        $response = $this->basicRequest('POST', '/api/v0/tax-invoices', $requestBody);
        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'No tax service']);
    }
}
