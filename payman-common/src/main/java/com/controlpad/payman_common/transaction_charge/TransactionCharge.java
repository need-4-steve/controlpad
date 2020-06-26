/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.transaction_charge;

import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.util.GsonUtil;

import java.math.BigDecimal;

public class TransactionCharge {

    private Long id;
    private String userId;
    private String transactionId;
    private Long accountId;
    private BigDecimal amount;
    private Long paymentId;
    private Long feeId;
    private String type;
    private Boolean processed;

    public TransactionCharge() {}

    public TransactionCharge(String userId, String transactionId, Long accountId, BigDecimal amount, String paymentType) {
        this.userId = userId;
        this.transactionId = transactionId;
        this.accountId = accountId;
        this.amount = amount;
        this.type = paymentType;
    }

    public TransactionCharge(String userId, String transactionId, Long accountId, BigDecimal amount, Long feeId, String type) {
        this.userId = userId;
        this.transactionId = transactionId;
        this.accountId = accountId;
        this.amount = amount;
        this.feeId = feeId;
        this.type = type;
    }

    public TransactionCharge(Long id, String userId, String transactionId, Long accountId, BigDecimal amount, Long paymentId, Long feeId, String type) {
        this.id = id;
        this.userId = userId;
        this.transactionId = transactionId;
        this.accountId = accountId;
        this.amount = amount;
        this.paymentId = paymentId;
        this.feeId = feeId;
        this.type = type;
    }

    public TransactionCharge(String userId, String transactionId, Long accountId, BigDecimal amount, Long feeId, String type, Boolean processed) {
        this.userId = userId;
        this.transactionId = transactionId;
        this.accountId = accountId;
        this.amount = amount;
        this.feeId = feeId;
        this.type = type;
        this.processed = processed;
    }

    public Long getId() {
        return id;
    }

    public String getUserId() {
        return userId;
    }

    public String getTransactionId() {
        return transactionId;
    }

    public Long getAccountId() {
        return accountId;
    }

    public BigDecimal getAmount() {
        return amount;
    }

    public Long getPaymentId() {
        return paymentId;
    }

    public Long getFeeId() {
        return feeId;
    }

    public String getType() {
        return type;
    }

    public Boolean getProcessed() {
        return processed;
    }

    public void setPaymentId(Long paymentId) {
        this.paymentId = paymentId;
    }

    public void setProcessed(Boolean paid) {
        this.processed = paid;
    }

    public int getTypeId() {
        return PaymentType.findForSlug(type).id;
    }

    @Override
    public String toString() {
        return GsonUtil.getGson().toJson(this);
    }
}
