<?php

namespace App\Services\Tax;

use Log;
use Avalara\TransactionBuilder;
use App\Services\TaxServiceInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Repositories\Interfaces\TaxInvoiceInterface;

class AvalaraTaxService extends TaxServiceInterface
{
    // Types pulled from Avalara\DocumentType but couldn't refer to it in const
    const TYPE_MAP = [
        'sale' => 'SalesInvoice',
        'use' => 'PurchaseInvoice',
        'transfer' => 'InventoryTransferInvoice',
        'refund' => 'ReturnInvoice'
    ];
    const EST_MAP = [
        'sale' => 'SalesOrder',
        'use' => 'PurchaseOrder',
        'transfer' => 'InventoryTransferOrder',
        'refund' => 'ReturnOrder'
    ];
    const CODE_MAP = [
        'discount' => 'OD010000',
        'shipping' => 'FR030000',
    ];

    public function setup()
    {
        // Called by parent constructor
        $this->client = new \Avalara\AvaTaxClient(
            'ControlPad Tax API', // Application Name
            '1.0', // Application version
            'localhost', // Machine name
            ($this->taxConnection->sandbox ? 'sandbox' : 'production')
        );
        if (isset($this->taxConnection->credentials->username) &&
            isset($this->taxConnection->credentials->password)) {
            $this->client->withSecurity(
                $this->taxConnection->credentials->username,
                $this->taxConnection->credentials->password
            );
        } elseif (isset($this->taxConnection->credentials->account_id) &&
                  isset($this->taxConnection->credentials->license_key)
        ) {
            $this->client->withLicenseKey(
                $this->taxConnection->credentials->account_id,
                $this->taxConnection->credentials->license_key
            );
        } else {
            Log::error('No tax credentials found', $this->getMetadata());
            throw new HttpException(500, 'No tax credentials found for merchant');
        }
    }

    public function getInvoice($referenceId)
    {
        return $this->client->getTransactionByCode($this->taxConnection->credentials->company_code, $referenceId, null, null);
    }

    public function getEstimate($taxInvoice)
    {
        $t = $this->createTransaction($taxInvoice, AvalaraTaxService::EST_MAP[$taxInvoice['type']]);
        return ['pid' => null, 'tax' => $t->totalTax, 'type' => $taxInvoice['type'], 'estimate' => true];
    }

    public function createInvoice($taxInvoice)
    {
        $t = $this->createTransaction($taxInvoice, AvalaraTaxService::TYPE_MAP[$taxInvoice['type']]);

        $taxInvoice['subtotal'] = $t->totalAmount;
        $taxInvoice['reference_id'] = $t->code;
        $taxInvoice['tax'] = $t->totalTax;
        $taxInvoice['tax_connection_id'] = $this->taxConnection->id;
        if (isset($taxInvoice['commit']) && $taxInvoice['commit']) {
            $taxInvoice['committed_at'] = \Carbon\Carbon::now()->toDateTimeString();
        }

        return $this->taxInvoiceRepo->create($taxInvoice);
    }

    public function commitInvoice($taxInvoice, $orderId = null)
    {
        $t = $this->client->commitTransaction(
            $this->taxConnection->credentials->company_code,
            $taxInvoice['reference_id'],
            AvalaraTaxService::TYPE_MAP[$taxInvoice['type']],
            ['commit' => true]
        );

        if (isset($t->status) && $t->status == "Committed") {
            $taxInvoice['committed_at'] = \Carbon\Carbon::now()->toDateTimeString();
            return $taxInvoice;
        } else {
            Log::error('Commit invoice failed.', $this->appendMetadata($t, $taxInvoice->pid));
            throw new HttpException(500);
        }
    }

    public function commitList()
    {
        // Issue https://git.controlpad.com/web/tax-api/issues/3
        throw new HttpException(405);
    }

    public function refund($taxRefund, $originalInvoice)
    {
        if ($taxRefund['type'] == 'refund-full') {
            $type = 'Full';
        } else {
            $type = 'Partial';
            throw new HttpException(405, "Partial refunds not implemented");
        }
        $requestBody = [
            'refundDate' => (isset($taxRefund['date']) ? $taxRefund['date'] : \Carbon\Carbon::now()->toDateTimeString()),
            'refundType' => $type
        ];
        $t = $this->client->refundTransaction(
            $this->taxConnection->credentials->company_code,
            $originalInvoice->reference_id,
            null,
            AvalaraTaxService::TYPE_MAP[$originalInvoice['type']],
            null,
            $requestBody
        );

        if (!isset($t->id)) {
            Log::error('Refund error', $this->appendMetadata($t, $originalInvoie->pid));
            throw new HttpException(500);
        }
        $taxRefund['subtotal'] = $t->totalAmount;
        $taxRefund['tax_connection_id'] = $this->taxConnection->id;
        $taxRefund['tax'] = $t->totalTax;
        $taxRefund['reference_id'] = $t->code;

        return $this->taxInvoiceRepo->create($taxRefund);
    }

    public function deleteInvoice($invoice)
    {
        // TODO implement later
        return false;
    }

    public function getCredentialValidationArray()
    {
        $validations = ['credentials.company_code' => 'required'];
        $hasUsername = isset($this->taxConnection->credentials->username);
        $hasPassword = isset($this->taxConnection->credentials->password);
        if ($hasUsername | $hasPassword) {
            $validations += ['credentials.username' => 'required|string', 'credentials.password' => 'required|string'];
        } else {
            $validations += ['credentials.account_id' => 'required|string', 'credentials.license_key' => 'required|string'];
        }

        return $validations;
    }

    public function validateCredentials()
    {
        //Looks like only the top user for the account can do this, added users can't access with username/password
        return $this->client->ping()->authenticated;
    }

    public function updateInvoice($taxInvoice)
    {
        throw new HttpException(405);
    }

    public function isAccountSame($taxConnection)
    {
        if (isset($this->taxConnection->credentials->username) &&
            isset($taxConnection['credentials']['username'])) {
                return ($this->taxConnection->credentials->username == $taxConnection['credentials']['username']);
        }
        if (isset($this->taxConnection->credentials->account_id) &&
            isset($taxConnection['credentials']['account_id'])) {
                return ($this->taxConnection->credentials->account_id == $taxConnection['credentials']['account_id']);
        }
        return false;
    }

    private function packAddress($tb, $address, $type)
    {
        $tb->withAddress(
            $type,
            (isset($address['line_1']) ? $address['line_1'] : null),
            (isset($address['line_2']) ? $address['line_2'] : null),
            null,
            (isset($address['city']) ? $address['city'] : null),
            (isset($address['state']) ? $address['state'] : null),
            $address['zip'], // Always required
            (isset($address['country']) ? $address['country'] : 'US')
        );
    }

    private function packLine($tb, $line)
    {
        if (isset($line['tax_code'])) {
            $code = $line['tax_code'];
        } elseif (isset($line['type'])) {
            $code = AvalaraTaxService::CODE_MAP[$line['type']];
        } else {
            $code = 'P0000000';
        }
        $tb->withLine(
            $line['subtotal'],
            $line['quantity'],
            (isset($line['sku']) ? $line['sku'] : null),
            $code,
            (isset($line['pid']) ? $line['pid'] : null)
        );
    }

    private function createTransaction($taxInvoice, $type)
    {
        $tb = new TransactionBuilder(
            $this->client,
            $this->taxConnection->credentials->company_code,
            $type,
            (isset($taxInvoice['customer_id']) ? $taxInvoice['customer_id'] : 'none')
            // Customer code can be set inside the portal, could be something other than user_id
        );
        if (isset($taxInvoice['single_location'])) {
            $this->packAddress($tb, $taxInvoice['single_location'], \Avalara\TransactionAddressType::C_SINGLELOCATION);
        } else {
            if (isset($taxInvoice['to_address'])) {
                $this->packAddress($tb, $taxInvoice['to_address'], \Avalara\TransactionAddressType::C_SHIPTO);
            }
            if (isset($taxInvoice['from_address'])) {
                $this->packAddress($tb, $taxInvoice['from_address'], \Avalara\TransactionAddressType::C_SHIPFROM);
            }
        }

        foreach ($taxInvoice['line_items'] as $line) {
            $this->packLine($tb, $line);
        }

        if (isset($taxInvoice['commit']) && $taxInvoice['commit']) {
            $tb->withCommit();
        }

        $t = $tb->create();

        if (!isset($t->totalTax)) { // a tax amount was calculated
            Log::error('Create transaction error', $this->appendMetadata($t));
            throw new HttpException(500);
        }
        return $t;
    }

    protected function getMetadata()
    {
        return [
            'service_type' => 'avalara',
            'tax_connection_id' => $this->taxConnection->id
        ];
    }
}
