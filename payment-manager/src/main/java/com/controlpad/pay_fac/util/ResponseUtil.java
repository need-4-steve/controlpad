package com.controlpad.pay_fac.util;

import com.controlpad.pay_fac.exceptions.ResponseException;
import org.springframework.http.HttpStatus;

public class ResponseUtil {

    private static final String INVALID_APPLICATION_MESSAGE = "Invalid application specified";

    public static ResponseException getInsufficientPrivileges(String message) {
        return new ResponseException(HttpStatus.FORBIDDEN, message);
    }

    public static ResponseException getUnauthorized(String message) {
        return new ResponseException(HttpStatus.UNAUTHORIZED, message);
    }

    public static ResponseException getInvalidApplication() {
        return new ResponseException(HttpStatus.BAD_REQUEST, INVALID_APPLICATION_MESSAGE);
    }
}