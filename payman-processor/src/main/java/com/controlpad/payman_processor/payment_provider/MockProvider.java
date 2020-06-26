package com.controlpad.payman_processor.payment_provider;

import com.controlpad.payman_common.merchant.Merchant;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.payment_provider.PaymentProvider;
import org.apache.commons.lang3.StringUtils;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

public class MockProvider implements PaymentProviderInterface {

    private static final Logger logger = LoggerFactory.getLogger(MockProvider.class);

    @Override
    public Payment createPayment(PaymentProvider paymentProvider, Payment payment, Merchant merchant) {
        if (merchant == null || StringUtils.isBlank(merchant.getEmail())) {
            // Skip user if not yet invited, this should probably not happen in our flow though
            logger.error("Merchant email not found during payquicker payout | UserId {}", payment.getUserId());
            return null;
        }
        payment.setReferenceId("fake-id");
        return payment;
    }
}
