/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.auth;

import com.controlpad.payman_common.validation.PostChecks;
import org.hibernate.validator.constraints.NotBlank;

public class LoginObject {

    @NotBlank(message = "username required", groups = PostChecks.class)
    private String username;
    //Password required, a null password represents a disabled login
    @NotBlank(message = "password required", groups = PostChecks.class)
    private String password;

    public LoginObject() {
    }

    public LoginObject(String username, String password) {
        this.username = username;
        this.password = password;
    }

    public String getUsername() {
        return username;
    }

    public String getPassword() {
        return password;
    }
}
