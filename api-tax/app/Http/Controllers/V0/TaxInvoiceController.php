<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Models\TaxConnection;
use App\Models\TaxInvoice;
use App\Repositories\Interfaces\TaxInvoiceInterface;
use Illuminate\Http\Request;

class TaxInvoiceController extends Controller
{
    public function __construct(
        TaxInvoiceInterface $taxInvoiceInterface
    ) {
        parent::__construct();
        $this->taxInvoiceRepo = $taxInvoiceInterface;
    }

    public function index(Request $request)
    {
        $this->validate($request, ['page' => 'required|integer']);
        return response()->json($this->taxInvoiceRepo->index($request->only(TaxInvoice::$indexParams)));
    }

    public function show(Request $request, $pid)
    {
        $invoice = $this->taxInvoiceRepo->show($pid);
        if (!$invoice) {
            abort(404, 'Invoice not found');
        }
        if (filter_var($request->input('transaction', false), FILTER_VALIDATE_BOOLEAN)) {
            $taxConnection = TaxConnection::where('id', $invoice->tax_connection_id)->first();

            $taxService = $taxConnection->getService($this->taxInvoiceRepo);
            $transactionData = $taxService->getInvoice($invoice->reference_id);
            $invoice->transaction = $transactionData;
        }
        return response()->json($invoice);
    }

    public function create(Request $request)
    {
        if ($request->input('type') === 'refund') {
            // Support refunding through create
            return $this->refund($request);
        }
        if ($request->input('type') === 'refund-full') {
            // Support full refund through create
            return $this->refundFull($request);
        }
        $this->validate($request, TaxInvoice::$createRules);
        $requestBody = $request->only(TaxInvoice::$createFields);
        if (isset($requestBody['commit']) && $requestBody['commit']) {
            // Only admins can commit
            $request->user->assertAnyRole(['Superadmin', 'Admin']);
        }

        // Try to find merchant connection
        $taxConnection = TaxConnection::where('merchant_id', $requestBody['merchant_id'])
                    ->where('active', true)->orderBy('created_at', 'DESC')->first();

        // Check if we should pull a default connection
        if ($taxConnection == null &&
            (!$request->has('allow_default_connection') ||
            filter_var($request->input('allow_default_connection'), FILTER_VALIDATE_BOOLEAN))) {
                // Find default connection
                $taxConnection = TaxConnection::where('merchant_id', 'default')
                    ->where('active', true)->orderBy('created_at', 'DESC')->first();
        }

        if ($taxConnection == null) {
            return response()->json(['error' => 'No tax service'], 400);
        }

        $taxService = $taxConnection->getService($this->taxInvoiceRepo);

        if ($request->has('estimate') && filter_var($request->input('estimate'), FILTER_VALIDATE_BOOLEAN)) {
            return response()->json($taxService->getEstimate($requestBody));
        } else {
            return response()->json($taxService->createInvoice($requestBody), 201);
        }
    }

    public function update(Request $request, $pid)
    {
        $this->validate($request, TaxInvoice::$updateRules);
        $requestBody = $request->only(TaxInvoice::$updateFields);
        $taxInvoice = $this->taxInvoiceRepo->show($pid);
        if ($taxInvoice == null) {
            return response()->json(['error' => 'No invoice found for id: ' . $pid], 404);
        }
        $taxConnection = TaxConnection::where('id', $taxInvoice->tax_connection_id)->first();

        $taxService = $taxConnection->getService($this->taxInvoiceRepo);

        $taxService->updateInvoice($taxInvoice);
        return response()->json($taxInvoice);
    }

    public function refund(Request $request, $pid = null)
    {
        $request->user->assertAnyRole(['Superadmin', 'Admin']);
        if (!$request->has('origin_pid')) {
            $request['origin_pid'] = $pid;
            $request['type'] = 'refund';
        }
        $this->validate($request, TaxInvoice::$refundRules);
        $requestBody = $request->only(TaxInvoice::$refundFields);
        $originalInvoice = $this->taxInvoiceRepo->show($requestBody['origin_pid']);
        if ($originalInvoice == null) {
            return response()->json(['error' => 'No invoice found for id: ' . $requestBody['origin_pid']], 404);
        } else {
            if ($originalInvoice->committed_at == null) {
                return response()->json(['error' => 'cannot refund taxes until committed'], 400);
            }
            // Refunds are stored with negative value
            $refundedAmount = $this->taxInvoiceRepo->getTotalRefunded($requestBody['origin_pid']);
            if ($refundedAmount != null) {
                // A refund exists
                if ($originalInvoice['subtotal'] == 0.00 || ($originalInvoice['subtotal'] + $refundedAmount == 0)) {
                    // zero amount invoices can only refund one time, or fully refunded
                    return response()->json(['error' => 'Already refunded'], 400);
                }
                $remaining = ($originalInvoice->subtotal + $refundedAmount);
            } else {
                $remaining = $originalInvoice->subtotal;
            }
            if ($requestBody['subtotal'] > $remaining) {
                // Don't refund more than original subtotal
                return response()->json(['subtotal' => ['subtotal too high.']], 422);
            }
        }
        $taxConnection = TaxConnection::where('id', $originalInvoice->tax_connection_id)->first();

        $taxService = $taxConnection->getService($this->taxInvoiceRepo);

        $requestBody['merchant_id'] = $originalInvoice->merchant_id;
        return response()->json($taxService->refund($requestBody, $originalInvoice), 201);
    }

    public function refundFull(Request $request, $pid = null)
    {
        $request->user->assertAnyRole(['Superadmin', 'Admin']);
        if (!$request->has('origin_pid')) {
            $request['origin_pid'] = $pid;
            $request['type'] = 'refund-full';
        }
        $this->validate($request, TaxInvoice::$refundRules);
        $requestBody = $request->only(TaxInvoice::$refundFields);
        $originalInvoice = $this->taxInvoiceRepo->show($requestBody['origin_pid']);
        if ($originalInvoice == null) {
            return response()->json(['error' => 'No invoice found for id: ' . $requestBody['origin_pid']], 404);
        }
        // Refunds are stored with negative value
        $refundedAmount = $this->taxInvoiceRepo->getTotalRefunded($requestBody['origin_pid']);

        if ($refundedAmount != null) {
            // A refund exists
            return response()->json(['error' => 'Already refunded'], 400);
        }

        $taxConnection = TaxConnection::where('id', $originalInvoice->tax_connection_id)->first();

        if ($originalInvoice->committed_at == null) {
            return response()->json(['error' => 'cannot refund taxes until committed'], 400);
        }
        $taxService = $taxConnection->getService($this->taxInvoiceRepo);
        $requestBody['merchant_id'] = $originalInvoice->merchant_id;
        return response()->json($taxService->refund($requestBody, $originalInvoice), 201);
    }

    public function commit(Request $request, $pid)
    {
        $request->user->assertAnyRole(['Superadmin', 'Admin']);
        $taxInvoice = $this->taxInvoiceRepo->show($pid);
        if (!$taxInvoice) {
            return response()->json(['error' => 'No invoice found for id: ' . $pid, 404]);
        }
        if ($taxInvoice['committed_at'] != null) {
            return response()->json($taxInvoice);
        }
        if ($request->has('order_pid')) {
            $taxInvoice['order_pid'] = $request->input('order_pid');
        }
        $taxConnection = TaxConnection::where('id', $taxInvoice->tax_connection_id)->first();

        $taxService = $taxConnection->getService($this->taxInvoiceRepo);
        $taxService->commitInvoice($taxInvoice, $request->input('order_id'));

        $taxInvoice->save();
        return response()->json($taxInvoice, 200);
    }

    public function delete(Request $request, $pid)
    {
        $request->user->assertAnyRole(['Superadmin', 'Admin']);
        $taxInvoice = $this->taxInvoiceRepo->show($pid);
        if (!$taxInvoice) {
            return response()->json(['error' => 'No invoice found for id: ' . $pid, 404]);
        }
        // For now we won't auto refund invoices, maybe later though
        $taxConnection = TaxConnection::where('id', $taxInvoice->tax_connection_id)->first();
        if ($taxConnection->type !== 'tax-jar' && $taxInvoice['committed_at'] != null) {
            return response()->json(['error' => 'Cannot delete commited invoice'], 400);
        }
        $taxService = $taxConnection->getService($this->taxInvoiceRepo);
        $taxService->deleteInvoice($taxInvoice);
        $taxInvoice->delete();
    }
}
