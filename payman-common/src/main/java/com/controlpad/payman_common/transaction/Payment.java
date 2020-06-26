/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.transaction;

import com.controlpad.payman_common.address.Address;
import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.common.RequestBodyInit;
import com.controlpad.payman_common.util.GsonUtil;
import com.controlpad.payman_common.util.NameParser;
import com.controlpad.payman_common.validation.AlwaysCheck;
import com.controlpad.payman_common.validation.PaymentChecks;
import com.controlpad.payman_common.validation.SaleChecks;
import com.controlpad.payman_common.validation.TransferChecks;
import org.apache.commons.lang3.StringUtils;

import javax.validation.Valid;
import javax.validation.constraints.*;
import java.math.BigDecimal;
import java.math.RoundingMode;
import java.util.List;

public class Payment implements RequestBodyInit {

    @NotNull(message = "payerUserId required", groups = {TransferChecks.class, PaymentChecks.class})
    private String payerUserId;
    @NotNull(message = "payeeUserId required", groups = {SaleChecks.class, TransferChecks.class})
    private String payeeUserId;
    @NotNull(message = "teamId required", groups = AlwaysCheck.class)
    private String teamId;
    private String name;
    private String firstName;
    private String lastName;
    @DecimalMin(value = "0.00", message = "tax must be positive", groups = AlwaysCheck.class)
    @DecimalMax(value = "999999999999.99", message = "tax must be less than 1000000000000.00", groups = AlwaysCheck.class)
    private BigDecimal tax;
    @DecimalMin(value = "0.00", message = "shipping must be positive", groups = AlwaysCheck.class)
    @DecimalMax(value = "999999999999.99", message = "shipping must be less than 1000000000000.00", groups = AlwaysCheck.class)
    private BigDecimal shipping;
    @DecimalMin(value = "0.00", message = "subtotal must be positive", groups = AlwaysCheck.class)
    @DecimalMax(value = "999999999999.99", message = "subtotal must be less than 1000000000000.00", groups = AlwaysCheck.class)
    private BigDecimal subtotal;
    @NotNull(message = "total required", groups = AlwaysCheck.class)
    @DecimalMin(value = "0.00", message = "total must be positive", groups = AlwaysCheck.class)
    @DecimalMax(value = "999999999999.99", message = "total must be less than 1000000000000.00", groups = AlwaysCheck.class)
    private BigDecimal total;
    @DecimalMin(value = "0.00", message = "discount must be positive")
    @DecimalMax(value = "999999999999.99", message = "discount must be less than 1000000000000.00", groups = AlwaysCheck.class)
    private BigDecimal discount;
    private String poNumber;
    private String description;
    private String orderId;
    @Valid
    private List<AffiliateCharge> affiliatePayouts;
    private Address billingAddress;
    private Address shippingAddress;

    public Payment() {}

    public Payment(String payerUserId, String payeeUserId, String teamId, String name, BigDecimal tax,
                   BigDecimal shipping, BigDecimal total, String description) {
        this.payerUserId = payerUserId;
        this.payeeUserId = payeeUserId;
        this.teamId = teamId;
        this.name = name;
        this.tax = tax;
        this.shipping = shipping;
        this.total = total;
        this.description = description;
    }

    public Payment(String payerUserId, String payeeUserId, String teamId, String name, BigDecimal tax,
                   BigDecimal shipping, BigDecimal subtotal, String poNumber, String description) {
        this.payerUserId = payerUserId;
        this.payeeUserId = payeeUserId;
        this.teamId = teamId;
        this.name = name;
        this.tax = tax;
        this.subtotal = subtotal;
        this.poNumber = poNumber;
        this.description = description;
    }

    public Payment(String payerUserId, String payeeUserId, String teamId, String name, BigDecimal tax, BigDecimal subtotal, String poNumber, String description) {
        this(payerUserId, payeeUserId, teamId, name, tax, BigDecimal.ZERO, subtotal, poNumber, description);
    }

    public Payment(String payerUserId, String payeeUserId, String teamId, String firstName, String lastName,
                   BigDecimal tax, BigDecimal shipping, BigDecimal subtotal, String poNumber, String description, List<AffiliateCharge> affiliatePayouts) {
        this.payerUserId = payerUserId;
        this.payeeUserId = payeeUserId;
        this.teamId = teamId;
        this.name = name;
        this.firstName = firstName;
        this.lastName = lastName;
        this.tax = tax;
        this.shipping = shipping;
        this.subtotal = subtotal;
        this.poNumber = poNumber;
        this.description = description;
        this.affiliatePayouts = affiliatePayouts;
    }

    public Payment(String payerUserId, String payeeUserId, String teamId, String name, String firstName, String lastName,
                   BigDecimal tax, BigDecimal shipping, BigDecimal subtotal, String poNumber, String description,
                   List<AffiliateCharge> affiliatePayouts, Address billingAddress, Address shippingAddress) {

        this.payerUserId = payerUserId;
        this.payeeUserId = payeeUserId;
        this.teamId = teamId;
        this.name = name;
        this.firstName = firstName;
        this.lastName = lastName;
        this.tax = tax;
        this.shipping = shipping;
        this.subtotal = subtotal;
        this.poNumber = poNumber;
        this.description = description;
        this.affiliatePayouts = affiliatePayouts;
        this.billingAddress = billingAddress;
        this.shippingAddress = shippingAddress;
    }

    public BigDecimal getTotal() {
        if (total == null) {
            if (subtotal == null) {
                return null;
            }
            BigDecimal calculation = subtotal;
            if (tax != null) {
                calculation = calculation.add(tax);
            }
            if (shipping != null) {
                calculation = calculation.add(shipping);
            }
            if (discount != null) {
                calculation = calculation.subtract(discount);
            }
            return calculation;
        } else {
            return total;
        }
    }

    public String getDescription() {
        if (description != null && description.length() > 128) {
            return description.substring(0, 128);
        }
        return description;
    }

    public String getPayerUserId() {
        return payerUserId;
    }

    public String getPayeeUserId() {
        return payeeUserId;
    }

    public String getName() {
        if (StringUtils.isNotBlank(name)) {
            return name;
        } else if (billingAddress != null && StringUtils.isNotBlank(billingAddress.getFirstName())) {
            if (StringUtils.isNotBlank(billingAddress.getLastName())) {
                return billingAddress.getFirstName() + " " + billingAddress.getLastName();
            } else {
                return billingAddress.getFirstName();
            }
        }
        return null;
    }

    public String getFirstName() {
        return firstName;
    }

    public String getLastName() {
        return lastName;
    }

    public BigDecimal getTax() {
        return tax;
    }

    public BigDecimal getShipping() {
        return shipping;
    }

    public BigDecimal getSubtotal() {
        return subtotal;
    }

    public String getPoNumber() {
        return poNumber;
    }

    public String getTeamId() {
        return teamId;
    }

    public void setTeamId(String teamId) {
        this.teamId = teamId;
    }

    public String getOrderId() {
        return orderId;
    }

    public void setOrderId(String orderId) {
        this.orderId = orderId;
    }

    public Address getBillingAddress() {
        return billingAddress;
    }

    public void setBillingAddress(Address billingAddress) {
        this.billingAddress = billingAddress;
    }

    public void setShippingAddress(Address shippingAddress) {
        this.shippingAddress = shippingAddress;
    }

    public Address getShippingAddress() {
        return shippingAddress;
    }

    public void setPayerUserId(String payerUserId) {
        this.payerUserId = payerUserId;
    }

    public void setPayeeUserId(String payeeUserId) {
        this.payeeUserId = payeeUserId;
    }

    public List<AffiliateCharge> getAffiliatePayouts() {
        return affiliatePayouts;
    }

    public void setAffiliatePayouts(List<AffiliateCharge> affiliatePayouts) {
        this.affiliatePayouts = affiliatePayouts;
    }

    public BigDecimal getTotalAffiliatePayoutAmount() {
        BigDecimal total = BigDecimal.ZERO;
        for(AffiliateCharge charge: affiliatePayouts) {
            if (charge.getAmount() != null) {
                total = total.add(charge.getAmount());
            }
        }
        return total;
    }

    @Override
    public String toString() {
        return GsonUtil.getGson().toJson(this);
    }

    @Override
    public void initRequestBody() {
        if (tax != null) {
            tax = tax.setScale(2, RoundingMode.HALF_UP);
        } else {
            tax = BigDecimal.ZERO.setScale(2, RoundingMode.HALF_UP);
        }
        if (shipping != null) {
            shipping = shipping.setScale(2, RoundingMode.HALF_UP);
        } else {
            shipping = BigDecimal.ZERO.setScale(2, RoundingMode.HALF_UP);
        }
        if (subtotal != null) {
            subtotal = subtotal.setScale(2, RoundingMode.HALF_UP);
        }
        if (total != null) {
            total = total.setScale(2, RoundingMode.HALF_UP);
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
                if (StringUtils.isNotBlank(getName())) {
                    convertAddressName(billingAddress, getName());
                }
            } else if (StringUtils.isBlank(billingAddress.getLastName())) {
                    convertAddressName(billingAddress, billingAddress.getFirstName());
            }
        } else if (StringUtils.isNotBlank(getName())){
            billingAddress = new Address();
            convertAddressName(billingAddress, getName());
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