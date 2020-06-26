package com.controlpad.payman_common.user_account;

import com.controlpad.payman_common.account.Account;
import org.apache.commons.lang3.StringUtils;

public class UserAccount extends Account {

    private String userId;
    private Boolean validated;
    private String hash;

    public UserAccount() {}

    public UserAccount(String name, String routing, String number, String type, String bankName, Boolean validated) {
        super(name, routing, number, type, bankName);
        this.validated = validated;
    }

    public UserAccount(String userId, String name, String routing, String number, String type, String bankName, Boolean validated) {
        super(name, routing, number, type, bankName);
        this.userId = userId;
        this.validated = validated;
    }

    public String getUserId() {
        return userId;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }

    public Boolean getValidated() {
        return validated;
    }

    public void setValidated(Boolean validated) {
        this.validated = validated;
    }

    public boolean equals(UserAccount account) {
        return StringUtils.equals(account.getName(), getName()) && StringUtils.equals(account.getRouting(), getRouting())
                && StringUtils.equals(account.getNumber(), getNumber()) && StringUtils.equals(account.getType(), getType())
                && StringUtils.equals(account.getBankName(), getBankName()) && account.getValidated() == validated;
    }
}
