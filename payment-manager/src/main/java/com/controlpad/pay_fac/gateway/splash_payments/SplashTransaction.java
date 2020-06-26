package com.controlpad.pay_fac.gateway.splash_payments;


import java.util.List;

public class SplashTransaction {

    private String id;
    private String created;
    private String merchant;
    private String description;
    private String first;
    private String last;
    private String batch;
    private String captured;
    private String settled;
    private String settledCurrency;
    private Integer settledTotal;
    private Integer tax;
    private Integer total;
    private Integer status;
    private Integer type;
    private String checkStage;
    private Integer swiped;
    private String fortxn;

    List<SplashTransactionResult> txnResults;

    public String getId() {
        return id;
    }

    public String getCreated() {
        return created;
    }

    public String getMerchant() {
        return merchant;
    }

    public String getDescription() {
        return description;
    }

    public String getFirst() {
        return first;
    }

    public String getLast() {
        return last;
    }

    public String getBatch() {
        return batch;
    }

    public String getCaptured() {
        return captured;
    }

    public String getSettled() {
        return settled;
    }

    public String getSettledCurrency() {
        return settledCurrency;
    }

    public Integer getSettledTotal() {
        return settledTotal;
    }

    public Integer getTax() {
        return tax;
    }

    public Integer getTotal() {
        return total;
    }

    public Integer getStatus() {
        return status;
    }

    public Integer getType() {
        return type;
    }

    public Integer getSwiped() {
        return swiped;
    }

    public String getForTxn() {
        return fortxn;
    }

    public String getCheckStage() {
        return checkStage;
    }

    public List<SplashTransactionResult> getTxnResults() {
        return txnResults;
    }
}