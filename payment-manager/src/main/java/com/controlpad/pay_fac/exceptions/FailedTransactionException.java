package com.controlpad.pay_fac.exceptions;


import com.controlpad.pay_fac.transaction.TransactionResponse;
import com.controlpad.payman_common.transaction.TransactionResult;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;

public class FailedTransactionException extends RuntimeException {

    private TransactionResult result;

    public FailedTransactionException(TransactionResult result) {
        this.result = result;
    }

    public ResponseEntity<TransactionResponse> toResponse() {
        return new ResponseEntity<>(new TransactionResponse(result), HttpStatus.OK);
    }
}
