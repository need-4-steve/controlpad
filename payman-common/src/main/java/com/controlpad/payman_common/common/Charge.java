/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.common;

import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.validation.AlwaysCheck;
import com.controlpad.payman_common.validation.PostChecks;

import javax.validation.constraints.Min;
import javax.validation.constraints.NotNull;
import java.math.BigDecimal;
import java.math.RoundingMode;

@ChargePercentValidate(groups = AlwaysCheck.class)
public class Charge implements RequestBodyInit {

    @NotNull(message = "amount required", groups = PostChecks.class)
    @Min(value = 0, message = "amount must be positive", groups = AlwaysCheck.class)
    private BigDecimal amount;
    @NotNull(message = "isPercent required", groups = PostChecks.class)
    private Boolean isPercent;

    public Charge() {}

    public Charge(BigDecimal amount, Boolean isPercent) {
        this.amount = amount;
        this.isPercent = isPercent;
    }

    public BigDecimal getAmount() {
        return amount;
    }

    public Boolean getPercent() {
        return isPercent;
    }

    public BigDecimal getConvertedAmount() {
        if (amount != null && isPercent) {
            return amount.divide(BigDecimal.valueOf(100), 5, RoundingMode.HALF_UP);
        } else {
            return amount;
        }
    }

    public BigDecimal calculateChargeAmount(Transaction transaction) {
        return calculateChargeAmount(transaction.getAmount());
    }

    public BigDecimal calculateChargeForSubtotal(Transaction transaction) {
        return calculateChargeAmount(transaction.getSubTotal());
    }

    public BigDecimal calculateChargeAmount(BigDecimal amount) {
        if (getPercent()) {
            return getConvertedAmount().multiply(amount).setScale(5, RoundingMode.HALF_UP);
        } else {
            return getAmount();
        }
    }

    public boolean hasAmount() {
        return amount != null && isPercent != null;
    }

    public boolean isPercentValid() {
        if (amount == null && isPercent == null) {
            return true;
        }
        if (amount == null ^ isPercent == null) {
            return false;
        }
        if (isPercent) {
            if (amount.compareTo(BigDecimal.ZERO) < 0) {
                return false;
            } else if (amount.compareTo(BigDecimal.valueOf(100)) > 0) {
                return false;
            }
        }
        return true;
    }

    @Override
    public void initRequestBody() {
        // We allow percent to go to basis points but normal value is in cents
        if (amount != null) {
            amount = amount.setScale((isPercent ? 4 : 2), RoundingMode.HALF_UP);
        }
    }
}
