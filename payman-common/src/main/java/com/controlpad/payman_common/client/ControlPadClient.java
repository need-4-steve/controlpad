/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.client;

import com.controlpad.payman_common.datasource.SqlConfig;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.Valid;
import javax.validation.constraints.NotNull;

public class ControlPadClient {

    private Long position;
    private String id;
    private String orgId;
    @NotBlank(message = "name required", groups = AlwaysCheck.class)
    private String name;
    @Valid
    @NotNull(message = "config required", groups = AlwaysCheck.class)
    private ClientConfig config;
    @Valid
    @NotNull(message = "sqlConfigWrite required", groups = AlwaysCheck.class)
    private SqlConfig sqlConfigWrite;
    private SqlConfig sqlConfigRead;
    private String jwtKey;
    private Boolean isSandbox;
    private String createdAt;

    public ControlPadClient() {

    }

    public ControlPadClient(String id, String name, ClientConfig clientConfig) {
        this.id = id;
        this.name = name;
        this.config = clientConfig;
    }

    public ControlPadClient(String id, String name, ClientConfig config, SqlConfig sqlConfig) {
        this.id = id;
        this.name = name;
        this.config = config;
        this.sqlConfigWrite = sqlConfig;
    }

    public ControlPadClient(String id, String name, ClientConfig config, SqlConfig sqlConfig, Boolean isSandbox) {
        this.id = id;
        this.name = name;
        this.config = config;
        this.sqlConfigWrite = sqlConfig;
        this.isSandbox = isSandbox;
    }

    public String getId() {
        return id;
    }

    public String getOrgId() {
        return orgId;
    }

    public String getName() {
        return name;
    }

    public ClientConfig getConfig() {
        return config;
    }

    public String getCreatedAt() {
        return createdAt;
    }

    public SqlConfig getSqlConfigWrite() {
        return sqlConfigWrite;
    }

    public SqlConfig getSqlConfigRead() {
        return sqlConfigRead;
    }

    public String getJwtKey() {
        return jwtKey;
    }

    public Boolean getSandbox() {
        return isSandbox;
    }

    public Long getPosition(){ return position; }

    public void setPosition(Long position){
        this.position = position;
    }

    public void setId(String id) {
        this.id = id;
    }
}
