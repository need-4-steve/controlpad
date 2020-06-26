package com.controlpad.pay_fac.report.custom;


import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.payment.PaymentType;

import java.math.BigDecimal;
import java.util.List;

public class MyPayment {

    private String transactionId;
    private String status;
    private String dateOfSale;
    private BigDecimal amount;
    private String cardHolderName;
    private BigDecimal fees;
    private BigDecimal salesTax;
    private BigDecimal shipping;
    private String datePaid;
    private BigDecimal netAmount;
    private BigDecimal consignment;

    public MyPayment() {
    }

    public MyPayment(String status, String dateOfSale, BigDecimal amount, String cardHolderName, BigDecimal fees,
                     BigDecimal salesTax, BigDecimal shipping, String datePaid, BigDecimal netAmount, BigDecimal consignment) {
        this.status = status;
        this.dateOfSale = dateOfSale;
        this.amount = amount;
        this.cardHolderName = cardHolderName;
        this.fees = fees;
        this.salesTax = salesTax;
        this.shipping = shipping;
        this.datePaid = datePaid;
        this.netAmount = netAmount;
        this.consignment = consignment;
    }

    public String getTransactionId() {
        return transactionId;
    }

    public String getStatus() {
        return status;
    }

    public String getDateOfSale() {
        return dateOfSale;
    }

    public BigDecimal getAmount() {
        return amount;
    }

    public String getCardHolderName() {
        return cardHolderName;
    }

    public BigDecimal getFees() {
        return fees;
    }

    public BigDecimal getSalesTax() {
        return salesTax;
    }

    public BigDecimal getShipping() {
        return shipping;
    }

    public String getDatePaid() {
        return datePaid;
    }

    public BigDecimal getNetAmount() {
        return netAmount;
    }

    public BigDecimal getConsignment() {
        return consignment;
    }

    public void setPaymentInfo(List<Entry> entries) {
        this.fees = BigDecimal.ZERO;
        this.netAmount = BigDecimal.ZERO;
        this.consignment = BigDecimal.ZERO;

        for (Entry entry: entries) {
            switch (PaymentType.findForSlug(entry.getType())) {
                case FEE:
                    this.fees = this.fees.add(entry.getAmount());
                    break;
                case MERCHANT:
                    this.netAmount = this.netAmount.add(entry.getAmount());
                    break;
                case CONSIGNMENT:
                    this.consignment = this.consignment.add(entry.getAmount());
                    break;
            }
        }
    }
}
