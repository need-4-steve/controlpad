package com.controlpad.payman_common.team;

import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.constraints.NotNull;
import java.math.BigDecimal;

public class TeamConfig {

    public static final BigDecimal EWALLET_LIMIT_DEFAULT = new Money(3000L);

    /**
     * If false the merchant payouts will not be exported
     */
    @NotNull(message = "merchantPayouts required", groups = AlwaysCheck.class)
    private Boolean merchantPayouts;

    /**
     * If True, extra money from payouts will automatically generate a payment to the merchant.
     * If a valid user account exists.
     */
    @NotNull
    private Boolean autoMerchantPayout;

    /**
     * If false the tax money will pay to the merchant. If true it will be sent to a tax account
     * Team.taxAccount will need set for this
     */
    @NotNull(message = "collectSalesTax required", groups = AlwaysCheck.class)
    private Boolean collectSalesTax;

    /**
     * Tax owed from sales that don't fund an internal account such as cash sale
     * Should only be used when collectSalesTax is true
     */
    @NotNull(message = "autoDeductOwedTax required", groups = AlwaysCheck.class)
    private Boolean autoDeductOwedTax;

    @NotNull(message = "userGatewayConnections required", groups = AlwaysCheck.class)
    private Boolean userGatewayConnections;

    @NotNull
    private BigDecimal eWalletLimit;

    @NotBlank
    private String payoutScheme;

    private String companyPayoutMethod;
    private String merchantPayoutMethod;

    private String quickPayType;

    public TeamConfig() {}

    public TeamConfig(Boolean merchantPayouts, Boolean autoMerchantPayout, Boolean collectSalesTax, Boolean autoDeductOwedTax,
                      Boolean userGatewayConnections, BigDecimal eWalletLimit, String payoutScheme) {
        this.merchantPayouts = merchantPayouts;
        this.autoMerchantPayout = autoMerchantPayout;
        this.collectSalesTax = collectSalesTax;
        this.autoDeductOwedTax = autoDeductOwedTax;
        this.userGatewayConnections = userGatewayConnections;
        this.eWalletLimit = eWalletLimit;
        this.payoutScheme = payoutScheme;
    }

    public TeamConfig(Boolean merchantPayouts, Boolean autoMerchantPayout, Boolean collectSalesTax, Boolean autoDeductOwedTax, Boolean userGatewayConnections, BigDecimal eWalletLimit, String payoutScheme, String companyPayoutMethod, String merchantPayoutMethod, String quickPayType) {
        this.merchantPayouts = merchantPayouts;
        this.autoMerchantPayout = autoMerchantPayout;
        this.collectSalesTax = collectSalesTax;
        this.autoDeductOwedTax = autoDeductOwedTax;
        this.userGatewayConnections = userGatewayConnections;
        this.eWalletLimit = eWalletLimit;
        this.payoutScheme = payoutScheme;
        this.companyPayoutMethod = companyPayoutMethod;
        this.merchantPayoutMethod = merchantPayoutMethod;
        this.quickPayType = quickPayType;
    }

    public Boolean getMerchantPayouts() {
        return merchantPayouts;
    }

    public Boolean getAutoMerchantPayout() {
        return autoMerchantPayout;
    }

    public Boolean getCollectSalesTax() {
        return collectSalesTax;
    }

    public Boolean getAutoDeductOwedTax() {
        return autoDeductOwedTax;
    }

    public Boolean getUserGatewayConnections() {
        return userGatewayConnections;
    }

    public BigDecimal geteWalletLimit() {
        return eWalletLimit;
    }

    public String getPayoutScheme() {
        return payoutScheme;
    }

    public String getCompanyPayoutMethod() {
        return companyPayoutMethod;
    }

    public String getMerchantPayoutMethod() {
        return merchantPayoutMethod;
    }

    public String getQuickPayType() {
        return quickPayType;
    }

    public void setMerchantPayouts(Boolean merchantPayouts) {
        this.merchantPayouts = merchantPayouts;
    }

    public void setCollectSalesTax(Boolean collectSalesTax) {
        this.collectSalesTax = collectSalesTax;
    }

    public void setUserGatewayConnections(Boolean userGatewayConnections) {
        this.userGatewayConnections = userGatewayConnections;
    }

    public void seteWalletLimit(BigDecimal eWalletLimit) {
        this.eWalletLimit = eWalletLimit;
    }

    public void setPayoutScheme(String payoutScheme) {
        this.payoutScheme = payoutScheme;
    }

    public void setAutoMerchantPayout(Boolean autoMerchantPayout) {
        this.autoMerchantPayout = autoMerchantPayout;
    }

    public void setAutoDeductOwedTax(Boolean autoDeductOwedTax) {
        this.autoDeductOwedTax = autoDeductOwedTax;
    }

    public void setQuickPayType(String quickPayType) {
        this.quickPayType = quickPayType;
    }

    public void setCompanyPayoutMethod(String companyPayoutMethod) {
        this.companyPayoutMethod = companyPayoutMethod;
    }

    public void setMerchantPayoutMethod(String merchantPayoutMethod) {
        this.merchantPayoutMethod = merchantPayoutMethod;
    }
}