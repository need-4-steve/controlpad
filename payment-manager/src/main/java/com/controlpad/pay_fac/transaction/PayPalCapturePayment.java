package com.controlpad.pay_fac.transaction;


import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.common.RequestBodyInit;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.apache.commons.lang3.StringUtils;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.constraints.NotNull;
import java.util.List;

public class PayPalCapturePayment implements RequestBodyInit {

    @NotNull(message = "gatewayConnectionId required")
    private Long gatewayConnectionId;
    @NotBlank(message = "paypalPaymentId required", groups = AlwaysCheck.class)
    String paypalPaymentId;
    @NotBlank(message = "paypalPayerId required", groups = AlwaysCheck.class)
    String paypalPayerId;
    private String payerUserId;
    @NotNull(message = "payeeUserId required", groups = AlwaysCheck.class)
    private String payeeUserId;
    private String firstName;
    private String lastName;
    private List<AffiliateCharge> affiliatePayouts;


    public PayPalCapturePayment() {}

    public String getPaypalPaymentId() {
        return paypalPaymentId;
    }

    public String getPaypalPayerId() {
        return paypalPayerId;
    }

    public String getPayerUserId() {
        return payerUserId;
    }

    public String getPayeeUserId() {
        return payeeUserId;
    }

    public List<AffiliateCharge> getAffiliatePayouts() {
        return affiliatePayouts;
    }

    public String getFirstName() {
        return firstName;
    }

    public String getLastName() {
        return lastName;
    }

    public String getFullName() {
        return StringUtils.join(firstName, " ", lastName);
    }

    public Long getGatewayConnectionId() {
        return gatewayConnectionId;
    }

    @Override
    public void initRequestBody() {
        if (affiliatePayouts != null) {
            for (AffiliateCharge affiliatePayout : affiliatePayouts) {
                affiliatePayout.initRequestBody();
            }
        }
    }
}
