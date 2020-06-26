package com.controlpad.payman_common.payout_job;


public class PayoutJob {

    private Long id;
    private String startAt;
    private String teamId;
    private String status;
    private String payoutScheme;
    private String paymentBatchId;

    public PayoutJob() {
    }

    public PayoutJob(String startAt, String status, String teamId, String payoutScheme) {
        this.startAt = startAt;
        this.status = status;
        this.teamId = teamId;
        this.payoutScheme = payoutScheme;
    }

    public PayoutJob(Long id, String startAt, String status, String teamId, String payoutScheme) {
        this.id = id;
        this.startAt = startAt;
        this.status = status;
        this.teamId = teamId;
        this.payoutScheme = payoutScheme;
    }

    public PayoutJob(String startAt, String teamId, String status, String payoutScheme, String paymentBatchId) {
        this.startAt = startAt;
        this.teamId = teamId;
        this.status = status;
        this.payoutScheme = payoutScheme;
        this.paymentBatchId = paymentBatchId;
    }

    public Long getId() {
        return id;
    }

    public String getStartAt() {
        return startAt;
    }

    public String getTeamId() {
        return teamId;
    }

    public String getStatus() {
        return status;
    }

    public String getPayoutScheme() {
        return payoutScheme;
    }

    public String getPaymentBatchId() {
        return paymentBatchId;
    }

    public void setId(Long id){
        this.id = id;
    }
}
