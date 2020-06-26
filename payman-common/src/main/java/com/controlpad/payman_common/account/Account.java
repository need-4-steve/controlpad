/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.account;

import com.controlpad.payman_common.util.GsonUtil;
import com.controlpad.payman_common.util.MoneyUtil;
import com.controlpad.payman_common.validation.AlwaysCheck;
import com.controlpad.payman_common.validation.PostChecks;
import org.apache.commons.codec.digest.DigestUtils;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.constraints.Size;

@AccountFieldsValidate(groups = AlwaysCheck.class)
public class Account {

    private Long id;
    @NotBlank(message = "name required", groups = PostChecks.class)
    private String name;
    @NotBlank(message = "routing required", groups = AlwaysCheck.class)
    @Size(min = 9, max = 9)
    private String routing;
    @NotBlank(message = "number required", groups = AlwaysCheck.class)
    private String number;
    @NotBlank(message = "type required", groups = AlwaysCheck.class)
    private String type;
    private String bankName = "";
    private String createdAt;
    private String updatedAt;

    public Account() {}

    public Account(String name, String routing, String number, String type) {
        this.name = name;
        this.routing = routing;
        this.number = number;
        this.type = type;
    }

    public Account(String name, String routing, String number, String type, String bankName) {
        this.name = name;
        this.routing = routing;
        this.number = number;
        this.type = type;
        this.bankName = bankName;
    }

    String formatNumber() {
        number = MoneyUtil.formatAccountNumber(number);
        return number;
    }

    public Long getId() {
        return id;
    }

    public String getName() {
        return name;
    }

    public String getRouting() {
        return routing;
    }

    public String getNumber() {
        return number;
    }

    public String getType() {
        return type;
    }

    public String getBankName() {return bankName;}

    public void setId(Long id) {
        this.id = id;
    }

    public String getHash() {
        return DigestUtils.sha256Hex(DigestUtils.sha256Hex(DigestUtils.sha512(routing + number + type.toLowerCase())));
    }

    public void obscure() {
        if (number != null) {
            number = "*****" + (number.length() > 6 ? number.substring(number.length() - 4) : "");
        }
    }

    @Override
    public String toString() {
        return GsonUtil.getGson().toJson(this);
    }
}