package com.controlpad.pay_fac.transaction;


import com.controlpad.payman_common.transaction.TransferPayment;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

import java.math.BigDecimal;

public class CheckTransfer extends TransferPayment {

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

    public CheckTransfer() {
    }

    public CheckTransfer(BigDecimal amount, String description, String payeeUserId, String payerUserId, String teamId,
                         String accountName, String routingNumber, String accountNumber, String accountType) {
        super(amount, description, payeeUserId, payerUserId, teamId);
        this.accountName = accountName;
        this.routingNumber = routingNumber;
        this.accountNumber = accountNumber;
        this.accountType = accountType;
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

    public CheckPayment convertToCheckPayment() {
        return new CheckPayment(getPayerUserId(), getPayeeUserId(), getTeamId(), accountName, null,
                getAmount(), null, getDescription(), accountName, routingNumber, accountNumber);
    }
}
