package com.controlpad.payman_common.transaction_batch;

import org.joda.time.DateTime;

import java.math.BigDecimal;
import java.math.BigInteger;

public class TransactionBatch {

    private Long id;
    private Long gatewayConnectionId;
    private String externalId;
    private BigDecimal gatewayNetAmount;
    private Integer externalNumber;
    private Integer status;
    private Long payoutJobId;
    private Long paymentFileId;
    private DateTime settledAt;
    private BigInteger gatewayTransactionCount;
    private BigInteger transactionCount;
    private BigDecimal sales;
    private BigDecimal subscriptions;
    private BigDecimal shipping;
    private BigDecimal taxPayments;
    private BigDecimal refunds;
    private BigDecimal voids;

    public TransactionBatch() {}

    public TransactionBatch(Long gatewayConnectionId, String externalId) {
        this.gatewayConnectionId = gatewayConnectionId;
        this.externalId = externalId;
    }

    public TransactionBatch(Long gatewayConnectionId, String externalId, Integer externalNumber) {
        this.gatewayConnectionId = gatewayConnectionId;
        this.externalId = externalId;
        this.externalNumber = externalNumber;
        this.status = 0;
    }

    public TransactionBatch(Long gatewayConnectionId, String externalId, Integer externalNumber, Integer status, Long payoutJobId, Long paymentFileId) {
        this.gatewayConnectionId = gatewayConnectionId;
        this.externalId = externalId;
        this.externalNumber = externalNumber;
        this.status = status;
        this.payoutJobId = payoutJobId;
        this.paymentFileId = paymentFileId;
    }

    public TransactionBatch(String externalId, BigDecimal gatewayNetAmount, DateTime settledAt, BigInteger gatewayTransactionCount) {
        this.externalId = externalId;
        this.gatewayNetAmount = gatewayNetAmount;
        this.settledAt = settledAt;
        this.gatewayTransactionCount = gatewayTransactionCount;
    }

    public Long getId() {
        return id;
    }

    public Long getGatewayConnectionId() {
        return gatewayConnectionId;
    }

    public String getExternalId() {
        return externalId;
    }

    public Integer getExternalNumber() {
        return externalNumber;
    }

    public Integer getStatus() {
        return status;
    }

    public Long getPayoutJobId() {
        return payoutJobId;
    }

    public Long getPaymentFileId() {
        return paymentFileId;
    }

    public DateTime getSettledAt() {
        return settledAt;
    }

    public void setSettledAt(DateTime settledAt) {
        this.settledAt = settledAt;
    }

    public void setStatus(Integer status) {
        this.status = status;
    }

    public BigDecimal getGatewayNetAmount() {
        return gatewayNetAmount;
    }

    public void setGatewayNetAmount(BigDecimal gatewayNetAmount) {
        this.gatewayNetAmount = gatewayNetAmount;
    }

    public BigInteger getGatewayTransactionCount() {
        return gatewayTransactionCount;
    }

    public void setGatewayTransactionCount(BigInteger gatewayTransactionCount) {
        this.gatewayTransactionCount = gatewayTransactionCount;
    }

    public BigInteger getTransactionCount() {
        return transactionCount;
    }

    public void setTransactionCount(BigInteger transactionCount) {
        this.transactionCount = transactionCount;
    }

    public BigDecimal getSales() {
        return sales;
    }

    public void setSales(BigDecimal sales) {
        this.sales = sales;
    }

    public BigDecimal getTaxPayments() {
        return taxPayments;
    }

    public void setTaxPayments(BigDecimal taxPayments) {
        this.taxPayments = taxPayments;
    }

    public BigDecimal getSubscriptions() {
        return subscriptions;
    }

    public void setSubscriptions(BigDecimal subscriptions) {
        this.subscriptions = subscriptions;
    }

    public BigDecimal getShipping() {
        return shipping;
    }

    public void setShipping(BigDecimal shipping) {
        this.shipping = shipping;
    }

    public BigDecimal getRefunds() {
        return refunds;
    }

    public void setRefunds(BigDecimal refunds) {
        this.refunds = refunds;
    }

    public BigDecimal getVoids() {
        return voids;
    }

    public void setVoids(BigDecimal voids) {
        this.voids = voids;
    }
}
