package com.controlpad.pay_fac.validation;

import com.controlpad.pay_fac.payment_info.CardPayment;
import com.controlpad.pay_fac.util.CardValidationUtil;
import org.apache.commons.lang3.StringUtils;

import javax.validation.ConstraintValidator;
import javax.validation.ConstraintValidatorContext;

public class CardPaymentValidator implements ConstraintValidator<CardPaymentValidate, CardPayment> {

    @Override
    public void initialize(CardPaymentValidate constraintAnnotation) {

    }

    @Override
    public boolean isValid(CardPayment cardPayment, ConstraintValidatorContext context) {
        if (cardPayment.getCardToken() != null) {
            return true;
        }
        if (cardPayment.getCardMagstripe() != null || cardPayment.getCardEncMagstripe() != null) {
            return true;
        }
        if (cardPayment.getCardNonce() != null) {
            return true;
        }

        if(cardPayment.getCard() == null){
            boolean valid = true;
            if (cardPayment.getCardNumber() == null || cardPayment.getCardExpiration() == null) {
                context.buildConstraintViolationWithTemplate("cardToken or cardMagstripe or cardNonce or cardExpiration and cardNumber are required").addConstraintViolation();
                valid = false;
            }
            if (!CardValidationUtil.isValidCardNumber(cardPayment.getCardNumber())) {
                context.buildConstraintViolationWithTemplate("cardNumber invalid").addConstraintViolation();
                valid = false;
            }
            return valid;
        }
        return true;
    }

    private boolean checkCardCode(CardPayment cardPayment, ConstraintValidatorContext context) {
        if (StringUtils.isBlank(cardPayment.getCardCode())) {
            context.buildConstraintViolationWithTemplate("cardCode required").addConstraintViolation();
            return false;
        }
        return true;
    }
}
