/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.client;

import com.controlpad.payman_common.validation.AlwaysCheck;

import javax.validation.Valid;
import javax.validation.constraints.NotNull;

public class ClientConfig {

    @Valid
    @NotNull(message = "clientConfig->features required", groups = AlwaysCheck.class)
    private Features features;

    public ClientConfig() {}

    public ClientConfig(Features features) {
        this.features = features;
    }

    public Features getFeatures() {
        return features;
    }
}
