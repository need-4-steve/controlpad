package com.controlpad.pay_fac.report.custom;


import com.controlpad.payman_common.transaction.Transaction;

import java.math.BigDecimal;

public class BalanceLedgerItem {

    private String transactionId;
    private BigDecimal transactionAmount;
    private String orderId;
    private String gatewayReferenceId;
    private String transactionType;
    private String statusCode;
    private BigDecimal amount;
    private BigDecimal affiliate;
    private Long balanceId;
    private BigDecimal fees;
    private BigDecimal salesTax;
    private BigDecimal withdraw;
    private BigDecimal net;
    private Boolean processed;
    private String date; // Settlement date?
    private BigDecimal balance;
    private String description;

    public String getTransactionId() {
        return this.transactionId;
    }

    public BigDecimal getNet() {
        return net;
    }

    public void setOrderId(String orderId) {
        this.orderId = orderId;
    }

    public void setTransactionType(String transactionType) {
        this.transactionType = transactionType;
    }

    public void setBalance(BigDecimal balance) {
        this.balance = balance;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public BigDecimal getTransactionAmount() {
        return this.transactionAmount;
    }

    public void setTransactionInfo(Transaction transaction) {
        setOrderId(transaction.getOrderId());
        setTransactionType(transaction.getTransactionType());
        setDescription(transaction.getDescription());
        this.transactionAmount = transaction.getAmount();
    }
}
