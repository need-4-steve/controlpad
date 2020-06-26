package com.controlpad.pay_fac.report.custom;


import com.controlpad.payman_common.util.GsonUtil;

public class SalesTaxInfo {

    private String transactionId;
    private String batchId;
    private String dateCollected;
    private String amount;

    public SalesTaxInfo() {
    }

    public SalesTaxInfo(String transactionId, String batchId, String dateCollected, String amount) {
        this.transactionId = transactionId;
        this.batchId = batchId;
        this.dateCollected = dateCollected;
        this.amount = amount;
    }

    public String getTransactionId() {
        return transactionId;
    }

    public String getBatchId() {
        return batchId;
    }

    public String getDateCollected() {
        return dateCollected;
    }

    public String getAmount() {
        return amount;
    }

    @Override
    public String toString(){
        return GsonUtil.getGson().toJson(this);
    }
}
