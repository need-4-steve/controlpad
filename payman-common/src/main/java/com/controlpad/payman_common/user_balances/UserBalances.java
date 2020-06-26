package com.controlpad.payman_common.user_balances;


import com.controlpad.payman_common.common.Money;

import javax.validation.constraints.NotNull;
import java.math.BigDecimal;
import java.math.RoundingMode;

public class UserBalances {

    private Long id;
    private String userId;
    private String teamId;
    private BigDecimal salesTax;
    private BigDecimal eWallet; // Not intended for use with a single merchant using multiple gateways on the same team
    private BigDecimal transaction;

    public UserBalances() {}

    public UserBalances(String userId, String teamId) {
        this.userId = userId;
        this.teamId = teamId;
        this.salesTax = BigDecimal.ZERO;
        this.eWallet = BigDecimal.ZERO;
        this.transaction = BigDecimal.ZERO;
    }

    public UserBalances(String userId, String teamId, @NotNull BigDecimal salesTax, @NotNull BigDecimal eWallet, @NotNull BigDecimal transaction) {
        this.userId = userId;
        this.teamId = teamId;
        this.salesTax = salesTax;
        this.eWallet = eWallet;
        this.transaction = transaction;
    }

    public UserBalances(String userId, String teamId, @NotNull Double salesTax, @NotNull Double eWallet, @NotNull Double transaction) {
        this.userId = userId;
        this.teamId = teamId;
        this.salesTax = new Money(salesTax);
        this.eWallet = new Money(eWallet);
        this.transaction = BigDecimal.valueOf(transaction).setScale(5, RoundingMode.HALF_UP);
    }

    public UserBalances(Long id, String userId, String teamId, BigDecimal salesTax, BigDecimal eWallet, BigDecimal transaction) {
        this.id = id;
        this.userId = userId;
        this.teamId = teamId;
        this.salesTax = salesTax;
        this.eWallet = eWallet;
        this.transaction = transaction;
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

    public BigDecimal getSalesTax() {
        return salesTax;
    }

    public void setSalesTax(BigDecimal salesTax) {
        this.salesTax = salesTax;
    }

    public void addSalesTax(BigDecimal salesTax) {
        this.salesTax = this.salesTax.add(salesTax);
    }

    public BigDecimal getEWallet() {
        return eWallet;
    }

    public void setEWallet(BigDecimal eWallet) {
        this.eWallet = eWallet;
    }

    public void addEWallet(BigDecimal eWallet) {
        this.eWallet = this.eWallet.add(eWallet);
    }

    public BigDecimal getTransaction() {
        return transaction;
    }

    public void setTransaction(BigDecimal transaction) {
        this.transaction = transaction;
    }

    public void addTransaction(BigDecimal transaction) {
        this.transaction = this.transaction.add(transaction);
    }

    public String getTeamId() {
        return teamId;
    }

    public void setTeamId(String teamId) {
        this.teamId = teamId;
    }
}
