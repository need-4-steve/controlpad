/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.client;

import com.controlpad.payman_common.validation.AlwaysCheck;

import javax.validation.constraints.NotNull;

public class Features {

    @NotNull(message = "features->eWallet required", groups = AlwaysCheck.class)
    private Boolean eWallet;
    @NotNull(message = "features->consignment required", groups = AlwaysCheck.class)
    private Boolean consignment;
    @NotNull(message = "features->teamCredits required", groups = AlwaysCheck.class)
    private Boolean teamCredits;
    @NotNull(message = "features->companyCredits required", groups = AlwaysCheck.class)
    private Boolean companyCredits;
    @NotNull(message = "features->accountValidation required", groups = AlwaysCheck.class)
    private Boolean accountValidation;
    @NotNull(message = "features->refund required", groups = AlwaysCheck.class)
    private Boolean refund;

    public Features() {}

    public Features(Boolean eWallet, Boolean consignment, Boolean teamCredits, Boolean companyCredits, Boolean accountValidation, Boolean refund) {
        this.eWallet = eWallet;
        this.consignment = consignment;
        this.teamCredits = teamCredits;
        this.companyCredits = companyCredits;
        this.accountValidation = accountValidation;
        this.refund = refund;
    }

    public Boolean getEWallet() {
        return eWallet;
    }

    public Boolean getConsignment() {
        return consignment;
    }

    public Boolean getTeamCredits() {
        return teamCredits;
    }

    public Boolean getCompanyCredits() {
        return companyCredits;
    }

    public Boolean getAccountValidation() {
        return accountValidation;
    }

    public Boolean getRefund() {
        return refund;
    }
}