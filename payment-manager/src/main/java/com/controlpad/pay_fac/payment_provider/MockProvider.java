package com.controlpad.pay_fac.payment_provider;

import com.controlpad.payman_common.payment_provider.PaymentProvider;

public class MockProvider implements PaymentProviderInterface {
    @Override
    public boolean validateCredentials(PaymentProvider paymentProvider) {
        return true;
    }
}
