package com.controlpad.pay_fac.report.custom;


import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.util.GsonUtil;

import java.math.BigDecimal;
import java.util.List;

public class ProcessingFeeInfo {

    private String transactionId;
    private String date;
    private String cardHolder;
    private BigDecimal amount;
    private BigDecimal salesTax;
    private BigDecimal shipping;
    private BigDecimal processing;
    private BigDecimal consignment;
    private BigDecimal netAmount;

    public ProcessingFeeInfo() {
    }

    public ProcessingFeeInfo(String transactionId, String date, String cardHolder, BigDecimal amount,
                             BigDecimal salesTax, BigDecimal shipping, BigDecimal processing, BigDecimal transactionFee,
                             BigDecimal other, BigDecimal netAmount) {
        this.transactionId = transactionId;
        this.date = date;
        this.cardHolder = cardHolder;
        this.amount = amount;
        this.salesTax = (salesTax != null ? salesTax : BigDecimal.ZERO);
        this.shipping = shipping;
        this.processing = processing;
        this.netAmount = netAmount;
    }

    public String getTransactionId() {
        return transactionId;
    }

    public String getDate() {
        return date;
    }

    public String getCardHolder() {
        return cardHolder;
    }

    public BigDecimal getAmount() {
        return amount;
    }

    public BigDecimal getSalesTax() {
        return salesTax;
    }

    public BigDecimal getShipping() {
        return shipping;
    }

    public BigDecimal getProcessing() {
        return processing;
    }

    public BigDecimal getConsignment() {
        return consignment;
    }

    public BigDecimal getNetAmount() {
        return netAmount;
    }

    public void setPayoutInfo(List<Entry> entries) {
        this.processing = BigDecimal.ZERO;
        this.netAmount = BigDecimal.ZERO;
        this.consignment = BigDecimal.ZERO;
        this.salesTax = BigDecimal.ZERO;

        for (Entry entry : entries) {
            switch (PaymentType.findForSlug(entry.getType())) {
                case FEE:
                    this.processing = this.processing.add(entry.getAmount());
                    break;
                case MERCHANT:
                    this.netAmount = entry.getAmount();
                    break;
                case CONSIGNMENT:
                    this.consignment = entry.getAmount();
                    break;
                case SALES_TAX:
                    this.salesTax = entry.getAmount();
                    break;
            }
        }
        this.netAmount = this.netAmount.add(this.processing).add(this.consignment).add(this.salesTax);
    }

    @Override
    public String toString(){
        return GsonUtil.getGson().toJson(this);
    }
}
