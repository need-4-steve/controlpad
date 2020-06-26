package com.controlpad.pay_fac.report.gateway;

import java.math.BigDecimal;

public class GatewayTransaction {

    private String id;
    private String date;
    private BigDecimal amount;
    private BigDecimal salesTax;
    private String type;
    private String name;
    private String source;
    private String status;
    private String description;
    private TransactionBreakdown transactionBreakdown;

    public GatewayTransaction() {
    }

    public GatewayTransaction(String id, String date, BigDecimal amount, BigDecimal salesTax, String type, String name, String source, String status, String description) {
        this.id = id;
        this.date = date;
        this.amount = amount;
        this.salesTax = salesTax;
        this.type = type;
        this.name = name;
        this.source = source;
        this.status = status;
        this.description = description;
    }

    public String getId() {
        return id;
    }

    public String getDate() {
        return date;
    }

    public BigDecimal getAmount() {
        return amount;
    }

    public BigDecimal getSalesTax() {
        return salesTax;
    }

    public String getType() {
        return type;
    }

    public String getName() {
        return name;
    }

    public String getSource() {
        return source;
    }

    public String getStatus() {
        return status;
    }

    public String getDescription() {
        return description;
    }

    public TransactionBreakdown getTransactionBreakdown() {
        return transactionBreakdown;
    }

    public void setTransactionBreakdown(TransactionBreakdown transactionBreakdown) {
        this.transactionBreakdown = transactionBreakdown;
    }
}
