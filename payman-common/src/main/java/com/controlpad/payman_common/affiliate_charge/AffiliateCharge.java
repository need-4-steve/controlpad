package com.controlpad.payman_common.affiliate_charge;

import com.controlpad.payman_common.common.RequestBodyInit;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.constraints.NotNull;
import javax.validation.constraints.Null;
import java.math.BigDecimal;
import java.math.RoundingMode;

public class AffiliateCharge implements RequestBodyInit {

    @Null(message = "affiliateCharge.transactionId cannot be set", groups = AlwaysCheck.class)
    private String transactionId;
    @NotBlank(message = "affiliateCharge.payeeUserId required", groups = AlwaysCheck.class)
    private String payeeUserId;
    @NotNull(message = "affiliateCharge.amount required", groups = AlwaysCheck.class)
    private BigDecimal amount;

    public AffiliateCharge() {}

    public AffiliateCharge(String transactionId, String payeeUserId, BigDecimal amount) {
        this.transactionId = transactionId;
        this.payeeUserId = payeeUserId;
        this.amount = amount;
    }

    public String getTransactionid() {
        return transactionId;
    }

    public String getPayeeUserId() {
        return payeeUserId;
    }

    public BigDecimal getAmount() {
        return amount;
    }

    public void setTransactionId(String transactionId) {
        this.transactionId = transactionId;
    }

    @Override
    public void initRequestBody() {
        if (amount != null) {
            amount = amount.setScale(5, RoundingMode.HALF_UP);
        }
    }

    public void negateAmount() {
        this.amount = this.amount.negate();
    }
}
