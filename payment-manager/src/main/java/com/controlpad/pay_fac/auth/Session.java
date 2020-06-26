/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.auth;

public class Session {

    /**
     * Also known as SessionKey in Authorization header
     */
    private String id;
    private String userId;
    private String clientId;
    private Long expiresAt;
    private String createdAt;

    public Session() {}

    public Session(String id, String userId, String clientId, Long exipresAt) {
        this.id = id;
        this.userId = userId;
        this.expiresAt = exipresAt;
        this.clientId = clientId;
    }

    public String getId() {
        return id;
    }

    public String getUserId() {
        return userId;
    }

    public String getClientId() {
        return clientId;
    }

    public Long getExpiresAt() {
        return expiresAt;
    }

    public String getCreatedAt() {
        return createdAt;
    }

    public void setExpiresAt(Long expiresAt) {
        this.expiresAt = expiresAt;
    }

    public void setClientId(String clientId) {
        this.clientId = clientId;
    }
}
