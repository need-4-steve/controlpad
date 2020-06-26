package com.controlpad.pay_fac.interceptor;

import java.lang.annotation.ElementType;
import java.lang.annotation.Retention;
import java.lang.annotation.RetentionPolicy;
import java.lang.annotation.Target;

/**
 * Being used in AuthInterceptor when an api key is used to allow specific permissions
 */
@Target({ElementType.METHOD, ElementType.TYPE})
@Retention(RetentionPolicy.RUNTIME)
public @interface APIKeyPermissions {
    boolean processSales() default false;
    boolean updateAccounts() default false;
    boolean createPaymentFile() default false;
}
