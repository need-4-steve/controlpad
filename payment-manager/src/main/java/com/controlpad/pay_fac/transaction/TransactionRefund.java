package com.controlpad.pay_fac.transaction;


import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.common.RequestBodyInit;
import com.controlpad.payman_common.transaction.Card;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.util.GsonUtil;
import com.controlpad.payman_common.validation.AlwaysCheck;
import com.controlpad.payman_common.validation.PostChecks;

import javax.validation.Valid;
import javax.validation.constraints.DecimalMin;
import javax.validation.constraints.NotNull;
import java.math.BigDecimal;
import java.math.RoundingMode;
import java.util.List;

public class TransactionRefund implements RequestBodyInit {

    @DecimalMin(value = "0.00", message = "subtotal must be positive", groups = AlwaysCheck.class)
    private BigDecimal subtotal;
    @NotNull(message = "total required", groups = PostChecks.class)
    @DecimalMin(value = "0.01", message = "total must be more than a penny", groups = AlwaysCheck.class)
    private BigDecimal total;
    private BigDecimal tax;
    private String type;
    private String authUserId;
    private String description;

    @Valid
    private Card card;

    @Valid
    private List<AffiliateCharge> affiliatePayouts;

    public TransactionRefund() {
    }

    public TransactionRefund(BigDecimal total) {
        this.total = total;
    }

    public BigDecimal getSubtotal() {
        return subtotal;
    }

    public BigDecimal getTax() {
        return tax;
    }

    public BigDecimal getTotal() {
        return total;
    }

    public String getType() {
        return type;
    }

    public String getAuthUserId() {
        return authUserId;
    }

    public String getDescription() {
        return description;
    }

    public void setSubtotal(BigDecimal amount) {
        this.subtotal = amount;
    }

    public void setTotal(BigDecimal total) {
        this.total = total;
    }

    public void setTax(BigDecimal tax) {
        this.tax = tax;
    }

    public void setType(String type) {
        this.type = type;
    }

    public void setAuthUserId(String authUserId) {
        this.authUserId = authUserId;
    }

    public Card getCard() {
        return card;
    }

    public List<AffiliateCharge> getAffiliatePayouts() {
        return affiliatePayouts;
    }

    public Transaction asTransaction() {
        Transaction transaction = new Transaction(null, null, null, null,
                null, type, total, tax, null, null, null, description);

        transaction.setAffiliatePayouts(affiliatePayouts);

        return transaction;
    }

    @Override
    public String toString() {
        return GsonUtil.getGson().toJson(this);
    }

    @Override
    public void initRequestBody() {
        if (this.total != null) {
            this.total = this.total.setScale(2, RoundingMode.HALF_UP);
        }
        if (this.tax != null) {
            this.tax = this.tax.setScale(2, RoundingMode.HALF_UP);
        }
        if (this.subtotal != null) {
            this.subtotal = this.subtotal.setScale(2, RoundingMode.HALF_UP);
        }
        if (affiliatePayouts != null) {
            for (AffiliateCharge affiliatePayout : affiliatePayouts) {
                affiliatePayout.initRequestBody();
            }
        }
    }
}
