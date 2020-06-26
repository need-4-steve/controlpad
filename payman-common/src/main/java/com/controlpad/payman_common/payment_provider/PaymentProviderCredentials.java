package com.controlpad.payman_common.payment_provider;

import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

public class PaymentProviderCredentials {
    private String id;
    @NotBlank(message = "privateKey required", groups = AlwaysCheck.class)
    private String privateKey;
    private String fundingAccountPublicId; // Used for payquicker
    private Boolean isSandbox;

    public String getId() {
        return id;
    }

    public String getPrivateKey() {
        return privateKey;
    }

    public String getFundingAccountPublicId() {
        return fundingAccountPublicId;
    }

    public Boolean isSandbox() {
        return isSandbox;
    }

}
