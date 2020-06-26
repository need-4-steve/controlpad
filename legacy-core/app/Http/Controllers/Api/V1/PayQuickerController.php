<?php

namespace App\Http\Controllers\Api\V1;

use Auth;
use App\Http\Controllers\Controller;
use App\Services\PayMan\PayManService;
use App\Services\PayMan\PaymentService;
use App\Services\Csv\CsvService;
use App\Models\PayQuickerToken;
use Illuminate\Pagination\LengthAwarePaginator;

class PayQuickerController extends Controller
{
    public function __construct(PayManService $payman, PaymentService $paymentService, CsvService $csvService)
    {
        $this->payman = $payman;
        $this->payment = $paymentService;
        $this->createCsv = $csvService;
    }

    /**
    * Generate a new invite link or redirect to payquicker website
    *
    * @return \Illuminate\Http\Response
    */
    public function invite()
    {
        $user = Auth::user();

        // no invite made, lets call paymanservice and generate a new one
        $invitationResponse = $this->payman->generatePayQuickerInvite($user);
        if (isset($invitationResponse['error'])) {
            return response()->json('Error creating a token: ' . $invitationResponse['error'], 422);
        }

        return response()->json([
            'invitationUrl' => $invitationResponse['invitationUrl']
        ]);
    }

    /**
    * get the payment list for payquicker.
    *
    */
    public function getPaymentLists($paginate = true)
    {
        $request = request()->all();
        $paymentList = $this->payment->paymentsList($request);
        if (!$paginate) {
            $paginatedSearchResults = $paymentList['data'];
        } else {
            $paginatedSearchResults = new LengthAwarePaginator(
                $paymentList['data'],
                $paymentList['total'],
                $request['per_page'],
                $paymentList['currentPage']
            );
        }
        return $paginatedSearchResults;
    }

    /**
    * get the batch payment info.
    *
    */
    public function getPayment()
    {
        return $this->payment->payments(request()->all());
    }
    /**
    * this is to push the payment info in payman to pay quicker.
    *
    */
    public function submitPayment($batch_id)
    {
        return $this->payment->submitPayment($batch_id);
    }
    /**
    * this is to get the payment info for each batch.
    *
    */
    public function paymentDetails($batch_id, $paginate = true)
    {
        $request = request()->all();
        $details = $this->payment->payments($batch_id, $request);
        if (!$paginate) {
            $paginatedSearchResults = $details['data'];
        } else {
            $paginatedSearchResults = new LengthAwarePaginator(
                $details['data'],
                $details['total'],
                $request['per_page'],
                $details['currentPage']
            );
        }
        return $paginatedSearchResults;
    }

    public function downloadCsvPayQuickerPaymentList()
    {
        $request = request()->all();
        $fileName = 'paymentList';

        $paymentHeader = [
            'createdAt',
            'id',
            'netAmount',
            'paymentCount',
            'status',
            'submittedAt'
        ];
        $payments = $this->getPaymentLists(false);
        $csv = $this->createCsv->createCSVDownload($fileName, $paymentHeader, $payments);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }
    public function downloadCsvPayQuickerDetail($batch_id)
    {
        $request = request()->all();
        $fileName = 'paymentDetail';

        $paymentHeader = [
            'accountId',
            'amount',
            'created_at',
            'id',
            'name',
            'paidAt',
            'paymentBatchId',
            'paymentFileId',
            'referenceId',
            'returned',
            'type',
            'userId'
        ];
        $payments = $this->paymentDetails($batch_id, false);
        $csv = $this->createCsv->createCSVDownload($fileName, $paymentHeader, $payments);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }
}
