package com.controlpad.payman_processor.gateway;

import com.controlpad.payman_common.entry.Entry;

import java.math.BigDecimal;

public class FeeEntry extends Entry {

    public FeeEntry() {
    }

    public FeeEntry(Long balanceId, BigDecimal amount, String transactionId, Long feeId, String paymentId, String type, Boolean processed, String referenceId) {
        super(balanceId, amount, transactionId, feeId, paymentId, type, processed);
        this.referenceId = referenceId;
    }

    private String referenceId;

    public String getReferenceId() {
        return referenceId;
    }

    public void setReferenceId(String referenceId) {
        this.referenceId = referenceId;
    }
}
