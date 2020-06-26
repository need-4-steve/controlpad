package com.controlpad.payman_common.gateway_connection;

import com.controlpad.payman_common.validation.AlwaysCheck;
import com.controlpad.payman_common.validation.PostChecks;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.constraints.AssertFalse;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Null;

@GatewayTypeValidate(groups = AlwaysCheck.class)
public class GatewayConnection {

    private Long id;
    @NotBlank(message = "teamId required", groups = PostChecks.class)
    private String teamId;
    private String userId;
    @NotBlank(message = "name required", groups = AlwaysCheck.class)
    private String name;
    private String username;
    private String merchantId;
    private String entityId;
    private String privateKey;
    private String publicKey;
    private String pin;
    @NotBlank(message = "type required", groups = AlwaysCheck.class)
    private String type;
    @NotNull(message = "isSandbox required", groups = AlwaysCheck.class)
    private Boolean isSandbox;
    private Boolean fundsCompany = true;
    @NotNull(message = "processCards required", groups = PostChecks.class)
    private Boolean processCards;
    @NotNull(message = "processChecks required", groups = PostChecks.class)
    private Boolean processChecks;
    @NotNull(message = "processInternal required", groups = PostChecks.class)
    private Boolean processInternal;
    @NotNull(message = "active required", groups = PostChecks.class)
    private Boolean active;
    @Null(message = "masterConnectionId isn't allowed to be set", groups = AlwaysCheck.class)
    private Long masterConnectionId;
    private String feeGroupId;  // Used for splash payments
    @AssertFalse(message = "fundsMaster isn't allowed to be set", groups = AlwaysCheck.class)
    private boolean fundsMaster = false;

    @Null(message = "masterConnection is not an accepted field")
    private GatewayConnection masterConnection;

    public GatewayConnection() {}

    public GatewayConnection(String teamId, String userId, String name, String username, String entityId, String merchantId, String privateKey, String type, Boolean isSandbox,
                             Boolean fundsCompany, Boolean processCards, Boolean processChecks, Boolean processInternal,
                             Long masterConnectionId, Boolean fundsMaster, Boolean active) {
        this.teamId = teamId;
        this.userId = userId;
        this.name = name;
        this.username = username;
        this.entityId = entityId;
        this.merchantId = merchantId;
        this.privateKey = privateKey;
        this.type = type;
        this.isSandbox = isSandbox;
        this.fundsCompany = fundsCompany;
        this.processCards = processCards;
        this.processChecks = processChecks;
        this.processInternal = processInternal;
        this.masterConnectionId = masterConnectionId;
        this.fundsMaster = fundsMaster;
        this.active = active;
    }

    public GatewayConnection(String teamId, String userId, String name, String username, String privateKey, String publicKey,
                             String pin, String type, Boolean isSandbox, Boolean fundsCompany, Boolean processCards,
                             Boolean processChecks, Boolean processInternal, Boolean active) {
        this.teamId = teamId;
        this.userId = userId;
        this.name = name;
        this.username = username;
        this.privateKey = privateKey;
        this.publicKey = publicKey;
        this.pin = pin;
        this.type = type;
        this.isSandbox = isSandbox;
        this.fundsCompany = fundsCompany;
        this.processCards = processCards;
        this.processChecks = processChecks;
        this.processInternal = processInternal;
        this.active = active;
    }

    public Long getId() {
        return id;
    }

    public String getName() {
        return name;
    }

    public String getUsername() {
        return username;
    }

    public String getMerchantId() {
        return merchantId;
    }

    public String getEntityId() {
        return entityId;
    }

    public String getPrivateKey() {
        return privateKey;
    }

    public String getPublicKey() {
        return publicKey;
    }

    public String getPin() {
        return pin;
    }

    public String getType() {
        return type;
    }

    public Boolean fundsCompany() {
        return fundsCompany;
    }

    public Boolean processCards() {
        return processCards;
    }

    public Boolean processChecks() {
        return processChecks;
    }

    public Boolean processInternal() {
        return processInternal;
    }

    public Boolean getIsSandbox() {
        return isSandbox;
    }

    public String getUserId() {
        return userId;
    }

    public String getTeamId() {
        return teamId;
    }

    public Long getMasterConnectionId() {
        return masterConnectionId;
    }

    public String getFeeGroupId() {
        return feeGroupId;
    }

    public Boolean fundsMaster() {
        return fundsMaster;
    }

    public void setTeamId(String teamId) {
        this.teamId = teamId;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public void setName(String name) {
        this.name = name;
    }

    public Boolean getActive() {
        return active;
    }

    public GatewayConnection getMasterConnection() {
        return masterConnection;
    }

    public void setUsername(String username) {
        this.username = username;
    }

    public void setPrivateKey(String privateKey) {
        this.privateKey = privateKey;
    }

    public void setPublicKey(String publicKey) {
        this.publicKey = publicKey;
    }

    public void setPin(String pin) {
        this.pin = pin;
    }

    public void setType(String type) {
        this.type = type;
    }

    public void setIsSandbox(Boolean sandbox) {
        isSandbox = sandbox;
    }

    public void setFundsCompany(Boolean solo) {
        fundsCompany = solo;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }

    public void setProcessCards(Boolean processCards) {
        this.processCards = processCards;
    }

    public void setProcessChecks(Boolean processChecks) {
        this.processChecks = processChecks;
    }

    public void setProcessInternal(Boolean processInternal) {
        this.processInternal = processInternal;
    }

    public void setActive(Boolean active) {
        this.active = active;
    }

    public void setMasterConnectionId(Long masterConnectionId) {
        this.masterConnectionId = masterConnectionId;
    }

    public void setFundsMaster(Boolean fundsMaster) {
        this.fundsMaster = fundsMaster;
    }

    public void setMasterConnection(GatewayConnection masterConnection) {
        this.masterConnection = masterConnection;
    }

    public GatewayConnection obscure() {
        pin = (pin == null ? null : "****");
        if (privateKey != null) {
            privateKey = "****" + (privateKey.length() > 8 ? privateKey.substring(privateKey.length() - 5) : "");
        }
        return this;
    }
}
