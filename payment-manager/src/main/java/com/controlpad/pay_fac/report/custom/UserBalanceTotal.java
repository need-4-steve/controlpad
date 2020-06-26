package com.controlpad.pay_fac.report.custom;

import java.math.BigDecimal;

public class UserBalanceTotal {

    private String userId;
    private BigDecimal taxOwed;

    public UserBalanceTotal(String userId) {
        this.userId = userId;
    }

    public UserBalanceTotal(String userId, BigDecimal taxOwed) {
        this.userId = userId;
        this.taxOwed = taxOwed;
    }

    public String getUserId() {
        return userId;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }

    public BigDecimal getTaxOwed() {
        return taxOwed;
    }

    public void setTaxOwed(BigDecimal taxOwed) {
        this.taxOwed = taxOwed;
    }
}
