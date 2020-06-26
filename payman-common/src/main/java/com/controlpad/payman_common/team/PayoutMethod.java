package com.controlpad.payman_common.team;

import org.apache.commons.lang3.StringUtils;

public enum PayoutMethod {
    NONE("none"),
    FILE("file"),
    SUB_ACCOUNT("sub-account"),
    PAYMENT_BATCH("payment-batch"),
    PAYMENT_BATCH_MANUAL("payment-batch-manual"),
    PAYMENT_PROVIDER("payment-provider")
    ;

    private String slug;

    PayoutMethod(String slug) {
        this.slug = slug;
    }

    public String getSlug() {
        return slug;
    }

    public static PayoutMethod findBySlug(String slug) {
        if (slug == null) {
            return NONE;
        }
        for(PayoutMethod scheme : values()) {
            if (StringUtils.equalsIgnoreCase(scheme.slug, slug)) {
                return scheme;
            }
        }
        return NONE;
    }
}
