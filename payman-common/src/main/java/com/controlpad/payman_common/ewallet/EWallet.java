/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.ewallet;

import com.controlpad.payman_common.common.Wallet;
import com.controlpad.payman_common.util.GsonUtil;

import java.math.BigDecimal;

public class EWallet extends Wallet {

    private String userId;
    private String teamId;

    public EWallet() {}

    public EWallet(String userId, String teamId, BigDecimal amount, Boolean isPercent) {
        super(BigDecimal.ZERO, amount, isPercent);
        this.userId = userId;
        this.teamId = teamId;
    }

    public EWallet(String userId, String teamId, BigDecimal balance, BigDecimal amount, Boolean isPercent) {
        super(balance, amount, isPercent);
        this.userId = userId;
        this.teamId = teamId;
    }

    public String getUserId() {
        return userId;
    }

    public String getTeamId() {
        return teamId;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }

    public String toString() {
		return GsonUtil.getGson().toJson(this);
	}
}

