package com.controlpad.payman_common.transaction_fee;


import java.math.BigDecimal;

public class TransactionFee {

    private Long id;
    private String transactionId;
    private String gatewayReferenceId;
    private String description;
    private BigDecimal amount;

    public TransactionFee() {

    }

    public TransactionFee(String transactionId, String gatewayReferenceId, String description, BigDecimal amount) {
        this.transactionId = transactionId;
        this.gatewayReferenceId = gatewayReferenceId;
        this.description = description;
        this.amount = amount;
    }

    public String getTransactionId() {
        return transactionId;
    }

    public Long getId() {
        return id;
    }

    public String getGatewayReferenceId() {
        return gatewayReferenceId;
    }

    public String getDescription() {
        return description;
    }

    public BigDecimal getAmount() {
        return amount;
    }
}
