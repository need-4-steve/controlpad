package com.controlpad.payman_common.transaction;

import com.controlpad.payman_common.common.RequestBodyInit;
import com.controlpad.payman_common.util.GsonUtil;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.constraints.Min;
import javax.validation.constraints.NotNull;
import java.math.BigDecimal;
import java.math.RoundingMode;

public class TransferPayment implements RequestBodyInit {

    @NotNull(message = "amount required", groups = AlwaysCheck.class)
    @Min(value = 0, message = "amount must be positive", groups = AlwaysCheck.class)
    private BigDecimal amount;
    private String description;
    @NotBlank(message = "payeeUserId required", groups = AlwaysCheck.class)
    private String payeeUserId;
    @NotBlank(message = "payerUserId required", groups = AlwaysCheck.class)
    private String payerUserId;
    @NotNull(message = "teamId required", groups = AlwaysCheck.class)
    private String teamId;

    public TransferPayment() {}

    public TransferPayment(BigDecimal amount, String description, String payeeUserId, String payerUserId, String teamId){
        this.amount = amount;
        this.description = description;
        this.payeeUserId = payeeUserId;
        this.payerUserId = payerUserId;
        this.teamId = teamId;
    }

    public String getDescription() {
        if (description != null && description.length() > 128) {
            return description.substring(0, 128);
        }
        return description;
    }

    public BigDecimal getAmount() {
        return amount;
    }

    public String getPayeeUserId() {
        return payeeUserId;
    }

    public String getPayerUserId() {
        return payerUserId;
    }

    public String getTeamId() {
        return teamId;
    }

    public void setTeamId(String teamId) {
        this.teamId = teamId;
    }

    public void setPayerUserId(String payerUserId) {
        this.payerUserId = payerUserId;
    }

    @Override
    public String toString() {
        return GsonUtil.getGson().toJson(this);
    }

    @Override
    public void initRequestBody() {
        if (amount != null) {
            amount = amount.setScale(2, RoundingMode.HALF_UP);
        }
    }
}
