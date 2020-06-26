/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.transaction;

import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction.TransactionType;
import org.junit.Test;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

public class CheckSaleTest extends PaymentControllerTest {

    @Test
    public void checkSaleTest() {

        String url = "/transactions/sale/e-check";

        checkTest(url, TransactionType.CHECK_SALE);
    }

    @Test
    public void checkSubTest() {
        String url = "/transactions/sub/e-check";

        checkTest(url, TransactionType.CHECK_SUB);
    }

    private void checkTest(String path, TransactionType transactionType) {
        String routing = "324377516";
        String number = "123456789";
        String accountHolderName = "Account Holder Name";
        CheckPayment checkPayment =
                new CheckPayment("12", "55", getTeamTwoId(), "Check Customer", getTax(),
                        getSubtotal().add(getTax()), "Sold my grandpas glasses.", accountHolderName, routing, number, "checking");

        TransactionResponse transactionResponse = performPostRequest(path, checkPayment, false);

        //check sale works
        assertTransactionValidForSale(transactionResponse.getTransaction(), checkPayment, transactionType);
        //check database
        Transaction savedTransaction = getSqlSession().getMapper(TransactionMapper.class).findById(transactionResponse.getTransaction().getId());
        assertTransactionValidForSale(savedTransaction, checkPayment, transactionType);

        // affiliatePayouts work
        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        affiliatePayouts.add(new AffiliateCharge(null, "19", BigDecimal.valueOf(1.77)));
        affiliatePayouts.add(new AffiliateCharge(null, "20", BigDecimal.valueOf(4.39)));
        checkPayment = new CheckPayment(null, "55", getTeamTwoId(), "first last",
                new Money(1.20), new Money(20.00), null, accountHolderName, routing, number, "checking");
        checkPayment.setAffiliatePayouts(affiliatePayouts);
        transactionResponse = performPostRequest(path, checkPayment, false);
        assertTransactionValidForSale(transactionResponse.getTransaction(), checkPayment, transactionType);
        savedTransaction = getSqlSession().getMapper(TransactionMapper.class).findById(transactionResponse.getTransaction().getId());
        assertTransactionValidForSale(savedTransaction, checkPayment, transactionType);

        assertAffiliateChargesValid(savedTransaction, checkPayment);

        //account name required
        checkPayment = new CheckPayment(null, getBadRequestPayeeId(), getTeamTwoId(), null, getTax(),
                getSubtotal().add(getTax()), null, null, null, routing, number);
        performBadPostRequest(path, checkPayment);

        //account routing required
        checkPayment = new CheckPayment(null, getBadRequestPayeeId(), getTeamTwoId(), null, getTax(),
                getSubtotal().add(getTax()), null, null, accountHolderName, null, number);
        performBadPostRequest(path, checkPayment);

        //account number required
        checkPayment = new CheckPayment(null, getBadRequestPayeeId(), getTeamTwoId(), null, getTax(),
                getSubtotal().add(getTax()), null, null, accountHolderName, routing, null);
        performBadPostRequest(path, checkPayment);

        //Check bad affiliate payout requests
        checkBadAffiliatePayoutRequest(path, routing, number, accountHolderName);
    }

    private void checkBadAffiliatePayoutRequest(String path, String routing, String number, String accountHolderName) {
        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        CheckPayment cardPayment = new CheckPayment("5", "6", getTeamTwoId(), "First Name", "Last Name",
                new Money(1.2), BigDecimal.ZERO, new Money(20.00), null,
                null, affiliatePayouts, accountHolderName, routing, number);

        // Check payee
        affiliatePayouts.add(new AffiliateCharge(null, null, BigDecimal.valueOf(10.00)));
        performBadPostRequest(path, cardPayment);

        // Check amount
        affiliatePayouts.clear();
        affiliatePayouts.add(new AffiliateCharge(null, "5", null));
        performBadPostRequest(path, cardPayment);

        // Check amount limit
        affiliatePayouts.clear();
        affiliatePayouts.add(new AffiliateCharge(null, "5", BigDecimal.valueOf(20.01)));
        performBadPostRequest(path, cardPayment);
    }
}
