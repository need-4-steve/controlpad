package com.controlpad.pay_fac.interceptor;

import java.lang.annotation.ElementType;
import java.lang.annotation.Retention;
import java.lang.annotation.RetentionPolicy;
import java.lang.annotation.Target;

/**
 * Being used in AuthInterceptor to authenticate user based on Authorization header
 */

/**
 * userSession: the information of user session
 *
 * SQL Session:
 *  clientSqlSession: SqlSession for access clients' database.
 *  apiSqlSession: SqlSession for access payman database. Not allowed APIKey(proxy user) to access
 *
 *
 */
@Target({ElementType.METHOD, ElementType.TYPE})
@Retention(RetentionPolicy.RUNTIME)
public @interface Authorization {
    boolean allowSessionKey() default true;
    boolean allowAPIKey() default true;

    boolean apiSqlAutoCommit() default false;
    boolean clientSqlAutoCommit() default false;
    boolean userSession() default false;

    boolean superuser() default false;
    int readPrivilege() default 0;
    int writePrivilege() default 0;
    int createPrivilege() default 0;
    boolean apiSqlSession() default false;
    boolean clientSqlSession() default false;
}
