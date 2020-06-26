package com.controlpad.pay_fac.exceptions;

import com.controlpad.pay_fac.common.CommonResponse;
import org.apache.commons.lang3.StringUtils;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.validation.BindingResult;
import org.springframework.validation.ObjectError;

import java.util.ArrayList;
import java.util.List;

/**
 * Exception that can be thrown to represent a response. This decouples some error handling logic from endpoints.
 * Captured by com.cms.exceptions.ExceptionController
 */
public class ResponseException extends RuntimeException {

    private HttpStatus status;
    private Object body;

    public ResponseException(HttpStatus status, String body) {
        this.status = status;
        this.body = body;
    }

    public ResponseException(HttpStatus status) {
        this.status = status;
    }

    public ResponseException(String body) {
        this.status = HttpStatus.INTERNAL_SERVER_ERROR;
        this.body = body;
    }

    public ResponseException(BindingResult bindingResult) {
        this.status = HttpStatus.BAD_REQUEST;
        List<String> errors = new ArrayList<>(bindingResult.getAllErrors().size());
        for (ObjectError error: bindingResult.getAllErrors()) {
            if (StringUtils.isNotBlank(error.getDefaultMessage()))
                errors.add(error.getDefaultMessage());
        }
        this.body = errors;
    }

    public ResponseException(CommonResponse response) {
        this.status = HttpStatus.OK;
        this.body = response;
    }

    public ResponseEntity<Object> toResponse() {
        return new ResponseEntity<>(body, status);
    }

    public HttpStatus getStatus() {
        return status;
    }
}