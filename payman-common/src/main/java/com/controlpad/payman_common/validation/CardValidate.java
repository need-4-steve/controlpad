package com.controlpad.payman_common.validation;

import javax.validation.Constraint;
import javax.validation.Payload;
import java.lang.annotation.*;

@Target({ElementType.TYPE, ElementType.ANNOTATION_TYPE })
@Retention(RetentionPolicy.RUNTIME)
@Constraint(validatedBy = { CardValidator.class })
@Documented
public @interface CardValidate {

    String message() default "";

    Class<?>[] groups() default {};

    Class<? extends Payload>[] payload() default { };
}
