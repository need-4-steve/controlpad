/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.account;

import com.controlpad.payman_common.util.MoneyUtil;

import javax.validation.ConstraintValidator;
import javax.validation.ConstraintValidatorContext;

public class AccountFieldsValidator implements ConstraintValidator<AccountFieldsValidate, Account> {

    @Override
    public void initialize(AccountFieldsValidate constraintAnnotation) {}

    @Override
    public boolean isValid(Account account, ConstraintValidatorContext context) {
        if (AccountType.getIdForType(account.getType()) == AccountType.UNKNOWN.id) {
            context.buildConstraintViolationWithTemplate("Account type unknown").addConstraintViolation();
            return false;
        }

        if (!MoneyUtil.isRoutingNumberValid(account.getRouting())) {
            context.buildConstraintViolationWithTemplate("Routing number invalid").addConstraintViolation();
            return false;
        }

        if (account.formatNumber() == null) {
            context.buildConstraintViolationWithTemplate("Account number invalid").addConstraintViolation();
            return false;
        }
        return true;
    }
}
