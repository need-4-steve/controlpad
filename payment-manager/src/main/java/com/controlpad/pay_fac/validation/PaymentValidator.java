package com.controlpad.pay_fac.validation;


import com.controlpad.payman_common.transaction.Payment;
import org.springframework.stereotype.Component;
import org.springframework.validation.Errors;
import org.springframework.validation.Validator;

import java.math.BigDecimal;

@Component
public class PaymentValidator implements Validator {

    BigDecimal minimumPayment = new BigDecimal("0.01");

    @Override
    public boolean supports(Class<?> clazz) {
        return Payment.class.isAssignableFrom(clazz);
    }

    @Override
    public void validate(Object target, Errors errors) {
        Payment payment = (Payment)target;
        if (payment.getTotal() == null) {
            errors.reject("3", "subtotal or total is required");
        } else if (payment.getTotal().compareTo(minimumPayment) < 0) {
            errors.reject("71", "total must be at least $0.01");
        }
        if (payment.getAffiliatePayouts() != null) {
            BigDecimal availableAmount = payment.getTotal();
            if (payment.getTax() != null) {
                availableAmount = availableAmount.subtract(payment.getTax());
            }
            if (payment.getShipping() != null) {
                availableAmount = availableAmount.subtract(payment.getShipping());
            }
            if (payment.getTotalAffiliatePayoutAmount()
                    .compareTo(availableAmount) > 0) {
                errors.reject("3", "affiliate payout amounts total cannot be greater than payment subtotal");
            }
        }
    }
}
