package com.controlpad.payman_common.team;

import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.validation.PostChecks;
import org.hibernate.validator.constraints.NotBlank;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.validation.Valid;
import javax.validation.constraints.NotNull;
import java.math.BigInteger;

public class Team {

    private static final Logger logger = LoggerFactory.getLogger(Team.class);

    @NotBlank(message = "id required")
    private String id;
    @NotBlank(message = "name required", groups = PostChecks.class)
    private String name;
    private Long accountId;
    private Long taxAccountId;
    private Long consignmentAccountId;
    private BigInteger paymentProviderId;

    @Valid
    private Account account;
    @Valid
    private Account consignmentAccount;
    @Valid
    private Account taxAccount;
    @Valid
    private PayoutSchedule payoutSchedule;
    @Valid
    @NotNull(message = "config required", groups = PostChecks.class)
    private TeamConfig config;
    private String paidOn;

    public Team() {}

    public Team(String id, String name, TeamConfig config) {
        this.id = id;
        this.name = name;
        this.config = config;
    }

    public static Logger getLogger() {
        return logger;
    }

    public String getId() {
        return id;
    }

    public Long getTaxAccountId() {
        return taxAccountId;
    }

    public Account getAccount() {
        return account;
    }

    public Account getConsignmentAccount() {
        return consignmentAccount;
    }

    public Account getTaxAccount() {
        return taxAccount;
    }

    public Long getAccountId() {
        return accountId;
    }

    public Long getConsignmentAccountId() {
        return consignmentAccountId;
    }

    public String getName() {
        return name;
    }

    public PayoutSchedule getPayoutSchedule() {
        return payoutSchedule;
    }

    public String getPaidOn() {
        return paidOn;
    }

    public BigInteger getPaymentProviderId() {
        return paymentProviderId;
    }

    public void setId(String id) {
        this.id = id;
    }

    public void setName(String name) {
        this.name = name;
    }

    public void setAccountId(Long accountId) {
        this.accountId = accountId;
    }

    public void setTaxAccountId(Long taxAccountId) {
        this.taxAccountId = taxAccountId;
    }

    public void setConsignmentAccountId(Long consignmentAccountId) {
        this.consignmentAccountId = consignmentAccountId;
    }

    public void setAccount(Account account) {
        this.account = account;
    }

    public void setConsignmentAccount(Account consignmentAccount) {
        this.consignmentAccount = consignmentAccount;
    }

    public void setTaxAccount(Account taxAccount) {
        this.taxAccount = taxAccount;
    }

    public void setPayoutSchedule(PayoutSchedule payoutSchedule) {
        this.payoutSchedule = payoutSchedule;
    }

    public void setPaymentProviderId(BigInteger paymentProviderId) {
        this.paymentProviderId = paymentProviderId;
    }

    public void setPaidOn(String paidOn) {
        this.paidOn = paidOn;
    }

    public TeamConfig getConfig() {
        return config;
    }
}
