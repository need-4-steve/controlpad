package com.controlpad.pay_fac.payment_provider;

import com.controlpad.payman_common.payment_provider.PaymentProvider;

public interface PaymentProviderInterface {

    boolean validateCredentials(PaymentProvider paymentProvider);
}
