/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.transaction;


import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.address.Address;
import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.common.RequestBodyInit;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import com.controlpad.payman_common.util.GsonUtil;
import com.controlpad.payman_common.util.NameParser;
import com.controlpad.payman_common.validation.AlwaysCheck;
import com.controlpad.payman_common.validation.SaleChecks;
import org.apache.commons.lang3.StringUtils;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.Valid;
import javax.validation.constraints.DecimalMax;
import javax.validation.constraints.DecimalMin;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;
import java.math.BigDecimal;
import java.math.RoundingMode;
import java.util.List;

public class Transaction implements RequestBodyInit {

	private String id;
    private String payeeUserId;
    private String payerUserId;
    private String teamId;
    @NotBlank(message = "transactionType required", groups = SaleChecks.class)
    private String transactionType;
    @DecimalMin(value = "0.00", message = "amount must be positive", groups = AlwaysCheck.class)
    @DecimalMax(value = "999999999999.99", message = "amount must be less than 1000000000000.00", groups = AlwaysCheck.class)
    private BigDecimal amount;
    @DecimalMin(value = "0.00", message = "subtotal must be positive", groups = AlwaysCheck.class)
    @DecimalMax(value = "999999999999.99", message = "subtotal must be less than 1000000000000.00", groups = AlwaysCheck.class)
    private BigDecimal subtotal;
    @DecimalMin(value = "0.00", message = "salesTax must be positive", groups = AlwaysCheck.class)
    @DecimalMax(value = "999999999999.99", message = "salesTax must be less than 1000000000000.00", groups = AlwaysCheck.class)
    private BigDecimal salesTax;
    @DecimalMin(value = "0.00", message = "shipping must be positive", groups = AlwaysCheck.class)
    @DecimalMax(value = "999999999999.99", message = "shipping must be less than 1000000000000.00", groups = AlwaysCheck.class)
    private BigDecimal shipping;
    @DecimalMin(value = "0.00", message = "discount must be positive")
    @DecimalMax(value = "999999999999.99", message = "discount must be less than 1000000000000.00", groups = AlwaysCheck.class)
    private BigDecimal discount;
    private String statusCode;
    private Integer resultCode;
    private String result;
    private Boolean swiped;
    private Boolean processed;
    private String accountHolder;
    private String description;
    private String gatewayReferenceId; // gateway reference id
    private Long batchId;
    private Long gatewayConnectionId;
    private String createdAt;
    private String datePaid;
    private String forTxnId;
    @Size(max = 255)
    private String orderId;
    @Size(max = 255)
    private String paymentId;

    @Valid
    private List<AffiliateCharge> affiliatePayouts;
    @Valid
    private Card card;
    @Valid
    private Account bankAccount;
    private Address billingAddress;
    private Address shippingAddress;
    private String poNumber;


    private List<TransactionCharge> transactionCharges;
    private List<Entry> entries;

	public Transaction() {}

	public Transaction(BigDecimal amount, BigDecimal salesTax) {
	    this.amount = amount;
	    this.salesTax = salesTax;
    }

    public Transaction(String payeeUserId, String payerUserId, String teamId, String transactionType, BigDecimal amount, BigDecimal subtotal, BigDecimal salesTax, String description) {
        this.payeeUserId = payeeUserId;
        this.payerUserId = payerUserId;
        this.teamId = teamId;
        this.transactionType = transactionType;
        this.amount = amount;
        this.subtotal = subtotal;
        this.salesTax = salesTax;
        this.description = description;
    }

    public Transaction(String id, String payeeUserId, String payerUserId, String teamId, String gatewayReferenceId, String transactionType,
                       BigDecimal amount, BigDecimal salesTax, BigDecimal shipping, String statusCode, Integer resultCode, Long gatewayConnectionId,
                       String description, String accountHolder) {
	    this.id = id;
        this.payeeUserId = payeeUserId;
        this.payerUserId = payerUserId;
        this.teamId = teamId;
        this.gatewayReferenceId = gatewayReferenceId;
        this.transactionType = transactionType;
        this.amount = amount;
        this.salesTax = salesTax;
        this.shipping = shipping;
        this.gatewayConnectionId = gatewayConnectionId;
        this.statusCode = statusCode;
        this.resultCode = resultCode;
        this.description = description;
        this.accountHolder = accountHolder;
    }

    public Transaction(String id, String payeeUserId, String teamId, String transactionType, BigDecimal amount, BigDecimal salesTax,
                       String statusCode, Integer resultCode, Long batchId, Long gatewayConnectionId) {
	    this.id = id;
        this.payeeUserId = payeeUserId;
        this.teamId = teamId;
        this.transactionType = transactionType;
        this.amount = amount;
        this.statusCode = statusCode;
        this.batchId = batchId;
        this.gatewayConnectionId = gatewayConnectionId;
        this.salesTax = salesTax;
        this.resultCode = resultCode;
    }

    public Transaction(String id, String payeeUserId, String payerUserId, String teamId, String gatewayReferenceId,
                       String transactionType, BigDecimal amount, BigDecimal salesTax, String statusCode,
                       Integer resultCode, Long gatewayConnectionId, String description) {
	    this.id = id;
        this.payeeUserId = payeeUserId;
        this.payerUserId = payerUserId;
        this.teamId = teamId;
        this.gatewayReferenceId = gatewayReferenceId;
        this.transactionType = transactionType;
        this.amount = amount;
        this.salesTax = salesTax;
        this.gatewayConnectionId = gatewayConnectionId;
        this.statusCode = statusCode;
        this.resultCode = resultCode;
        this.description = description;
    }

    public Transaction(Payment payment, String gatewayReferenceId, TransactionType transactionType, String accountHolder,
                       String statusCode, Integer resultCode, Long gatewayConnectionId) {
        this(null, payment.getPayeeUserId(), payment.getPayerUserId(), payment.getTeamId(), gatewayReferenceId, transactionType.slug,
                payment.getTotal(), payment.getTax(), payment.getShipping(), statusCode, resultCode,  gatewayConnectionId, payment.getDescription(), accountHolder);
        this.orderId = payment.getOrderId();
        this.billingAddress = payment.getBillingAddress();
        this.shippingAddress = payment.getShippingAddress();
        this.subtotal = payment.getSubtotal();
        this.affiliatePayouts = payment.getAffiliatePayouts();
    }

    public Transaction(TransferPayment payment, TransactionType transactionType) {
        this(null, payment.getPayeeUserId(), payment.getPayerUserId(), payment.getTeamId(), null, transactionType.slug,
                payment.getAmount(), null, null, "S", 1, null, payment.getDescription(), null);
    }

    public BigDecimal getSubTotal() {
        if (amount != null) {
            if (salesTax == null)
                return amount;
            else
                return amount.subtract(salesTax);
        }
        return BigDecimal.ZERO;
    }

    public String getId() {
        return id;
    }

    public String getPayeeUserId() {
        return payeeUserId;
    }

    public String getPayerUserId() {
        return payerUserId;
    }

    public String getTeamId() {
        return teamId;
    }

    public String getGatewayReferenceId() {
        return gatewayReferenceId;
    }

    public String getTransactionType() {
        return transactionType;
    }

    public BigDecimal getAmount() {
        return amount;
    }

    public BigDecimal getSalesTax() {
        return salesTax;
    }

    public BigDecimal getShipping() {
        return shipping;
    }

    public String getStatusCode() {
        return statusCode;
    }

    public Integer getResultCode() {
        return resultCode;
    }

    public String getResult() {
        return result;
    }

    public String getOrderId() {
        return orderId;
    }

    public String getPoNumber() {
        return poNumber;
    }

    public Card getCard() {
        return card;
    }

    public Account getBankAccount() {
        return bankAccount;
    }

    public Boolean getSwiped() {
        return swiped;
    }

    public Boolean getProcessed() {
        return processed;
    }

    public String getAccountHolder() {
	    if (accountHolder != null) {
            return accountHolder;
        } else if (billingAddress != null) {
	        return billingAddress.getFullName();
        } else {
	        return null;
        }
    }

    public Long getBatchId() {
        return batchId;
    }

    public Long getGatewayConnectionId() {
        return gatewayConnectionId;
    }

    public String getDescription() {
        return description;
    }

    public void setOrderId(String orderId) {
        this.orderId = orderId;
    }

    public String getCreatedAt() {
        return createdAt;
    }

    public BigDecimal getSubtotal() {
        return subtotal;
    }

    public BigDecimal getDiscount() {
        return discount;
    }

    public List<AffiliateCharge> getAffiliatePayouts() {
        return affiliatePayouts;
    }

    public Address getBillingAddress() {
        return billingAddress;
    }

    public Address getShippingAddress() {
        return shippingAddress;
    }

    public void setCreatedAt(String createdAt) {
        this.createdAt = createdAt;
    }

    public String getForTxnId() {
        return forTxnId;
    }

    public List<TransactionCharge> getTransactionCharges() {
        return transactionCharges;
    }

    public List<Entry> getEntries() {
        return entries;
    }

    public String getDatePaid() {
        return datePaid;
    }

    public void setPayeeUserId(String payeeUserId) {
        this.payeeUserId = payeeUserId;
    }

    public void setPayerUserId(String payerUserId) {
        this.payerUserId = payerUserId;
    }

    public void setTeamId(String teamId) {
        this.teamId = teamId;
    }

    public void setId(String id) {
        this.id = id;
    }

    public void setStatusCode(String statusCode) {
        this.statusCode = statusCode;
    }

    public void setResultCode(Integer resultCode) {
        this.resultCode = resultCode;
    }

    public void updateResultAndCode(Integer resultCode) {
        this.resultCode = resultCode;
        this.result = TransactionResult.findById(resultCode).getMessage();
    }

    public void updateResultAndCode(TransactionResult transactionResult) {
	    this.resultCode = transactionResult.getResultCode();
	    this.result = transactionResult.getMessage();
	    this.statusCode = transactionResult.getStatusCode();
    }

    public void setGatewayConnectionId(Long gatewayConnectionId) {
        this.gatewayConnectionId = gatewayConnectionId;
    }

    public void setGatewayReferenceId(String gatewayReferenceId) {
        this.gatewayReferenceId = gatewayReferenceId;
    }

    public void setBatchId(Long batchId) {
        this.batchId = batchId;
    }

    public void setTransactionType(String transactionType) {
        this.transactionType = transactionType;
    }

    public void setTransactionCharges(List<TransactionCharge> transactionCharges) {
        this.transactionCharges = transactionCharges;
    }

    public void setBankAccount(Account bankAccount) {
        this.bankAccount = bankAccount;
    }

    public void setSwiped(Boolean swiped) {
        this.swiped = swiped;
    }

    public void setDatePaid(String datePaid) {
        this.datePaid = datePaid;
    }

    public void setForTxnId(String forTxnId) {
        this.forTxnId = forTxnId;
    }

    public void setEntries(List<Entry> entries) {
        this.entries = entries;
    }

    public void setAffiliatePayouts(List<AffiliateCharge> affiliatePayouts) {
        this.affiliatePayouts = affiliatePayouts;
    }

    public void setBillingAddress(Address billingAddress) {
        this.billingAddress = billingAddress;
    }

    public void setCard(Card card) {
        this.card = card;
    }

    public void setPaymentId(String paymentId) {
        this.paymentId = paymentId;
    }

    public String getPaymentId() {
        return paymentId;
    }

    @Override
    public String toString() {
        return GsonUtil.getGson().toJson(this);
    }

    @Override
    public void initRequestBody() {
        if (salesTax != null) {
            salesTax = salesTax.setScale(2, RoundingMode.HALF_UP);
        } else {
            salesTax = BigDecimal.ZERO.setScale(2, RoundingMode.HALF_UP);
        }
        if (shipping != null) {
            shipping = shipping.setScale(2, RoundingMode.HALF_UP);
        } else {
            shipping = BigDecimal.ZERO.setScale(2, RoundingMode.HALF_UP);
        }
        if (subtotal != null) {
            subtotal = subtotal.setScale(2, RoundingMode.HALF_UP);
        }
        if (amount != null) {
            amount = amount.setScale(2, RoundingMode.HALF_UP);
        }
        if (discount != null) {
            discount = discount.setScale(2, RoundingMode.HALF_UP);
        }
        if (affiliatePayouts != null) {
            for(AffiliateCharge affiliateCharge : affiliatePayouts) {
                affiliateCharge.initRequestBody();
            }
        }

        // Allow putting full billing name in either first name or getName()
        if (billingAddress != null) {
            if (StringUtils.isBlank(billingAddress.getFirstName())) {
                if (StringUtils.isNotBlank(accountHolder)) {
                    convertAddressName(billingAddress, accountHolder);
                }
            } else if (StringUtils.isBlank(billingAddress.getLastName())) {
                convertAddressName(billingAddress, billingAddress.getFirstName());
            }
        } else if (StringUtils.isNotBlank(accountHolder)){
            billingAddress = new Address();
            convertAddressName(billingAddress, accountHolder);
        }

        // Allow putting full name in shipping address first name field
        if (shippingAddress != null) {
            if (StringUtils.isNotBlank(shippingAddress.getFirstName()) && StringUtils.isBlank(shippingAddress.getLastName())) {
                convertAddressName(shippingAddress, shippingAddress.getFirstName());
            }
        }
    }

    private void convertAddressName(Address address, String name) {
        NameParser nameParser = new NameParser(name);
        address.setFirstName(nameParser.getFirstName());
        address.setLastName(nameParser.getLastName());
    }
}