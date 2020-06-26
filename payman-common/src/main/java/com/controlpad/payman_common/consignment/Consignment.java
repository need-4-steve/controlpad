/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.consignment;

import com.controlpad.payman_common.common.Wallet;

import java.math.BigDecimal;

public class Consignment extends Wallet {

	private String userId;

    public Consignment() {}

	public Consignment(BigDecimal balance, BigDecimal amount, Boolean is_percent) {
        super(balance, amount, is_percent);
	}

    public Consignment(String userId, BigDecimal balance, BigDecimal amount, Boolean is_percent) {
        this(balance, amount, is_percent);
        this.userId = userId;
    }

    public String getUserId() {
        return userId;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }

}