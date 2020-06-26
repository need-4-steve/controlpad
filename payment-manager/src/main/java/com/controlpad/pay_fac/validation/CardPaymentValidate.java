package com.controlpad.pay_fac.validation;

import javax.validation.Constraint;
import javax.validation.Payload;
import java.lang.annotation.*;

@Target({ElementType.TYPE, ElementType.ANNOTATION_TYPE })
@Retention(RetentionPolicy.RUNTIME)
@Constraint(validatedBy = { CardPaymentValidator.class })
@Documented
public @interface CardPaymentValidate {
    String message() default "";

    Class<?>[] groups() default {};

    Class<? extends Payload>[] payload() default { };
}