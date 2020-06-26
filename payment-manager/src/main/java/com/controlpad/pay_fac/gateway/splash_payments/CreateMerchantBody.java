package com.controlpad.pay_fac.gateway.splash_payments;

import com.controlpad.payman_common.validation.AlwaysCheck;

import javax.validation.Valid;
import javax.validation.constraints.NotNull;

public class CreateMerchantBody {

    @NotNull(message = "business required", groups = AlwaysCheck.class)
    @Valid
    private Business business;

    public Business getBusiness() {
        return business;
    }

}
