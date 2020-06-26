package com.controlpad.payman_common.datasource;

import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;


public class SqlConfig {

    @NotBlank(message = "SqlConfig->url required", groups = AlwaysCheck.class)
    private String url;
    @NotBlank(message = "SqlConfig->username required", groups = AlwaysCheck.class)
    private String username;
    @NotBlank(message = "SqlConfig->password required", groups = AlwaysCheck.class)
    private String password;
    private String dbType;

    public SqlConfig() {}

    public SqlConfig(String url, String username, String password, String dbType) {
        this.url = url;
        this.username = username;
        this.password = password;
        this.dbType = dbType;
    }

    public String getUrl() {
        return url;
    }

    public void setUrl(String url) {
        this.url = url;
    }

    public String getUsername() {
        return username;
    }

    public void setUsername(String username) {
        this.username = username;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

    public String getDbType() {
        return dbType;
    }

    public void setDbType(String dbType) {
        this.dbType = dbType;
    }
}
