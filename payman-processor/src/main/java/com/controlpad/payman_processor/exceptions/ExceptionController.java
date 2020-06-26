package com.controlpad.payman_processor.exceptions;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.ResponseStatus;

import java.sql.SQLException;

/**
 * Handles exceptions and logging/logic
 */
@ControllerAdvice
public class ExceptionController {

    private static final Logger logger = LoggerFactory.getLogger(ExceptionController.class);

    @ResponseStatus(value = HttpStatus.INTERNAL_SERVER_ERROR, reason = "Unable to access database")
    @ExceptionHandler(SQLException.class)
    public void onSQLException(SQLException e) {
        logger.error("", e);
    }

    @ExceptionHandler(Exception.class)
    public void onException(Exception e) {
        logger.error(e.getMessage(), e);
    }
}