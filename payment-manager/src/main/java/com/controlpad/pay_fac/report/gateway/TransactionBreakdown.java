package com.controlpad.pay_fac.report.gateway;

import java.math.BigDecimal;

public class TransactionBreakdown {

    private String id;
    private String gatewayReferenceId;
    private String payeeUserId;
    private String payerUserId;
    private BigDecimal eWallet;
    private BigDecimal affiliate;
    private BigDecimal salesTax;
    private BigDecimal consignment;
    private BigDecimal fees;
    private String type;
    private Boolean processed;

    public TransactionBreakdown() {
    }

    public TransactionBreakdown(String id, String gatewayReferenceId, String payeeUserId, String payerUserId, BigDecimal eWallet, BigDecimal affiliate, BigDecimal salesTax, BigDecimal consignment, BigDecimal fees) {
        this.id = id;
        this.gatewayReferenceId = gatewayReferenceId;
        this.payeeUserId = payeeUserId;
        this.payerUserId = payerUserId;
        this.eWallet = eWallet;
        this.affiliate = affiliate;
        this.salesTax = salesTax;
        this.consignment = consignment;
        this.fees = fees;
    }

    public TransactionBreakdown(String id, String gatewayReferenceId, String payeeUserId, String payerUserId,
                                BigDecimal eWallet, BigDecimal affiliate, BigDecimal salesTax, BigDecimal consignment,
                                BigDecimal fees, String type, Boolean processed) {
        this.id = id;
        this.gatewayReferenceId = gatewayReferenceId;
        this.payeeUserId = payeeUserId;
        this.payerUserId = payerUserId;
        this.eWallet = eWallet;
        this.affiliate = affiliate;
        this.salesTax = salesTax;
        this.consignment = consignment;
        this.fees = fees;
        this.type = type;
        this.processed = processed;
    }

    public String getId() {
        return id;
    }

    public String getGatewayReferenceId() {
        return gatewayReferenceId;
    }

    public String getPayeeUserId() {
        return payeeUserId;
    }

    public String getPayerUserId() {
        return payerUserId;
    }

    public BigDecimal geteWallet() {
        return eWallet;
    }

    public BigDecimal getAffiliate() {
        return affiliate;
    }

    public BigDecimal getSalesTax() {
        return salesTax;
    }

    public BigDecimal getConsignment() {
        return consignment;
    }

    public BigDecimal getFees() {
        return fees;
    }

    public String getType() {
        return type;
    }

    public Boolean getProcessed() {
        return processed;
    }
}
