<?php namespace Test\MockServices;

use App\Services\PaymanServiceInterface;
use CPCommon\Pid\Pid;

class MockPaymanService implements PaymanServiceInterface
{

    public function authorizePayment($teamId, $payeeUserId, $payerUserId, $payment, $tax, $shipping, $discount, $orderPid, $affiliatePayouts)
    {
        return (object) [
            'id' => Pid::create(),
            'type' => $payment['type'],
            'gatewayReferenceId' => Pid::create(),
            'resultCode' => 1,
            'statusCode' => 'A'
        ];
    }

    public function captureTransaction($transactionId, $receiptId)
    {
        return (object) [
            'id' => $transactionId,
            'orderId' => $receiptId,
            'type' => 'credit-card-sale',
            'resultCode' => 1,
            'statusCode' => 'P'
        ];
    }

    public function cancelTransaction($transactionId, $amount)
    {
        // Do nothing
    }
}
