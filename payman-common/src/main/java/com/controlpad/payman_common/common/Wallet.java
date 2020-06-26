/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.common;

import com.controlpad.payman_common.validation.AlwaysCheck;

import javax.validation.constraints.Null;
import java.math.BigDecimal;

public class Wallet extends Charge {

    @Null(message = "Balance cannot be updated directly. Please use transactions.", groups = AlwaysCheck.class)
    private BigDecimal balance;

    protected Wallet() {}

    public Wallet(BigDecimal balance, BigDecimal amount, Boolean isPercent) {
        super(amount, isPercent);
        this.balance = balance;
    }

    public BigDecimal getBalance() {
        return balance;
    }

    public void setBalance(BigDecimal balance) {
        this.balance = balance;
    }
}