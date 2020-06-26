package com.controlpad.pay_fac.transaction;


import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.constraints.Min;
import javax.validation.constraints.NotNull;
import java.math.BigDecimal;

public class PayPalCreatePayment {

    @NotNull(message = "gatewayConnectionId required", groups = AlwaysCheck.class)
    private Long gatewayConnectionId;
    @NotNull(message = "payeeUserId required", groups = AlwaysCheck.class)
    private String payeeUserId;
    @Min(value = 0, message = "tax must be positive", groups = AlwaysCheck.class)
    private BigDecimal tax;
    @Min(value = 0, message = "shipping must be positive", groups = AlwaysCheck.class)
    private BigDecimal shipping;
    @NotNull(message = "total required", groups = AlwaysCheck.class)
    @Min(value = 0, message = "total must be positive", groups = AlwaysCheck.class)
    private BigDecimal total;
    private BigDecimal subtotal;
    @NotBlank(message = "cancelUrl required", groups = AlwaysCheck.class)
    private String cancelUrl;
    @NotBlank(message = "processUrl required", groups = AlwaysCheck.class)
    private String processUrl;
    private String description;

    public PayPalCreatePayment() {}

    public PayPalCreatePayment(Long gatewayConnectionId, BigDecimal tax, BigDecimal shipping, BigDecimal subtotal,
                               String cancelUrl, String processUrl, String description) {
        this.gatewayConnectionId = gatewayConnectionId;
        this.tax = tax;
        this.total = subtotal.add(shipping).add(tax);
        this.shipping = shipping;
        this.subtotal = subtotal;
        this.cancelUrl = cancelUrl;
        this.processUrl = processUrl;
        this.description = description;
    }

    public Long getGatewayConnectionId() {
        return gatewayConnectionId;
    }

    public String getPayeeUserId() {
        return payeeUserId;
    }

    public BigDecimal getTax() {
        return tax;
    }

    public BigDecimal getShipping() {
        return shipping;
    }

    public BigDecimal getSubtotal() {
        return subtotal;
    }

    public String getDescription() {
        return description;
    }

    public String getCancelUrl() {
        return cancelUrl;
    }

    public void setCancelUrl(String cancelUrl) {
        this.cancelUrl = cancelUrl;
    }

    public String getProcessUrl() {
        return processUrl;
    }

    public void setProcessUrl(String processUrl) {
        this.processUrl = processUrl;
    }

    public BigDecimal getTotal() {
        return total;
    }

}
