package com.controlpad.pay_fac.validation;

import com.controlpad.pay_fac.payment_info.TokenRequest;

import javax.validation.ConstraintValidator;
import javax.validation.ConstraintValidatorContext;

public class CardTokenizationValidator implements ConstraintValidator<CardTokenizationValidate, TokenRequest> {
    @Override
    public void initialize(CardTokenizationValidate cardTokenizationValidate){

    }

    @Override
    public boolean isValid(TokenRequest tokenRequest, ConstraintValidatorContext context){
        if (tokenRequest.getCardNonce() == null && tokenRequest.getCard() == null) {
            context.buildConstraintViolationWithTemplate("cardNonce or card required").addConstraintViolation();
            return false;
        }
        return true;
    }
}
