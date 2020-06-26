package com.controlpad.payman_common.migration;


import org.apache.commons.lang3.StringUtils;

public enum DatabaseType {
    PAYMAN("payman", 7L),
    PAYMAN_CLIENT("payman_client", 26L);

    private String slug;
    private Long maxVersion;

    DatabaseType(String slug, Long maxVersion) {
        this.slug = slug;
        this.maxVersion = maxVersion;
    }

    public String getSlug() {
        return slug;
    }

    public Long getMaxVersion() {
        return maxVersion;
    }

    public static DatabaseType findForSlug(String slug) {
        for (DatabaseType databaseType : values()) {
            if (StringUtils.equals(databaseType.getSlug(), slug)) {
                return databaseType;
            }
        }
        return null;
    }
}
