package com.controlpad.payman_common.payment;


import org.apache.commons.lang3.StringUtils;

public enum PaymentType {
    ACCOUNT_VERIFICATION("account-verification", 1),
    ACH_CREDIT("ach-credit", 2),
    AFFILIATE("affiliate", 3),
    COMMISSION("commission", 4),
    CONSIGNMENT("consignment", 5),
    WITHDRAW("withdraw", 6),
    FEE("fee", 7),
    MERCHANT("merchant", 8),
    SALES_TAX("sales-tax", 9),
    FAILED_PAYMENT("failed-payment", 10),
    TRANSFER("transfer", 11),
    UNKNOWN("", 0);

    public final String slug;
    public final int id;

    PaymentType(String slug, int id) {
        this.slug = slug;
        this.id = id;
    }

    public static PaymentType findForSlug(String slug) {
        for (PaymentType type : values()) {
            if (StringUtils.equals(type.slug, slug)) {
                return type;
            }
        }
        return UNKNOWN;
    }

    @Override
    public String toString() {
        return slug;
    }
}
