package com.controlpad.pay_fac.report;


import java.math.BigDecimal;

public class EWalletReport {

    private BigDecimal pendingSalesTotal;
    private Integer pendingSalesCount;
    private BigDecimal pendingTaxTotal;
    private Integer pendingTaxCount;
    private BigDecimal eWalletBalance;

    public EWalletReport() {}

    public EWalletReport(Totals openSales, Totals openTax, BigDecimal eWalletBalance) {
        this.pendingSalesTotal = (openSales != null ? openSales.getTotal() : BigDecimal.ZERO);
        this.pendingSalesCount = (openSales != null ? openSales.getCount() : 0);
        this.pendingTaxTotal = (openTax != null ? openTax.getTotal() : BigDecimal.ZERO);
        this.pendingTaxCount = (openTax != null ? openTax.getCount() : 0);
        this.eWalletBalance = eWalletBalance;
    }

    public BigDecimal getPendingSalesTotal() {
        return pendingSalesTotal;
    }

    public Integer getPendingSalesCount() {
        return pendingSalesCount;
    }

    public BigDecimal getPendingTaxTotal() {
        return pendingTaxTotal;
    }

    public Integer getPendingTaxCount() {
        return pendingTaxCount;
    }

    public BigDecimal geteWalletBalance() {
        return eWalletBalance;
    }
}
