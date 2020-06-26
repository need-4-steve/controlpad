package com.controlpad.payman_common.validation;

import com.controlpad.payman_common.team.PayoutSchedule;

import javax.validation.ConstraintValidator;
import javax.validation.ConstraintValidatorContext;

public class PayoutScheduleValidator implements ConstraintValidator<PayoutScheduleValidate, PayoutSchedule> {

    @Override
    public void initialize(PayoutScheduleValidate constraintAnnotation) {

    }

    @Override
    public boolean isValid(PayoutSchedule payoutSchedule, ConstraintValidatorContext context) {
        PayoutSchedule.DayType dayType = PayoutSchedule.DayType.getForName(payoutSchedule.getTypeOfDay());
        if (dayType == PayoutSchedule.DayType.UNKNOWN) {
            context.buildConstraintViolationWithTemplate("typeOfDay supports month or week").addConstraintViolation();
            return false;
        }
        for (Integer day : payoutSchedule.getDays()) {
            if (day < 1) {
                context.buildConstraintViolationWithTemplate("day cannot be less than 1").addConstraintViolation();
                return false;
            }
            if (dayType == PayoutSchedule.DayType.MONTH && day > 31) {
                context.buildConstraintViolationWithTemplate("day cannot be greater than 31 for type month").addConstraintViolation();
                return false;
            } else if (dayType == PayoutSchedule.DayType.WEEK && day > 7) {
                context.buildConstraintViolationWithTemplate("day cannot be greater than 7 for type week").addConstraintViolation();
                return false;
            }
        }
        return true;
    }
}
