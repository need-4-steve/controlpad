/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.api_key;

import com.controlpad.pay_fac.interceptor.APIKeyPermissions;
import com.controlpad.pay_fac.util.ResponseUtil;
import com.controlpad.payman_common.util.GsonUtil;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.joda.time.DateTime;

import javax.validation.Valid;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Null;

public class APIKey {

    private String id;
    @Null(message = "clientId not allowed to be set during create", groups = AlwaysCheck.class)
    private String clientId;
    @Valid
    @NotNull(message = "config required", groups = AlwaysCheck.class)
    private APIKeyConfig config;
    private Boolean disabled;
    private DateTime createdAt;

    public APIKey() {}

    public APIKey(String id, String clientId, APIKeyConfig config) {
        this.id = id;
        this.clientId = clientId;
        this.config = config;
    }

    public APIKey(String id, String clientId, String config, Boolean disabled, DateTime createdAt) {
        this.id = id;
        this.clientId = clientId;
        this.config = GsonUtil.getGson().fromJson(config, APIKeyConfig.class);
        this.disabled = disabled;
        this.createdAt = createdAt;
    }

    public String getId() {
        return id;
    }

    public String getClientId() {
        return clientId;
    }

    public APIKeyConfig getConfig() {
        return config;
    }

    public Boolean getDisabled() {
        return disabled;
    }

    public DateTime getCreatedAt() {
        return createdAt;
    }

    public void setConfig(APIKeyConfig config) {
        this.config = config;
    }

    public void setId(String id) {
        this.id = id;
    }

    public void setClientId(String clientId) {
        this.clientId = clientId;
    }

    public void verifyPermissions(APIKeyPermissions apiKeyPermissions) {
        if (apiKeyPermissions == null)
            return;

        if (apiKeyPermissions.createPaymentFile() && !config.getCreatePaymentFile()) {
            throw ResponseUtil.getInsufficientPrivileges("api key missing required permission: createPaymentFile");
        }
        if (apiKeyPermissions.processSales() && !config.getProcessSales()) {
            throw ResponseUtil.getInsufficientPrivileges("api key missing required permission: processSales");
        }
        if (apiKeyPermissions.updateAccounts() && !config.getUpdateAccounts()) {
            throw ResponseUtil.getInsufficientPrivileges("api key missing required permission: updateAccounts");
        }
    }

    @Override
    public String toString() {
        return GsonUtil.getGson().toJson(this);
    }
}
