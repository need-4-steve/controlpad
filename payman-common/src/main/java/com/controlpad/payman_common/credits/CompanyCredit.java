package com.controlpad.payman_common.credits;

import com.controlpad.payman_common.validation.AlwaysCheck;

import javax.validation.constraints.Null;
import java.math.BigDecimal;

public class CompanyCredit {

    private String userId;

    @Null(message = "Balance cannot be updated directly. Please use transactions.", groups = AlwaysCheck.class)
    private BigDecimal balance;

    public CompanyCredit() {}

    public CompanyCredit(String userId, BigDecimal balance) {
        this.userId = userId;
        this.balance = balance;
    }

    public String getUserId() {
        return userId;
    }

    public BigDecimal getBalance() {
        return balance;
    }

    public void setBalance(BigDecimal balance) {
        this.balance = balance;
    }
}