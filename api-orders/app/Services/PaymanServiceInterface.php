<?php

namespace App\Services;

interface PaymanServiceInterface
{
    public function authorizePayment($teamId, $payeeUserId, $payerUserId, $payment, $tax, $shipping, $discount, $orderPid, $affiliatePayouts);
    public function captureTransaction($transactionId, $receiptId);
    public function cancelTransaction($transactionId, $amount);
}
