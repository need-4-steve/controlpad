package com.controlpad.payman_common.fee;


import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;

public class TeamFeeSet {

    private String teamId;
    private String transactionType;
    @NotBlank(message = "description required", groups = AlwaysCheck.class)
    private String description;
    @NotNull(message = "feeIds required", groups = AlwaysCheck.class)
    @Size(min = 1, message = "feeIds can't be empty", groups = AlwaysCheck.class)
    private FeeIds feeIds;

    public TeamFeeSet() {
    }

    public TeamFeeSet(String teamId, String transactionType, String description, FeeIds feeIds) {
        this.teamId = teamId;
        this.transactionType = transactionType;
        this.description = description;
        this.feeIds = feeIds;
    }

    public String getTeamId() {
        return teamId;
    }

    public String getTransactionType() {
        return transactionType;
    }

    public String getDescription() {
        return description;
    }

    public FeeIds getFeeIds() {
        return feeIds;
    }

    public void setTeamId(String teamId) {
        this.teamId = teamId;
    }

    public void setTransactionType(String transactionType) {
        this.transactionType = transactionType;
    }
}