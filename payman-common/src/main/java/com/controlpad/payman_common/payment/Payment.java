package com.controlpad.payman_common.payment;


import java.math.BigDecimal;

public class Payment {

    private String id;
    private String teamId;
    private String userId;
    private Long accountId;
    private BigDecimal amount;
    private Long paymentFileId;
    private String paymentBatchId;
    private String referenceId;
    private String type;
    private String paidAt;
    private Boolean returned;
    private String created_at;

    public Payment() {}

    public Payment(String teamId, String userId, Long accountId, BigDecimal amount, String type) {
        this.teamId = teamId;
        this.userId = userId;
        this.accountId = accountId;
        this.amount = amount;
        this.type = type;
    }

    public Payment(String id, String teamId, String userId, Long accountId, BigDecimal amount, Long paymentFileId,
                   String referenceId, String type) {
        this.id = id;
        this.teamId = teamId;
        this.userId = userId;
        this.accountId = accountId;
        this.amount = amount;
        this.paymentFileId = paymentFileId;
        this.referenceId = referenceId;
        this.type = type;
    }

    public Payment(String id, String teamId, String userId, Long accountId, BigDecimal amount, String paymentBatchId,
                   String type) {
        this.id = id;
        this.teamId = teamId;
        this.userId = userId;
        this.accountId = accountId;
        this.amount = amount;
        this.paymentBatchId = paymentBatchId;
        this.type = type;
    }

    public String getId() {
        return id;
    }

    public String getTeamId() {
        return teamId;
    }

    public String getUserId() {
        return userId;
    }

    public Long getAccountId() {
        return accountId;
    }

    public BigDecimal getAmount() {
        return amount;
    }

    public Long getPaymentFileId() {
        return paymentFileId;
    }

    public String getReferenceId() {
        return referenceId;
    }

    public String getType() {
        return type;
    }

    public Integer getTypeId() {
        return PaymentType.findForSlug(type).id;
    }

    public String getPaidAt() {
        return paidAt;
    }

    public Boolean getReturned() {
        return returned;
    }

    public String getPaymentBatchId() {
        return paymentBatchId;
    }

    public String getCreated_at() {
        return created_at;
    }

    public void setId(String id) {
        this.id = id;
    }

    public void setPaymentFileId(Long paymentFileId) {
        this.paymentFileId = paymentFileId;
    }

    public void setReturned(Boolean returned) {
        this.returned = returned;
    }

    public void setReferenceId(String referenceId) {
        this.referenceId = referenceId;
    }

    public void setPaidAt(String paidAt) {
        this.paidAt = paidAt;
    }

    public void addAmount(BigDecimal amount) {
        this.amount = this.amount.add(amount);
    }

    public void setAmount(BigDecimal amount) {
        this.amount = amount;
    }

    public void setPaymentBatchId(String paymentBatchId) {
        this.paymentBatchId = paymentBatchId;
    }
}
