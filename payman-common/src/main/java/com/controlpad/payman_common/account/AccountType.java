/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.account;

import org.apache.commons.lang3.StringUtils;

public enum AccountType {
    CHECKING(1, "checking", 22, 27),
    SAVINGS(2, "savings", 32, 37),
    UNKNOWN(-1, "", -1, -1);

    public final int id;
    public final String slug;
    public final int creditTransactionCode;
    public final int debitTransactionCode;

    AccountType(int id, String slug, int creditTransactionCode, int debitTransactionCode) {
        this.id = id;
        this.slug = slug;
        this.creditTransactionCode = creditTransactionCode;
        this.debitTransactionCode = debitTransactionCode;
    }

    public static AccountType getTypeForName(String slug) {
        for (AccountType accountType : AccountType.values()) {
            if (StringUtils.equalsIgnoreCase(accountType.slug, slug))
                return accountType;
        }
        return UNKNOWN;
    }

    public static AccountType getTypeForTransactionCode(int code) {
        for (AccountType accountType : AccountType.values()) {
            if (accountType.creditTransactionCode == code) {
                return accountType;
            }
        }
        return UNKNOWN;
    }

    public static int getIdForType(String type) {
        return getTypeForName(type).id;
    }

    public static AccountType getTypeForId(Integer id) {
        if (id == null)
            return UNKNOWN;

        for (AccountType type : values()) {
            if (type.id == id)
                return type;
        }
        return UNKNOWN;
    }
}
