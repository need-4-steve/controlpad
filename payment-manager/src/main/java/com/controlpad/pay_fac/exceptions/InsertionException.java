package com.controlpad.pay_fac.exceptions;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.HttpStatus;

import java.sql.SQLException;

public class InsertionException<Record> {
    private static final Logger logger = LoggerFactory.getLogger(InsertionException.class);

    public void handle(Exception e, Record record){
        if(e.getCause() != null && e.getCause() instanceof SQLException){
            SQLException sqlException = (SQLException) e.getCause();
            if(sqlException.getErrorCode() != 1062) {
                logger.error("Insert duplicate record happened!" + record.toString(), e);
            }else{
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
            }
        }else{
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }
}
