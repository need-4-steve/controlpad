<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Services\PayMan\EwalletService;
use App\Services\PayMan\PayManService;
use Illuminate\Http\Request;

class EwalletController extends Controller
{
    public function __construct(EwalletService $ewalletService, PayManService $payManService)
    {
        $this->ewalletService = $ewalletService;
        $this->payManService = $payManService;
    }

    /**
    * This gets a report from payman with the sales tax information.
    *
    * @param query string request This is an example
    *    $request = [
    *       'order'     => 'ASC',
    *       'column'    => 'name',
    *       'per_page'  => 15,
    *       'page'      => 1
    *   ];
    * @return \Illuminate\Http\Response
    */
    public function getSalesTax()
    {
        $request = $this->ewalletService->getParams(request()->all());
        $salesTax = $this->ewalletService->salesTax($request);
        $pages = $this->ewalletService->pagination($salesTax, $request);
        return response()->json($pages, HTTP_SUCCESS);
    }

    /**
    * This gets a processing fee report from payman.
    *
    * @return \Illuminate\Http\Response
    */
    public function getProcessingFees()
    {
        $request = $this->ewalletService->getParams(request()->all());
        $fees = $this->ewalletService->processingFees($request);
        $pages = $this->ewalletService->pagination($fees, $request);
        return response()->json($pages, HTTP_SUCCESS);
    }

    /**
    * This gets the need information for the Dashboard from payman.
    *
    */
    public function getDashboardReport()
    {
        $dashboard = [
            'balance' => [
                'pendingSalesTotal' => null,
                'pendingSalesCount' => null,
                'pendingTaxTotal' => null,
                'pendingTaxCount' => null,
                'eWalletBalance' => null,
            ],
            'commission' => [
                'pendingSalesTotal' => null,
                'pendingSalesCount' => null,
                'pendingTaxTotal' => null,
                'pendingTaxCount' => null,
                'eWalletBalance' => null,
            ]
        ];
        if (auth()->user()->hasRole(['Superadmin'])) {
            $dashboard['balance'] = $this->ewalletService->dashboardReport(null, 'Company');
        } elseif (auth()->user()->hasRole(['Rep']) && auth()->user()->hasSellerType(['Reseller'])) {
            if (app('globalSettings')->getGlobal('reseller_ewallet_balance', 'show')) {
                $dashboard['balance'] = $this->ewalletService->dashboardReport(auth()->id(), 'Rep');
            }
            if (app('globalSettings')->getGlobal('reseller_ewallet_commission', 'show')) {
                $dashboard['commission'] = $this->ewalletService->dashboardReport(auth()->id(), 'Company');
            }
        } elseif (auth()->user()->hasRole(['Rep']) && auth()->user()->hasSellerType(['Affiliate'])) {
            if (app('globalSettings')->getGlobal('affiliate_ewallet_balance', 'show')) {
                $dashboard['balance'] = $this->ewalletService->dashboardReport(auth()->id(), 'Rep');
            }
            if (app('globalSettings')->getGlobal('affiliate_ewallet_commission', 'show')) {
                $dashboard['commission'] = $this->ewalletService->dashboardReport(auth()->id(), 'Company');
            }
        }
        return response()->json($dashboard, HTTP_SUCCESS);
    }

    /**
    * This gets the user information for the Dashboard.
    *
    */
    public function userWithAddress()
    {
        return auth()->user()->load('billingAddress');
    }

    /**
    * This is posting a withdraw to postman
    */
    public function postWithdraw(Request $request)
    {
        $validation = $this->validate($request, [
            'total' => 'required|numeric',
            'source' => 'required|in:Rep,Company',
        ]);
        $withdraw = $this->ewalletService->withdraw(request('total'), request('source'));
        return response()->json($withdraw, HTTP_SUCCESS);
    }

    /**
    * This gets the transactions of the ewallet from payman.
    *
    * @param query string request This is an example
    *    $request = [
    *       'order'     => 'ASC',
    *       'column'    => 'name',
    *       'per_page'  => 15,
    *       'page'      => 1
    *   ];
    * @return \Illuminate\Http\Response
    */
    public function getTransactions()
    {
        $request = $this->ewalletService->getParams(request()->all());
        $transactions = $this->ewalletService->transactions($request);
        $pages = $this->ewalletService->pagination($transactions, $request);
        return response()->json($pages, HTTP_SUCCESS);
    }

    /**
    * This gets the payment information for the ewallet from payman.
    *
    * @return \Illuminate\Http\Response
    */
    public function getPayments()
    {
        $request = $this->ewalletService->getParams(request()->all());
        $myPayment = $this->ewalletService->myPayment($request);
        $pages = $this->ewalletService->pagination($myPayment, $request);
        return response()->json($pages, HTTP_SUCCESS);
    }

    public function getLedger()
    {
        $request = $this->ewalletService->getParams(request()->all());
        $ledger = $this->ewalletService->ledger($request);
        $pages = $this->ewalletService->pagination($ledger, $request);
        return response()->json($pages, HTTP_SUCCESS);
    }
    /**
     * gets the cash sales tax ledger
     *
     * @param Array $request
     * @return Json
     */
    public function getCashTax()
    {
        $request = $this->ewalletService->getParams(request()->all());
        $ledger = $this->ewalletService->getCashTaxLedger($request);
        $pages = $this->ewalletService->pagination($ledger, $request);
        return response()->json($pages, HTTP_SUCCESS);
    }

    public function getTransaction($transactionId)
    {
        $transaction = $this->ewalletService->transaction($transactionId);
        return response()->json($transaction, HTTP_SUCCESS);
    }

    public function downloadCsvBalanceLedger()
    {
        $filename = 'balance_ledger.csv';
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header("Cache-control: private");
        header("Content-type: application/force-download");
        header("Content-transfer-encoding: binary\n");

        $out = fopen('php://output', 'w');

        $ledgerData = [
            'Date',
            'Amount',
            'Fees',
            'Sales Tax',
            'Net',
            'Balance'
        ];
        $maxCount = 0;
        $ledgerList = [];
        $request = $this->ewalletService->getParams(request()->all());
        $ledgers = $this->ewalletService->ledger($request);

        foreach ($ledgers['data'] as $ledger) {
            $ledgerList [] = [
                $ledger['date'],
                $ledger['amount'],
                $ledger['fees'],
                $ledger['salesTax'],
                $ledger['net'],
                $ledger['balance']
            ];
        }
        fputcsv($out, $ledgerData);
        foreach ($ledgerList as $ledger) {
            fputcsv($out, $ledger);
        }
        fclose($out);
    }

    public function downloadCsvSaleTaxLedger()
    {
        $filename = 'sale_tax_ledger.csv';
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header("Cache-control: private");
        header("Content-type: application/force-download");
        header("Content-transfer-encoding: binary\n");

        $out = fopen('php://output', 'w');

        $ledgerData = [
            'transactionId',
            'gatewayReferenceId',
            'transactionType',
            'amount',
            'balanceId',
            'fees',
            'salesTax',
            'withdraw',
            'net',
            'processed',
            'date',
            'balance',
            'receipt_id',
            'customer_name',
            'customer_id',
            'address_1',
            'address_2',
            'city',
            'state',
            'zip',
            'order_type',
            'order_subtotal_price',
            'order_total_tax',
            'order_total_shipping',
            'order_total_price',
        ];
        $maxCount = 0;
        $ledgerList = [];
        $request = $this->ewalletService->getParams(request()->all());
        $ledgers = $this->ewalletService->getCashTaxLedger($request, true);
        foreach ($ledgers['data'] as $ledger) {
            $withdraw = 'false';
            if ($ledger['withdraw'] > 0) {
                $ledger['amount'] = $ledger['withdraw'];
                $withdraw = 'true';
            }
            $ledgerList [] = [
                $ledger['transactionId'],
                $ledger['gatewayReferenceId'],
                $ledger['transactionType'],
                $ledger['amount'],
                $ledger['balanceId'],
                $ledger['fees'],
                $ledger['salesTax'],
                $withdraw,
                $ledger['net'],
                $ledger['processed'],
                $ledger['date'],
                $ledger['balance'],
                isset($ledger['order']['receipt_id']) ? $ledger['order']['receipt_id'] : '',
                isset($ledger['order']['customer_name']) ? $ledger['order']['customer_name'] : '',
                isset($ledger['order']['customer_id']) ? $ledger['order']['customer_id']: '',
                isset($ledger['order']['address_1']) ? $ledger['order']['address_1']: '',
                isset($ledger['order']['address_2']) ? $ledger['order']['address_2']:'',
                isset($ledger['order']['city']) ? $ledger['order']['city']:'',
                isset($ledger['order']['state']) ? $ledger['order']['state']:'',
                isset($ledger['order']['zip']) ? $ledger['order']['zip']:'',
                isset($ledger['order']['order_type']) ? $ledger['order']['order_type']:'',
                isset($ledger['order']['subtotal_price']) ? $ledger['order']['subtotal_price']:'',
                isset($ledger['order']['total_tax']) ? $ledger['order']['total_tax']:'',
                isset($ledger['order']['total_shipping']) ? $ledger['order']['total_shipping']:'',
                isset($ledger['order']['total_price']) ? $ledger['order']['total_price']:'',
            ];
        }
        fputcsv($out, $ledgerData);

        foreach ($ledgerList as $ledger) {
            fputcsv($out, $ledger);
        }
        fclose($out);
    }
}
