package com.controlpad.payman_common.team;

import org.apache.commons.lang3.StringUtils;

public enum PayoutScheme {
    NONE("none"),
    MANUAL_SCHEDULE("manual-schedule"), // Can que up jobs through the api
    AUTO_SCHEDULE("auto-schedule"), // Process all payouts per schedule
    AUTO_SCHEDULE_DAILY_WITHDRAW("auto-schedule-daily-withdraw"), // Process payouts per schedule, but also checks daily for withdraw requests
    // Not for team config
    BATCH_TO_PROVIDER("batch-to-provider"),
    USER_ACCOUNT_VALIDATION("user-account-validation") // Used to trigger manual validation file
    ;

    private String slug;

    PayoutScheme(String slug) {
        this.slug = slug;
    }

    public String getSlug() {
        return slug;
    }

    public static PayoutScheme findBySlug(String slug) {
        if (slug == null) {
            return NONE;
        }
        for(PayoutScheme scheme : values()) {
            if (StringUtils.equalsIgnoreCase(scheme.slug, slug)) {
                return scheme;
            }
        }
        return NONE;
    }
}