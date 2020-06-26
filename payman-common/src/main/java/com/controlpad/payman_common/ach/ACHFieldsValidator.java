package com.controlpad.payman_common.ach;

import com.controlpad.payman_common.util.MoneyUtil;

import javax.validation.ConstraintValidator;
import javax.validation.ConstraintValidatorContext;

public class ACHFieldsValidator implements ConstraintValidator<ACHFieldsValidate, ACH> {

    @Override
    public void initialize(ACHFieldsValidate constraintAnnotation) {

    }

    @Override
    public boolean isValid(ACH ach, ConstraintValidatorContext context) {
        boolean valid = true;
        if (!MoneyUtil.isRoutingNumberValid(ach.getDestinationRoute())) {
            context.buildConstraintViolationWithTemplate("destinationRoute number invalid").addConstraintViolation();
            valid = false;
        }
        return valid;
    }
}
