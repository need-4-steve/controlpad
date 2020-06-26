package com.controlpad.payman_common.user_account;

import com.controlpad.payman_common.util.MoneyUtil;

import java.util.Random;


public class UserAccountValidation {

    private Long id;
    private String userId;
    private Long paymentFileId;
    private String amount1;
    private String amount2;
    private UserAccount userAccount;
    private String submittedAt;
    private String accountHash;
    private String createdAt;

    public static UserAccountValidation generateNew(UserAccount userAccount) {
        UserAccountValidation accountValidation = new UserAccountValidation();
        Random random = new Random();

        double amount1, amount2;

        amount1 = (Math.floor(random.nextDouble() * 24) + 1) / 100;
        amount2 = (Math.floor(random.nextDouble() * 24) + 1) / 100;

        accountValidation.setUserId(userAccount.getUserId());
        accountValidation.setAmount1(MoneyUtil.formatMoney(amount1));
        accountValidation.setAmount2(MoneyUtil.formatMoney(amount2));
        accountValidation.setAccountHash(userAccount.getHash());
        accountValidation.setUserAccount(userAccount);
        return accountValidation;
    }

    public Long getId() {
        return id;
    }

    public String getUserId() {
        return userId;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }

    public Long getPaymentFileId() {
        return paymentFileId;
    }

    public String getAmount1() {
        return amount1;
    }

    public String getAmount2() {
        return amount2;
    }

    public String getSubmittedAt() {
        return submittedAt;
    }

    public void setAmount1(String amount1) {
        this.amount1 = amount1;
    }

    public void setAmount2(String amount2) {
        this.amount2 = amount2;
    }

    public String getAccountHash() {
        return accountHash;
    }

    public void setAccountHash(String accountHash) {
        this.accountHash = accountHash;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public void setPaymentFileId(Long paymentFileId) {
        this.paymentFileId = paymentFileId;
    }

    public void setSubmittedAt(String submittedAt) {
        this.submittedAt = submittedAt;
    }

    public UserAccount getUserAccount() {
        return userAccount;
    }

    public void setUserAccount(UserAccount userAccount) {
        this.userAccount = userAccount;
    }

    public String getCreatedAt() {
        return createdAt;
    }
}
