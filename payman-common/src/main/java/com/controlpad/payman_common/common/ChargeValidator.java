/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.common;


import javax.validation.ConstraintValidator;
import javax.validation.ConstraintValidatorContext;

public class ChargeValidator implements ConstraintValidator<ChargePercentValidate, Charge> {

    @Override
    public void initialize(ChargePercentValidate constraintAnnotation) {

    }

    @Override
    public boolean isValid(Charge charge, ConstraintValidatorContext context) {
        if (charge.getAmount() == null ^ charge.getPercent() == null) {
            context.buildConstraintViolationWithTemplate("amount and isPercent are required together").addConstraintViolation();
            return false;
        } else if (!charge.isPercentValid()){
            context.buildConstraintViolationWithTemplate("amount must be a number from 0 to 100 when isPercent").addConstraintViolation();
            return false;
        }
        return true;
    }
}
