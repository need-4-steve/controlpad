package com.controlpad.pay_fac.gateway.splash_payments;

import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.address.Address;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.Length;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.Valid;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Pattern;

public class Business {

    @NotBlank(message = "business.name required", groups = AlwaysCheck.class)
    private String name;
    @NotNull(message = "business.address required", groups = AlwaysCheck.class)
    @Valid
    private Address address;
    @NotNull(message = "business.merchantCategoryCode required", groups = AlwaysCheck.class)
    private Integer merchantCategoryCode; // TODO currently only supporting 5699 until Adam gets us approved for more
    @NotNull(message = "business.type required", groups = AlwaysCheck.class)
    private Integer type; // '0' (sole proprietor), '1' (corporation), '2' (limited liability company), '3' (partnership), '4' (association), '5' (non-profit organization), and '6' (government organization)
    @NotNull(message = "business.ein required", groups = AlwaysCheck.class)
    @Pattern(regexp = "^[A-Za-z0-9]*$", message = "ein must be alphanumeric only", groups = AlwaysCheck.class)
    @Length(min = 9, max = 9, message = "ein must have a length of 9", groups = AlwaysCheck.class)
    private String ein;
    @NotBlank(message = "business.email required", groups = AlwaysCheck.class)
    private String email;
    @NotBlank(message = "business.phone required", groups = AlwaysCheck.class)
    @Pattern(regexp = "^[0-9]{10,13}$", message = "business.phone should be 10 to 13 digits", groups = AlwaysCheck.class)
    private String phone;
    private String dba; // 'doing business as'
    @NotNull(message = "business.established required", groups = AlwaysCheck.class)
    private String established;

    @NotBlank(message = "business.website required", groups = AlwaysCheck.class)
    private String website;

    @NotNull(message = "business.owner required", groups = AlwaysCheck.class)
    @Valid
    BusinessMember owner;

    @NotNull(message = "business.account required", groups = AlwaysCheck.class)
    @Valid
    private Account account;

    public Business() {
    }

    public Business(Account account) {
        this.account = account;
    }

    public String getName() {
        return name;
    }

    public Address getAddress() {
        return address;
    }

    public Integer getMerchantCategoryCode() {
        return merchantCategoryCode;
    }

    public Integer getType() {
        return type;
    }

    public String getEin() {
        return ein;
    }

    public String getEmail() {
        return email;
    }

    public String getPhone() {
        return phone;
    }

    public String getDba() {
        return dba;
    }

    public String getEstablished() {
        return established;
    }

    public String getWebsite() {
        return website;
    }

    public BusinessMember getOwner() {
        return owner;
    }

    public Account getAccount() {
        return account;
    }

    public void obscure() {
        this.ein = null;
        if (owner != null) {
            owner.obscure();
        }
    }

    public void setName(String name) {
        this.name = name;
    }
}
