/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.transaction;

import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.consignment.Consignment;
import com.controlpad.payman_common.consignment.ConsignmentMapper;
import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.transaction.Payment;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import com.controlpad.payman_common.transaction_charge.TransactionChargeMapper;
import org.junit.Test;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

public class CashSaleTest extends PaymentControllerTest {

    String cashPayeeId = "4";

    @Test
    public void cashSaleTest() {
        loadDummyData();

        String url = "/transactions/sale/cash";
        Payment payment;

        //Check bad requests are properly validated
        //payee user id is required
        payment = new Payment(null, null, getTeamTwoId(), null, getTax(), getSubtotal(), "55", null);
        performBadPostRequest(url, payment);

        //team id is required
        payment = new Payment(null, getBadRequestPayeeId(), null, null, getTax(), getSubtotal(), "55", null);
        performBadPostRequest(url, payment);

        //subtotal positive
        payment = new Payment(null, getBadRequestPayeeId(), getTeamTwoId(), null, getTax(), new Money(-50D), "55", null);
        performBadPostRequest(url, payment);

        payment = new Payment("15", cashPayeeId, getTeamTwoId(), "Cash Customer", getTax(), BigDecimal.ZERO,
                getSubtotal().add(getTax()), null);

        TransactionResponse transactionResponse = performPostRequest(url, payment, false);

        //cash sale works and writes to database
        assertTransactionValidForSale(transactionResponse.getTransaction(), payment, TransactionType.CASH_SALE);

        TransactionChargeMapper transactionChargeMapper = getSqlSession().getMapper(TransactionChargeMapper.class);
        List<TransactionCharge> transactionCharges = transactionChargeMapper.listForTransactionId(transactionResponse.getTransaction().getId());
        System.out.println("TransactionCharge list size: " + transactionCharges.size());

        List<Fee> fees = getTestUtil().getMockData().getFeesForTeamAndType(getTeamTwoId(), TransactionType.CASH_SALE.slug);

        // Make sure we only have as many transaction charges as there are fees + 1 consignment + 1 tax
        assert transactionCharges.size() == fees.size() + 1;

        // Check that affiliate payouts is rejected for cash sales
        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        affiliatePayouts.add(new AffiliateCharge(null, "55", BigDecimal.valueOf(1.00)));
        Payment affiliatePayment = new Payment("5", "6", getTeamTwoId(), "first name", "last name",
                new Money(0.6), BigDecimal.ZERO, new Money(10.00), null, null, affiliatePayouts);
        performBadPostRequest(url, affiliatePayment);
    }

    private void loadDummyData() {
        // Add a consignment charge to be taken out
        getSqlSession().getMapper(ConsignmentMapper.class).insert(new Consignment(cashPayeeId, new Money(1000D), new Money(10D), true));
        getSqlSession().commit();
    }
}