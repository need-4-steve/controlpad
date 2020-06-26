package com.controlpad.payman_common.transaction;

import org.apache.commons.lang3.StringUtils;

public enum TransactionType {
    UNKNOWN(0, "unknown"),
    CASH_SALE(1, "cash-sale"),
    CHECK_SALE(2, "check-sale"),
    CREDIT_CARD_SALE(3, "credit-card-sale"),
    DEBIT_CARD_SALE(4, "debit-card-sale"),
    CARD_SWIPE_SALE(5, "card_swipe_sale"),
    CHECK_SUB(6, "check-sub"),
    CREDIT_CARD_SUB(7, "credit-card-sub"),
    DEBIT_CARD_SUB(8, "debit-card-sub"),
    E_WALLET_SALE(10, "e-wallet-sale"),
    E_WALLET_TRANSFER(11, "e-wallet-transfer"),
    E_WALLET_CREDIT(12, "e-wallet-credit"),
    E_WALLET_DEPOSIT(13, "e-wallet-deposit"), // Currently unsupported/undefined
    E_WALLET_SUB(14, "e-wallet-sub"),
    E_WALLET_WITHDRAW(15, "e-wallet-withdraw"),
    E_WALLET_DEBIT(16, "e-wallet-debit"), // Stuff like declaring a manual payment, or providing a fix
    TEAM_CREDITS_SALE(20, "team-credits-sale"),
    TEAM_CREDITS_CREDIT(21, "team-credits-credit"),
    TEAM_CREDITS_TRANSFER(22, "team-credits-transfer"),
    COMPANY_CREDITS_SALE(30, "company-credits-sale"),
    COMPANY_CREDITS_CREDIT(31, "company-credits-credit"),
    COMPANY_CREDITS_TRANSFER(32, "company-credits-transfer"),
    PAYPAL_SALE(40, "paypal-sale"),
    E_CHECK_DEPOSIT_E_WALLET(50, "e-check-deposit-e-wallet"),
    ACH_DEPOSIT_E_WALLET(51, "ach-deposit-e-wallet"),
    E_CHECK_PAYMENT_TAX(60, "e-check-payment-tax"),
    ACH_PAYMENT_TAX(61, "ach-payment-tax"),
    E_WALLET_PAYMENT_TAX(62, "e-wallet-payment-tax"),
    CARD_PAYMENT_TAX(63, "card-payment-tax"),
    CREDIT_CARD_SHIPPING(70, "credit-card-shipping"),
    DEBIT_CARD_SHIPPING(71, "debit-card-shipping"),
    E_CHECK_SHIPPING(73, "e-check-shipping"),
    ACH_SHIPPING(74, "ach-shipping"),
    E_WALLET_SHIPPING(75, "e-wallet-shipping"),
    REFUND(90, "refund"),
    REFUND_CASH(91, "cash-refund"),
    REFUND_TAX_PAYMENT(92, "tax-payment-refund"),
    VOID(94, "void");

    public final String slug;
    public final int id;

    TransactionType(int id, String slug) {
        this.id = id;
        this.slug = slug;
    }

    @Override
    public String toString() {
        return slug;
    }

    public static TransactionType findBySlug(String slug) {
        for (TransactionType transactionType : TransactionType.values()) {
            if (StringUtils.equalsIgnoreCase(transactionType.slug, slug)) {
                return transactionType;
            }
        }
        return UNKNOWN;
    }
}
