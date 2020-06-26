package com.controlpad.payman_processor.payment_provider;


import com.controlpad.payman_common.merchant.Merchant;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.payment_provider.PaymentProvider;

public interface PaymentProviderInterface {

    Payment createPayment(PaymentProvider paymentProvider, Payment payment, Merchant merchant);
}
