package com.controlpad.pay_fac.gateway.splash_payments;

import com.controlpad.payman_common.address.Address;
import com.controlpad.payman_common.common.RequestBodyInit;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.Length;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.Valid;
import javax.validation.constraints.DecimalMax;
import javax.validation.constraints.DecimalMin;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Pattern;
import java.math.BigDecimal;
import java.math.RoundingMode;

public class BusinessMember implements RequestBodyInit {

    @NotBlank(message = "owner.firstName required", groups = AlwaysCheck.class)
    private String firstName;
    @NotBlank(message = "owner.lastName required", groups = AlwaysCheck.class)
    private String lastName;
    @NotBlank(message = "owner.dob required", groups = AlwaysCheck.class)
    private String dob;
    @NotNull(message = "owner.ownership percent required", groups = AlwaysCheck.class)
    @DecimalMin(value = "0.00", message = "owner.ownership must be positive", groups = AlwaysCheck.class)
    @DecimalMax(value = "100.00", message = "owner.ownership must be below 100", groups = AlwaysCheck.class)
    private BigDecimal ownership;
    @NotBlank(message = "owner.ssn required", groups = AlwaysCheck.class)
    @Length(min = 9, max = 9, message = "ssn must have a length of 9", groups = AlwaysCheck.class)
    @Pattern(regexp = "^[0-9]*$", message = "ssn should be numeric only", groups = AlwaysCheck.class)
    private String ssn;
    private String dl;
    private String dlState;
    @NotNull(message = "owner.address required", groups = AlwaysCheck.class)
    @Valid
    private Address address;
    @NotBlank(message = "owner.email required", groups = AlwaysCheck.class)
    private String email;
    @NotBlank(message = "owner.phone required", groups = AlwaysCheck.class)
    @Pattern(regexp = "^[0-9]{10,13}$", message = "owner.phone should be 10 to 13 digits", groups = AlwaysCheck.class)
    private String phone;

    public String getFirstName() {
        return firstName;
    }

    public String getLastName() {
        return lastName;
    }

    public String getDob() {
        return dob;
    }

    public BigDecimal getOwnership() {
        return ownership;
    }

    public String getSsn() {
        return ssn;
    }

    public String getDl() {
        return dl;
    }

    public String getDlState() {
        return dlState;
    }

    public Address getAddress() {
        return address;
    }

    public String getEmail() {
        return email;
    }

    public String getPhone() {
        return phone;
    }

    @Override
    public void initRequestBody() {
        if (this.ownership != null) {
            this.ownership = this.ownership.setScale(4, RoundingMode.HALF_UP);
        }
    }

    public void obscure() {
        this.ssn = null;
    }
}
