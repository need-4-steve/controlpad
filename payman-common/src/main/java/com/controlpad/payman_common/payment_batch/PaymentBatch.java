package com.controlpad.payman_common.payment_batch;

import org.joda.time.DateTime;

import java.math.BigDecimal;

public class PaymentBatch {

    private String id;
    private String description;
    private DateTime submittedAt;
    private String teamId;
    private String status;
    private BigDecimal netAmount;
    private Integer paymentCount;
    private DateTime createdAt;

    public PaymentBatch() {
    }

    public PaymentBatch(String id, String description, DateTime submittedAt, String teamId, String status) {
        this.id = id;
        this.description = description;
        this.submittedAt = submittedAt;
        this.teamId = teamId;
        this.status = status;
    }

    public PaymentBatch(String id, String description, String teamId, String status, BigDecimal netAmount, Integer paymentCount) {
        this.id = id;
        this.description = description;
        this.teamId = teamId;
        this.status = status;
        this.netAmount = netAmount;
        this.paymentCount = paymentCount;
    }

    public String getId() {
        return id;
    }

    public String getDescription() {
        return description;
    }

    public DateTime getSubmittedAt() {
        return submittedAt;
    }

    public String getTeamId() {
        return teamId;
    }

    public String getStatus() {
        return status;
    }

    public BigDecimal getNetAmount() {
        return netAmount;
    }

    public Integer getPaymentCount() {
        return paymentCount;
    }

    public DateTime getCreatedAt() {
        return createdAt;
    }

    public void setStatus(String status) {
        this.status = status;
    }

    public void setNetAmount(BigDecimal netAmount) {
        this.netAmount = netAmount;
    }

    public void setPaymentCount(Integer paymentCount) {
        this.paymentCount = paymentCount;
    }

    public void setSubmittedAt(DateTime submittedAt) {
        this.submittedAt = submittedAt;
    }
}
