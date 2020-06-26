package com.controlpad.payman_common.validation;

import javax.validation.Constraint;
import javax.validation.Payload;
import java.lang.annotation.*;

@Target({ElementType.TYPE, ElementType.ANNOTATION_TYPE })
@Retention(RetentionPolicy.RUNTIME)
@Constraint(validatedBy = { PayoutScheduleValidator.class })
@Documented
public @interface PayoutScheduleValidate {
    String message() default "";

    Class<?>[] groups() default {};

    Class<? extends Payload>[] payload() default { };
}
