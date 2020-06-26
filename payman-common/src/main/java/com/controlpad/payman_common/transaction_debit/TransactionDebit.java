package com.controlpad.payman_common.transaction_debit;


import java.math.BigDecimal;

public class TransactionDebit {

    private String id;
    private String userId;
    private String transactionId;
    private BigDecimal amount;
    private Long accountId;
    private Long paymentFileId;
    private Boolean returned;

    public TransactionDebit() {}

    public TransactionDebit(String id, String userId, String transactionId, BigDecimal amount) {
        this.id = id;
        this.userId = userId;
        this.transactionId = transactionId;
        this.amount = amount;
    }

    public TransactionDebit(String id, String userId, String transactionId, BigDecimal amount, Long accountId, Long paymentFileId) {
        this.id = id;
        this.userId = userId;
        this.transactionId = transactionId;
        this.amount = amount;
        this.accountId = accountId;
        this.paymentFileId = paymentFileId;
    }

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getUserId() {
        return userId;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }

    public String getTransactionId() {
        return transactionId;
    }

    public void setTransactionId(String transactionId) {
        this.transactionId = transactionId;
    }

    public BigDecimal getAmount() {
        return amount;
    }

    public void setAmount(BigDecimal amount) {
        this.amount = amount;
    }

    public Long getAccountId() {
        return accountId;
    }

    public void setAccountId(Long accountId) {
        this.accountId = accountId;
    }

    public Long getPaymentFileId() {
        return paymentFileId;
    }

    public void setPaymentFileId(Long paymentFileId) {
        this.paymentFileId = paymentFileId;
    }

    public Boolean getReturned() {
        return returned;
    }

    public void setReturned(Boolean returned) {
        this.returned = returned;
    }
}
