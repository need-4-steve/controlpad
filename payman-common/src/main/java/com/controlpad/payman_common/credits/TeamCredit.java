package com.controlpad.payman_common.credits;

import com.controlpad.payman_common.validation.AlwaysCheck;
import com.controlpad.payman_common.validation.PostChecks;

import javax.validation.constraints.NotNull;
import javax.validation.constraints.Null;
import java.math.BigDecimal;

public class TeamCredit {

    private String userId;
    @NotNull(message = "teamId required", groups = PostChecks.class)
    private String teamId;
    @Null(message = "Balance cannot be updated directly. Please use transactions.", groups = AlwaysCheck.class)
    private BigDecimal balance;

    public TeamCredit() {}

    public TeamCredit(String userId, String teamId, BigDecimal balance) {
        this.userId = userId;
        this.teamId = teamId;
        this.balance = balance;
    }

    public String getUserId() {
        return userId;
    }

    public String getTeamId() {
        return teamId;
    }

    public BigDecimal getBalance() {
        return balance;
    }

    public void setBalance(BigDecimal balance) {
        this.balance = balance;
    }
}