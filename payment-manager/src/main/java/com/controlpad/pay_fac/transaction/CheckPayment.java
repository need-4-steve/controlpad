package com.controlpad.pay_fac.transaction;

import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.transaction.Payment;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

import java.math.BigDecimal;
import java.util.List;

public class CheckPayment extends Payment {

    @NotBlank(message = "accountName required", groups = AlwaysCheck.class)
    private String accountName;
    @NotBlank(message = "routingNumber required", groups = AlwaysCheck.class)
    private String routingNumber;
    @NotBlank(message = "accountNumber required", groups = AlwaysCheck.class)
    private String accountNumber;
    // TODO validate for authorize.net not blank
    private String accountType;
    private String checkNumber;
    private String licenseState;
    private String licenseNumber;

    private Long gatewayConnectionId;

    public CheckPayment() {}

    public CheckPayment(String payerUserId, String payeeUserId, String teamId, String name, BigDecimal tax,
                        BigDecimal total, String description, String accountName,
                        String routingNumber, String accountNumber, String accountType) {
        super(payerUserId, payeeUserId, teamId, name, tax, null, total, description);
        this.accountName = accountName;
        this.routingNumber = routingNumber;
        this.accountNumber = accountNumber;
        this.accountType = accountType;
    }

    public CheckPayment(String payerUserId, String payeeUserId, String teamId, String firstName, String lastName,
                        BigDecimal tax, BigDecimal shipping, BigDecimal subtotal, String poNumber, String description,
                        List<AffiliateCharge> affiliatePayouts, String accountName, String routingNumber, String accountNumber) {
        super(payerUserId, payeeUserId, teamId, firstName, lastName, tax, shipping, subtotal, poNumber, description, affiliatePayouts);
        this.accountName = accountName;
        this.routingNumber = routingNumber;
        this.accountNumber = accountNumber;
    }

    @Override
    public String getName() {
        return accountName;
    }

    public String getAccountName() { return accountName; }

    public String getRoutingNumber() {
        return routingNumber;
    }

    public String getAccountNumber() {
        return accountNumber;
    }

    public String getCheckNumber() {
        return checkNumber;
    }

    public String getAccountType() {
        return accountType;
    }

    public String getLicenseState() {
        return licenseState;
    }

    public String getLicenseNumber() {
        return licenseNumber;
    }

    public Long getGatewayConnectionId() {
        return gatewayConnectionId;
    }
}
