package com.controlpad.pay_fac.express;


import com.controlpad.pay_fac.auth.LoginObject;
import com.controlpad.payman_common.datasource.SqlConfig;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.Valid;
import javax.validation.constraints.NotNull;

public class ExpressClientRequest {

    @NotBlank(message = "clientName required", groups = AlwaysCheck.class)
    private String clientName;

    private Boolean isSandbox = false;

    @Valid
    private LoginObject user;

    @NotNull(message = "sqlConfig required", groups = AlwaysCheck.class)
    @Valid
    private SqlConfig sqlConfig;

    @Valid
    @NotNull(message = "gatewayConnection required", groups = AlwaysCheck.class)
    private GatewayConnection gatewayConnection;

    public String getClientName() {
        return clientName;
    }

    public void setClientName(String clientName) {
        this.clientName = clientName;
    }

    public Boolean getSandbox() {
        return isSandbox;
    }

    public void setSandbox(Boolean sandbox) {
        isSandbox = sandbox;
    }

    public LoginObject getUser() {
        return user;
    }

    public void setUser(LoginObject user) {
        this.user = user;
    }

    public SqlConfig getSqlConfig() {
        return sqlConfig;
    }

    public void setSqlConfig(SqlConfig sqlConfig) {
        this.sqlConfig = sqlConfig;
    }

    public GatewayConnection getGatewayConnection() {
        return gatewayConnection;
    }

    public void setGatewayConnection(GatewayConnection gatewayConnection) {
        this.gatewayConnection = gatewayConnection;
    }
}
