<?php

use App\Models\TaxInvoice;

class TaxInvoiceTest extends TestCase
{
    private $indexJsonStructure = [
        'current_page',
        'data',
        'data' => [
            '*' => [
                'pid',
                'type',
                "merchant_id",
                "subtotal",
                "tax",
                "created_at",
                "updated_at",
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
        'pid',
        'type',
        "merchant_id",
        "subtotal",
        "tax",
        "created_at",
        "updated_at",
    ];

    public function testIndexFilter()
    {
        $url = '/api/v0/tax-invoices';
        $merchantId = "IndexMerchant";
        $referenceId = "1234567890";
        $orderId = "Order-1";
        $defaultTaxConnection = factory(App\Models\TaxConnection::class)->create(['merchant_id' => 'default']);
        $merchantTaxConnection = factory(App\Models\TaxConnection::class)->create(['merchant_id' => $merchantId]);

        $defaultInvoice = factory(App\Models\TaxInvoice::class)
            ->create(['merchant_id' => $merchantId, 'tax_connection_id' => $defaultTaxConnection->id]);
        $merchantInvoice = factory(App\Models\TaxInvoice::class)
            ->create(['merchant_id' => $merchantId, 'tax_connection_id' => $merchantTaxConnection->id]);
        $merchantRefundInvoice = factory(App\Models\TaxInvoice::class)
            ->create(['merchant_id' => $merchantId, 'tax_connection_id' => $merchantTaxConnection->id,
             'origin_pid' => $merchantInvoice->pid, 'type' => 'refund']);
        $referenceInvoice = factory(App\Models\TaxInvoice::class)
            ->create(['merchant_id' => 'OtherMerchant', 'tax_connection_id' => $defaultTaxConnection->id, 'reference_id' => $referenceId]);
        $orderInvoice = factory(App\Models\TaxInvoice::class)
            ->create(['merchant_id' => $merchantId, 'tax_connection_id' => $merchantTaxConnection->id, 'order_pid' => $orderId]);
        $notCommittedInvoice = factory(App\Models\TaxInvoice::class)
            ->create(['merchant_id' => $merchantId, 'tax_connection_id' => $merchantTaxConnection->id, 'committed_at' => null]);

        // Find all for merchant and verify structure
        $response = $this->basicRequest('GET', $url, ['page' => 1, 'merchant_id' => $merchantId]);

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->indexJsonStructure);
        $response->seeJson(['current_page' => 1, 'from' => 1, 'to' => 5]);
        $response->seeJson(['pid' => $defaultInvoice->pid]);
        $response->seeJson(['pid' => $merchantInvoice->pid]);
        $response->seeJson(['pid' => $merchantRefundInvoice->pid]);
        $response->seeJson(['pid' => $orderInvoice->pid]);
        $response->seeJson(['pid' => $notCommittedInvoice->pid]);

        // Find for tax connection
        $response = $this->basicRequest('GET', $url, ['page' => 1, 'tax_connection_id' => $defaultTaxConnection->id]);

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->indexJsonStructure);
        $response->seeJson(['current_page' => 1, 'from' => 1, 'to' => 2]);
        $response->seeJson(['pid' => $defaultInvoice->pid]);
        $response->seeJson(['pid' => $referenceInvoice->pid]);

        // Find by reference id
        $response = $this->basicRequest('GET', $url, ['page' => 1, 'reference_id' => $referenceId]);

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->indexJsonStructure);
        $response->seeJson(['current_page' => 1, 'from' => 1, 'to' => 1]);
        $response->seeJson(['pid' => $referenceInvoice->pid]);

        // Find by type
        $response = $this->basicRequest('GET', $url, ['page' => 1, 'type' => 'refund']);

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->indexJsonStructure);
        $response->seeJson(['current_page' => 1, 'from' => 1, 'to' => 1]);
        $response->seeJson(['pid' => $merchantRefundInvoice->pid]);

        // Find by order id
        $response = $this->basicRequest('GET', $url, ['page' => 1, 'order_pid' => $orderId]);

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->indexJsonStructure);
        $response->seeJson(['current_page' => 1, 'from' => 1, 'to' => 1]);
        $response->seeJson(['pid' => $orderInvoice->pid]);

        // Find by committed
        $response = $this->basicRequest('GET', $url, ['page' => 1, 'committed' => "true"]);

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->indexJsonStructure);
        $response->seeJson(['current_page' => 1, 'from' => 1, 'to' => 5]);
        $response->seeJson(['pid' => $defaultInvoice->pid]);
        $response->seeJson(['pid' => $merchantInvoice->pid]);
        $response->seeJson(['pid' => $merchantRefundInvoice->pid]);
        $response->seeJson(['pid' => $orderInvoice->pid]);
        $response->seeJson(['pid' => $referenceInvoice->pid]);

        // Find not committed
        $response = $this->basicRequest('GET', $url, ['page' => 1, 'committed' => "false"]);

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->indexJsonStructure);
        $response->seeJson(['current_page' => 1, 'from' => 1, 'to' => 1]);
        $response->seeJson(['pid' => $notCommittedInvoice->pid]);
    }

    public function testIndexPagination()
    {
        $url = '/api/v0/tax-invoices';
        $merchantId = 'InvoiceIndexPaginate';
        $taxConnection = factory(App\Models\TaxConnection::class)->create();
        $taxInvoices = factory(App\Models\TaxInvoice::class, 8)
            ->create(['tax_connection_id' => $taxConnection->id, 'merchant_id' => $taxConnection->merchant_id]);

        $response = $this->basicRequest('GET', $url, ['page' => 2, 'per_page' => 3]);

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->indexJsonStructure);
        $response->seeJson(['current_page' => 2, 'from' => 4, 'to' => 6]);
    }

    public function testSale()
    {
        $url = '/api/v0/tax-invoices';
        $merchantId = 'InvoiceSale';
        $taxConnection = factory(App\Models\TaxConnection::class)->create(['active' => true]);

        $requestBody = [
            'to_address' => [
                'state' => 'NY',
                'zip' => '10001'
            ],
            'from_address' => [
                'state' => 'NY',
                'zip' => '10001'
            ],
            'line_items' => [
                [
                    'subtotal' => 10.00,
                    'quantity' => 1
                ]
            ],
            'merchant_id' => $taxConnection->merchant_id,
            'type' => 'sale',
            'commit' => true
        ];

        $response = $this->basicRequest('POST', $url, $requestBody);

        $response->seeStatusCode(201);
        $response->seeJsonStructure($this->singleStructure);
        $response->seeInDatabase('tax_invoices', json_decode($response->response->content(), true));
    }

    public function testEstimate()
    {
        $url = '/api/v0/tax-invoices?estimate=true';
        $merchantId = 'InvoiceEstimate';
        $taxConnection = factory(App\Models\TaxConnection::class)->create(['active' => true]);

        $requestBody = [
            'to_address' => [
                'state' => 'NY',
                'zip' => '10001'
            ],
            'from_address' => [
                'state' => 'NY',
                'zip' => '10001'
            ],
            'merchant_id' => $taxConnection->merchant_id,
            'line_items' => [
                [
                    'subtotal' => 10.00,
                    'quantity' => 1
                ]
            ],
            'type' => 'sale',
            'commit' => true
        ];

        $response = $this->basicRequest('POST', $url, $requestBody);

        $response->seeStatusCode(200);
        $response->seeJsonStructure(['type', 'tax']);
        $response->seeJson(['pid' => null, 'estimate' => true]);
    }

    public function testFullRefund()
    {
        $merchantId = 'InvoiceRefundTester';
        $taxConnection = factory(App\Models\TaxConnection::class)->create(['active' => true]);
        $taxInvoice = factory(App\Models\TaxInvoice::class)
            ->create(['tax_connection_id' => $taxConnection->id, 'merchant_id' => $taxConnection->merchant_id]);

        $url = '/api/v0/tax-invoices';

        $requestBody = [
            'type' => 'refund-full',
            'origin_pid' => $taxInvoice->pid
        ];
        $response = $this->basicRequest('POST', $url, $requestBody);

        $response->seeStatusCode(201);
        $response->seeJsonStructure($this->singleStructure);
        // Refunds save as negative value, make sure a full refund is all tax
        $response->seeJson(['tax' => (-1 * $taxInvoice->tax)]);
        $response->seeInDatabase('tax_invoices', json_decode($response->response->content(), true));
    }

    public function testShow()
    {
        $merchantId = 'InvoiceShowTester';
        $taxConnection = factory(App\Models\TaxConnection::class)->create(['active' => true]);
        $taxInvoice = factory(App\Models\TaxInvoice::class)
            ->create(['tax_connection_id' => $taxConnection->id, 'merchant_id' => $taxConnection->merchant_id]);

        $response = $this->basicRequest('GET', '/api/v0/tax-invoices/' . $taxInvoice->pid);

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->singleStructure);
        $response->seeJson(json_decode(json_encode($taxInvoice), true));
    }

    public function testUpdate()
    {
        $merchantId = 'InvoiceUpdateTester';
        $taxConnection = factory(App\Models\TaxConnection::class)->create(['active' => true]);
        $taxInvoice = factory(App\Models\TaxInvoice::class)
            ->create(
                [
                    'tax_connection_id' => $taxConnection->id,
                    'merchant_id' => $taxConnection->merchant_id,
                    'committed_at' => null
                ]
            );
        $this->basicRequest('PATCH', '/api/v0/tax-invoices/' . $taxInvoice->pid, [])->seeStatusCode(405);
    }

    public function testCommit()
    {
        $merchantId = 'InvoiceCommitTester';
        $taxConnection = factory(App\Models\TaxConnection::class)->create(['active' => true]);
        $taxInvoice = factory(App\Models\TaxInvoice::class)
            ->create(
                [
                    'tax_connection_id' => $taxConnection->id,
                    'merchant_id' => $taxConnection->merchant_id,
                    'committed_at' => null
                ]
            );

        $url = '/api/v0/tax-invoices/' . $taxInvoice->pid . '/commit';

        $response = $this->basicRequest('POST', $url);

        $response->seeStatusCode(200);
        assert(TaxInvoice::where('pid', $taxInvoice->pid)->first()->committed_at != null);
    }
}
