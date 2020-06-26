package com.controlpad.payman_common.entry;


import com.controlpad.payman_common.payment.PaymentType;

import java.math.BigDecimal;

public class Entry {

    private Long id;
    private Long balanceId;
    private BigDecimal amount;
    private String transactionId;
    private Long feeId;
    private String paymentId;
    private String type;
    private Boolean processed;
    private String createdAt;

    // Join from transactions
    private String payeeUserId;
    private String payerUserId;

    // Join from fees
    private String description;
    private String effectiveRate;

    public Entry() {}

    public Entry(Long balanceId, BigDecimal amount, String transactionId, Long feeId, String paymentId, String type, Boolean processed) {
        this.balanceId = balanceId;
        this.amount = amount;
        this.transactionId = transactionId;
        this.feeId = feeId;
        this.paymentId = paymentId;
        this.type = type;
        this.processed = processed;
    }

    public Entry(Long balanceId, BigDecimal amount, String transactionId, Long feeId, String paymentId, String type, Boolean processed, String createdAt) {
        this.balanceId = balanceId;
        this.amount = amount;
        this.transactionId = transactionId;
        this.feeId = feeId;
        this.paymentId = paymentId;
        this.type = type;
        this.processed = processed;
        this.createdAt = createdAt;
    }

    public Long getId() {
        return id;
    }

    public Long getBalanceId() {
        return balanceId;
    }

    public BigDecimal getAmount() {
        return amount;
    }

    public String getTransactionId() {
        return transactionId;
    }

    public Long getFeeId() {
        return feeId;
    }

    public String getPaymentId() {
        return paymentId;
    }

    public String getType() {
        return type;
    }

    public int getTypeId() {
        return PaymentType.findForSlug(type).id;
    }

    public Boolean getProcessed() {
        return processed;
    }

    public String getCreatedAt() {
        return createdAt;
    }

    public String getPayeeUserId() {
        return payeeUserId;
    }

    public String getPayerUserId() {
        return payerUserId;
    }

    public void setFeeId(Long feeId) {
        this.feeId = feeId;
    }
}
