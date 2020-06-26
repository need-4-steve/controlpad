package com.controlpad.pay_fac.transaction;

import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.consignment.Consignment;
import com.controlpad.payman_common.consignment.ConsignmentMapper;
import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import com.controlpad.payman_common.transaction_charge.TransactionChargeMapper;
import org.junit.Test;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

public class CashTransactionTest extends TransactionControllerTest {

    private String cashPayeeId = "CashCustomer1";
    private String type = TransactionType.CASH_SALE.slug;
    private String url = "/transactions";

    @Test
    public void validationTest() {
        Transaction payment;

        //Check bad requests are properly validated
        //payee user id is required
        payment = new Transaction(null, null, getTeamTwoId(), type, getTotal(), getSubtotal(), getTax(), null);
        performBadPostRequest(url, payment);

        //team id is required
        payment = new Transaction(getBadRequestPayeeId(), null, null, type, getTotal(), getSubtotal(), getTax(), null);
        performBadPostRequest(url, payment);

        //total required
        payment = new Transaction(getBadRequestPayeeId(), null, getTeamTwoId(), type, null, null, getTax(), null);
        performBadPostRequest(url, payment);

        //total positive
        payment = new Transaction(getBadRequestPayeeId(), null, getTeamTwoId(), type, getTotal().negate(), null, getTax(), null);
        performBadPostRequest(url, payment);

        //tax positive
        payment = new Transaction(getBadRequestPayeeId(), null, getTeamTwoId(), type, getTotal(), null, getTax().negate(), null);
        performBadPostRequest(url, payment);
    }

    @Test
    public void cashSaleTest() {
        loadDummyData();

        Transaction payment = new Transaction(cashPayeeId, "15", getTeamTwoId(), type, getTotal(), getSubtotal(), getTax(), null);

        Transaction transactionResponse = performPostRequest(url, payment, 1,false);

        //cash sale works and writes to database
        assertTransactionValidForSale(transactionResponse, payment);

        TransactionChargeMapper transactionChargeMapper = getSqlSession().getMapper(TransactionChargeMapper.class);
        List<TransactionCharge> transactionCharges = transactionChargeMapper.listForTransactionId(transactionResponse.getId());
        System.out.println("TransactionCharge list size: " + transactionCharges.size());

        List<Fee> fees = getTestUtil().getMockData().getFeesForTeamAndType(getTeamTwoId(), TransactionType.CASH_SALE.slug);

        // Make sure we only have as many transaction charges as there are fees + 1 consignment + 1 tax
        assert transactionCharges.size() == fees.size() + 1;

        // Check that affiliate payouts is rejected for cash sales
        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        affiliatePayouts.add(new AffiliateCharge(null, "55", BigDecimal.valueOf(1.00)));
        payment.setAffiliatePayouts(affiliatePayouts);
        performBadPostRequest(url, payment);
    }

    private void loadDummyData() {
        // Add a consignment charge to be taken out
        getSqlSession().getMapper(ConsignmentMapper.class).insert(new Consignment(cashPayeeId, new Money(1000D), new Money(10D), true));
        getSqlSession().commit();
    }
}
