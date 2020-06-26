package com.controlpad.pay_fac.gateway.splash_payments;

import com.controlpad.payman_common.address.Address;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.Valid;

public class SplashUser {

    @NotBlank(message = "username required", groups = AlwaysCheck.class)
    private String username;
    //Password required, a null password represents a disabled login
    @NotBlank(message = "password required", groups = AlwaysCheck.class)
    private String password;
    @NotBlank(message = "firstName required", groups = AlwaysCheck.class)
    private String firstName;
    @NotBlank(message = "lastName required", groups = AlwaysCheck.class)
    private String lastName;
    @NotBlank(message = "email required", groups = AlwaysCheck.class)
    private String email;
    private String phone;
    @Valid
    private Address address;

    public String getUsername() {
        return username;
    }

    public String getPassword() {
        return password;
    }

    public String getFirstName() {
        return firstName;
    }

    public String getLastName() {
        return lastName;
    }

    public String getEmail() {
        return email;
    }

    public String getPhone() {
        return phone;
    }

    public Address getAddress() {
        return address;
    }
}
