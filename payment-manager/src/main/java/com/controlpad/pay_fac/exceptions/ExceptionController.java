package com.controlpad.pay_fac.exceptions;

import com.controlpad.pay_fac.transaction.TransactionResponse;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.exceptions.PersistenceException;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.http.converter.HttpMessageNotReadableException;
import org.springframework.validation.ObjectError;
import org.springframework.web.HttpMediaTypeNotSupportedException;
import org.springframework.web.HttpRequestMethodNotSupportedException;
import org.springframework.web.bind.MethodArgumentNotValidException;
import org.springframework.web.bind.MissingServletRequestParameterException;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.ResponseStatus;
import org.springframework.web.method.annotation.MethodArgumentTypeMismatchException;

import javax.servlet.http.HttpServletRequest;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

/**
 * Handles exceptions for converting into response and performing logging/logic
 */
@ControllerAdvice
public class ExceptionController {

    private static final Logger logger = LoggerFactory.getLogger(ExceptionController.class);

    @ExceptionHandler(ResponseException.class)
    public ResponseEntity<Object> onResponseException(ResponseException exception) {
        return exception.toResponse();
    }

    @ExceptionHandler(FailedTransactionException.class)
    public ResponseEntity<TransactionResponse> onFailedTransaction(FailedTransactionException exception) {
        return exception.toResponse();
    }

    @ResponseStatus(value = HttpStatus.INTERNAL_SERVER_ERROR)
    @ExceptionHandler(SQLException.class)
    public void onSQLException(HttpServletRequest request, SQLException ex) {
        logger.error(ex.getMessage(), ex);
    }

    @ResponseStatus(value = HttpStatus.INTERNAL_SERVER_ERROR)
    @ExceptionHandler(PersistenceException.class)
    public void onPersistenceException(PersistenceException pe) {
        logger.error(pe.getMessage(), pe);
    }

    @ExceptionHandler(HttpMediaTypeNotSupportedException.class)
    public ResponseEntity<String> onMediaTypeNotSupportedException(HttpMediaTypeNotSupportedException exception) {
        return new ResponseEntity<>("Media type not supported. Please use json only", HttpStatus.UNSUPPORTED_MEDIA_TYPE);
    }

    @ExceptionHandler(MethodArgumentNotValidException.class)
    public ResponseEntity<List<String>> onMethodArgumentNotValidException(MethodArgumentNotValidException exception) {
        List<String> errors = new ArrayList<>(exception.getBindingResult().getAllErrors().size());
        for (ObjectError error: exception.getBindingResult().getAllErrors()) {
            if (StringUtils.isNotBlank(error.getDefaultMessage()))
                errors.add(error.getDefaultMessage());
        }
        return new ResponseEntity<>(errors, HttpStatus.BAD_REQUEST);
    }

    @ExceptionHandler(HttpRequestMethodNotSupportedException.class)
    public ResponseEntity<String> onHttpRequestMethodNotSupportedException(HttpRequestMethodNotSupportedException hrmnse) {
        return new ResponseEntity<>(hrmnse.getMethod() + " not supported", HttpStatus.METHOD_NOT_ALLOWED);
    }

    @ExceptionHandler(MissingServletRequestParameterException.class)
    public ResponseEntity<String> onParamException(MissingServletRequestParameterException msrpe) {
        return new ResponseEntity<>("Query Param " + msrpe.getParameterType() + " " + msrpe.getParameterName() + " required.", HttpStatus.BAD_REQUEST);
    }

    @ExceptionHandler(MethodArgumentTypeMismatchException.class)
    public ResponseEntity<String> onArgumentTypeMismatch(MethodArgumentTypeMismatchException matme) {
        return new ResponseEntity<>(matme.getName() + " should be of type " + matme.getRequiredType() ,HttpStatus.BAD_REQUEST);
    }

    @ExceptionHandler(HttpMessageNotReadableException.class)
    public ResponseEntity<String> onMessageNotReadable(HttpMessageNotReadableException hmnre) {
        return new ResponseEntity<>("Request body wrong format", HttpStatus.BAD_REQUEST);
    }

    @ResponseStatus(HttpStatus.INTERNAL_SERVER_ERROR)
    @ExceptionHandler(Exception.class)
    public void onException(Exception e) {
        logger.error(e.getMessage(), e);
    }
}