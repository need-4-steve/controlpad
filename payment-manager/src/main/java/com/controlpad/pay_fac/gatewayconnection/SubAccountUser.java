package com.controlpad.pay_fac.gatewayconnection;


import com.controlpad.pay_fac.gateway.splash_payments.Business;
import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.Valid;

public class SubAccountUser {

    @NotBlank(message = "teamId required", groups = AlwaysCheck.class)
    private String teamId;
    @NotBlank(message = "userId required", groups = AlwaysCheck.class)
    private String userId;

    private Long masterGatewayConnectionId;

    @Valid
    private Business business;

    public SubAccountUser() {
    }

    public SubAccountUser(Account account) {
        business = new Business(account);
    }

    public String getTeamId() {
        return teamId;
    }

    public String getUserId() {
        return userId;
    }

    public void setTeamId(String teamId) {
        this.teamId = teamId;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }

    public Business getBusiness() {
        return business;
    }

    public Long getMasterGatewayConnectionId() {
        return masterGatewayConnectionId;
    }
}
