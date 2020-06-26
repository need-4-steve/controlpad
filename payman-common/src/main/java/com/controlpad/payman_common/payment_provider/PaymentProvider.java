package com.controlpad.payman_common.payment_provider;

import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.Valid;
import javax.validation.constraints.NotNull;
import java.math.BigInteger;

public class PaymentProvider {

    private BigInteger id;
    @NotBlank(message = "name required", groups = AlwaysCheck.class)
    private String name;
    @NotBlank(message = "type required", groups = AlwaysCheck.class)
    private String type;
    @NotNull(message = "credentials required", groups = AlwaysCheck.class)
    @Valid
    private PaymentProviderCredentials credentials;
    private String subdomain;

    public PaymentProvider() {
    }

    public PaymentProvider(String name, String type, PaymentProviderCredentials credentials) {
        this.name = name;
        this.type = type;
        this.credentials = credentials;
    }

    public BigInteger getId() {
        return id;
    }

    public String getName() {
        return name;
    }

    public String getType() {
        return type;
    }

    public PaymentProviderCredentials getCredentials() {
        return credentials;
    }

    public String getSubdomain() {
        return subdomain;
    }
}