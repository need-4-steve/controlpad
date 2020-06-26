/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.fee;

import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.common.Charge;
import com.controlpad.payman_common.util.GsonUtil;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.Valid;
import java.math.BigDecimal;

public class Fee extends Charge {

    private Long id;
    @NotBlank(message = "description required", groups = AlwaysCheck.class)
    private String description;
    private Long accountId;
    private String referenceId;
    @Valid
    private Account account;

    public Fee() {}

    public Fee(BigDecimal amount, Boolean isPercent, String description, Account account) {
        super(amount, isPercent);
        this.description = description;
        this.account = account;
    }

    public Long getId() {
        return id;
    }

    public String getDescription() {
        return description;
    }

    public Long getAccountId() {
        if (account != null && account.getId() != null) {
            return account.getId();
        }
        return accountId;
    }

    public Account getAccount() {
        return account;
    }

    public String getReferenceId() {
        return referenceId;
    }

    public void setAccountId(Long accountId) {
        this.accountId = accountId;
    }

    @Override
	public String toString() {
		return GsonUtil.getGson().toJson(this);
	}
}
