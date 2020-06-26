package com.controlpad.pay_fac.transaction;

import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionResult;
import com.controlpad.payman_common.util.GsonUtil;

public class TransactionResponse extends CommonResponse {

    private String transactionId;
    private Transaction transaction;
    private Object transactionResponse;

    public TransactionResponse(Object transactionResponse, boolean issuccess){
        super(issuccess, null);
        this.transactionResponse = transactionResponse;
    }

    public TransactionResponse(Transaction transaction, boolean showTransaction) {
        this(transaction, showTransaction, TransactionResult.Success);
    }

    public TransactionResponse(Transaction transaction, boolean showTransaction, TransactionResult result) {
        this(result);
        if (showTransaction) {
            this.transaction = transaction;
        }
        this.transactionId = transaction.getId();
    }

    public TransactionResponse(String error) {
        super(false, error);
    }

    public TransactionResponse(boolean success) {
        super(success);
    }

    public TransactionResponse(boolean success, String description) {
        super(success, description);
    }

    public TransactionResponse(Boolean success, Integer statusCode, String description) {
        super(success, statusCode, description);
    }

    public TransactionResponse(TransactionResult transactionResult) {
        super(transactionResult.getSuccess(), transactionResult.getResultCode(), transactionResult.getMessage());
    }

    public TransactionResponse(String error, Object gatewayResponse) {
        this(error);
        this.transactionResponse = gatewayResponse;
    }

    public String getTransactionId() {
        return transactionId;
    }

    public Transaction getTransaction() {
        return transaction;
    }

    public Object getTransactionResponse() {
        return transactionResponse;
    }

    public void setTransactionResponse(Object transactionResponse) {
        this.transactionResponse = transactionResponse;
    }

    @Override
    public String toString() {
        return GsonUtil.getGson().toJson(this);
    }
}
