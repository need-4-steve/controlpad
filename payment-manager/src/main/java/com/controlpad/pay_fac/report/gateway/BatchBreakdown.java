package com.controlpad.pay_fac.report.gateway;

import java.math.BigDecimal;
import java.math.BigInteger;

public class BatchBreakdown {

    private String id;
    private String externalId;
    private BigDecimal eWallet;
    private BigDecimal affiliate;
    private BigDecimal salesTax;
    private BigDecimal consignment;
    private BigDecimal fees;
    private BigDecimal processedAmount;
    private BigDecimal notProcessedAmount;
    private BigInteger processedCount;
    private BigInteger notProcessedCount;

    public BatchBreakdown() {
    }

    public BatchBreakdown(String id, String gatewayReferenceId, BigDecimal eWallet, BigDecimal affiliate, BigDecimal salesTax, BigDecimal consignment, BigDecimal fees) {
        this.id = id;
        this.externalId = gatewayReferenceId;
        this.eWallet = eWallet;
        this.affiliate = affiliate;
        this.salesTax = salesTax;
        this.consignment = consignment;
        this.fees = fees;
    }

    public BatchBreakdown(String id, String externalId, BigDecimal eWallet, BigDecimal affiliate, BigDecimal salesTax, BigDecimal consignment, BigDecimal fees, BigDecimal processedAmount, BigDecimal notProcessedAmount, BigInteger processedCount, BigInteger notProcessedCount) {
        this.id = id;
        this.externalId = externalId;
        this.eWallet = eWallet;
        this.affiliate = affiliate;
        this.salesTax = salesTax;
        this.consignment = consignment;
        this.fees = fees;
        this.processedAmount = processedAmount;
        this.notProcessedAmount = notProcessedAmount;
        this.processedCount = processedCount;
        this.notProcessedCount = notProcessedCount;
    }

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getGatewayReferenceId() {
        return externalId;
    }

    public void setGatewayReferenceId(String gatewayReferenceId) {
        this.externalId = gatewayReferenceId;
    }

    public BigDecimal geteWallet() {
        return eWallet;
    }

    public void seteWallet(BigDecimal eWallet) {
        this.eWallet = eWallet;
    }

    public BigDecimal getAffiliate() {
        return affiliate;
    }

    public void setAffiliate(BigDecimal affiliate) {
        this.affiliate = affiliate;
    }

    public BigDecimal getSalesTax() {
        return salesTax;
    }

    public void setSalesTax(BigDecimal salesTax) {
        this.salesTax = salesTax;
    }

    public BigDecimal getConsignment() {
        return consignment;
    }

    public void setConsignment(BigDecimal consignment) {
        this.consignment = consignment;
    }

    public BigDecimal getFees() {
        return fees;
    }

    public void setFees(BigDecimal fees) {
        this.fees = fees;
    }

    public String getExternalId() {
        return externalId;
    }

    public void setExternalId(String externalId) {
        this.externalId = externalId;
    }

    public BigDecimal getProcessedAmount() {
        return processedAmount;
    }

    public void setProcessedAmount(BigDecimal processedAmount) {
        this.processedAmount = processedAmount;
    }

    public BigDecimal getNotProcessedAmount() {
        return notProcessedAmount;
    }

    public void setNotProcessedAmount(BigDecimal notProcessedAmount) {
        this.notProcessedAmount = notProcessedAmount;
    }

    public BigInteger getProcessedCount() {
        return processedCount;
    }

    public void setProcessedCount(BigInteger processedCount) {
        this.processedCount = processedCount;
    }

    public BigInteger getNotProcessedCount() {
        return notProcessedCount;
    }

    public void setNotProcessedCount(BigInteger notProcessedCount) {
        this.notProcessedCount = notProcessedCount;
    }
}
