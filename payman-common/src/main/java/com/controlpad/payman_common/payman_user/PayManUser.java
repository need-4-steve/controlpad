package com.controlpad.payman_common.payman_user;

import org.apache.commons.lang3.StringUtils;

public class PayManUser {

    private String id;
    private String clientId;
    private String username;
    private String password;
    private String email;
    private String createdAt;

    private Privilege privilege;

    public static PayManUser createProxyUser(String clientId) {
        int privilege = (clientId == null ? 3 : 7); // Client bound keys have less access
        return new PayManUser(
                null,
                clientId,
                "proxy-user",
                null,
                null,
                new Privilege(
                        false,
                        true,
                        privilege,
                        privilege,
                        privilege
                )
        );
    }

    public PayManUser() {

    }

    public PayManUser(String id, String clientId, String username, String password, String email) {
        this.id = id;
        this.clientId = clientId;
        this.username = username;
        this.password = password;
        this.email = email;
    }

    public PayManUser(String id, String clientId, String username, String password, String email, Privilege privilege) {
        this.id = id;
        this.clientId = clientId;
        this.username = username;
        this.password = password;
        this.email = email;
        this.privilege = privilege;
    }

    public String getId() {
        return id;
    }

    public String getClientId() {
        return clientId;
    }

    public String getUsername() {
        return username;
    }

    public String getPassword() {
        return password;
    }

    public String getEmail() {
        return email;
    }

    public String getCreatedAt() {
        return createdAt;
    }

    public Privilege getPrivilege() {
        return privilege;
    }

    public void setClientId(String clientId) {
        this.clientId = clientId;
    }

    public void setId(String id) {
        this.id = id;
    }

    public boolean canWriteOwner(String userId) {
        return getPrivilege().getWritePrivilege() < 8 || StringUtils.equals(userId, id);
    }

    public boolean canReadOwner(String userId) {
        return getPrivilege().getReadPrivilege() < 8 || StringUtils.equals(userId, id);
    }

    public boolean canCreateOwner(String userId) {
        return getPrivilege().getCreatePrivilege() < 8 || StringUtils.equals(userId, id);
    }
}
