/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.transaction;

import com.controlpad.pay_fac.payment_info.CardPayment;
import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction.TransactionType;
import org.junit.Test;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

public class CardPaymentTest extends PaymentControllerTest {

    @Test
    public void creditCardSaleTest() {

        String path = "/transactions/sale/credit-card";

        CardPayment cardPayment = new CardPayment("5", "20", getTeamTwoId(),
                "Credit TokenRequest Customer", getTax(), BigDecimal.ZERO, getSubtotal().add(getTax()),
                null, "4000100211112222", "555", 2019, 9);

        cardSaleTest(path, cardPayment, TransactionType.CREDIT_CARD_SALE);

        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        affiliatePayouts.add(new AffiliateCharge(null, "6", BigDecimal.valueOf(6.55)));
        affiliatePayouts.add(new AffiliateCharge(null, "7", BigDecimal.valueOf(5.23)));
        cardPayment = new CardPayment("5", "20", getTeamTwoId(), "Credit TokenRequest Customer",
                getTax(), BigDecimal.ZERO, getSubtotal().add(getTax()), null, "4000100211112222", "555", 2019, 9);
        cardPayment.setAffiliatePayouts(affiliatePayouts);

        cardSaleTest(path, cardPayment, TransactionType.CREDIT_CARD_SALE);

        checkBadSaleRequest(path);
    }

    @Test
    public void repConnectionTest() {
        String path = "/transactions/sale/credit-card";
        String userId = getMockData().getRepGatewayConnection().getUserId();
        BigDecimal total = getSubtotal().add(getTax());

        CardPayment cardPayment = new CardPayment("5", userId, getTeamFourId(),
                "Credit TokenRequest Customer", getTax(), BigDecimal.valueOf(5.00), total,
                "rep connection test","4000100211112222","999", 2099, 12);

        TransactionResponse response = cardSaleTest(path, cardPayment, TransactionType.CREDIT_CARD_SALE);
        assert response.getTransaction().getAmount().equals(total);
        assert response.getTransaction().getGatewayConnectionId().equals(getMockData().getRepGatewayConnection().getId());

        // check that affiliates is blocked
        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        affiliatePayouts.add(new AffiliateCharge(null, "6", BigDecimal.valueOf(6.55)));
        cardPayment = new CardPayment("5", userId, getTeamFourId(), "Credit TokenRequest", "Customer", getTax(),
                BigDecimal.ZERO, getSubtotal(), null,null, affiliatePayouts, "84096", "4000100211112222", "092019");

        checkBadSaleRequest(path);
    }

    @Test
    public void creditCardSubscriptionTest() {

        String path = "/transactions/sub/credit-card";

        CardPayment cardPayment = new CardPayment("Company", "30", getTeamTwoId(),
                "Credit TokenRequest Customer", getTax(), BigDecimal.ZERO, getSubtotal().add(getTax()),
                null, "4000100211112222", "555", 2019, 9);

        cardSaleTest(path, cardPayment, TransactionType.CREDIT_CARD_SUB);

        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        affiliatePayouts.add(new AffiliateCharge(null, "6", BigDecimal.valueOf(4.38)));
        affiliatePayouts.add(new AffiliateCharge(null, "7", BigDecimal.valueOf(5.03)));
        cardPayment = new CardPayment("Company", "30", getTeamTwoId(), "Credit TokenRequest Customer", getTax(),
                BigDecimal.ZERO, getSubtotal().add(getTax()), null,"4000100211112222", "555", 2099, 12);
        cardPayment.setAffiliatePayouts(affiliatePayouts);

        cardSaleTest(path, cardPayment, TransactionType.CREDIT_CARD_SUB);

        checkBadSaleRequest(path);
    }

    @Test
    public void debitCardSaleTest() {
        String path = "/transactions/sale/debit-card";

        CardPayment cardPayment = new CardPayment("6", "22", getTeamTwoId(),
                "Debit TokenRequest Customer", getTax(), BigDecimal.ZERO, getSubtotal().add(getTax()),
                "Sold some stuff", "4000100211112222", "555", 2019, 9);

        cardSaleTest(path, cardPayment, TransactionType.DEBIT_CARD_SALE);

        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        affiliatePayouts.add(new AffiliateCharge(null, "6", BigDecimal.valueOf(4.88)));
        affiliatePayouts.add(new AffiliateCharge(null, "7", BigDecimal.valueOf(7.03)));
        cardPayment = new CardPayment("6", "22", getTeamTwoId(), "Credit TokenRequest Customer",
                getTax(), BigDecimal.ZERO, getSubtotal().add(getTax()), null, "4000100211112222", "555", 2019, 9);
        cardPayment.setAffiliatePayouts(affiliatePayouts);

        cardSaleTest(path, cardPayment, TransactionType.DEBIT_CARD_SALE);

        checkBadSaleRequest(path);
    }

    @Test
    public void debitCardSubscriptionTest() {
        String path = "/transactions/sub/debit-card";

        CardPayment cardPayment = new CardPayment("16", "Company", getTeamOneId(),
                "Debit TokenRequest Customer", getTax(), BigDecimal.ZERO, getSubtotal().add(getTax()),
                "Bought a subscription", "4000100211112222", "999", 2019, 9);

        cardSaleTest(path, cardPayment, TransactionType.DEBIT_CARD_SUB);

        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        affiliatePayouts.add(new AffiliateCharge(null, "6", BigDecimal.valueOf(3.33)));
        affiliatePayouts.add(new AffiliateCharge(null, "7", BigDecimal.valueOf(4.87)));
        cardPayment = new CardPayment("16", "Company", getTeamTwoId(), "Credit TokenRequest Customer", getTax(),
                BigDecimal.ZERO, getSubtotal().add(getTax()), null, "4000100211112222", "555", 2019, 9);
        cardPayment.setAffiliatePayouts(affiliatePayouts);

        cardSaleTest(path, cardPayment, TransactionType.DEBIT_CARD_SUB);

        checkBadSaleRequest(path);
    }

    private TransactionResponse cardSaleTest(String path, CardPayment cardPayment, TransactionType transactionType) {
        TransactionResponse transactionResponse = performPostRequest(path, cardPayment, false);

        // Make sure the response is good
        assertTransactionValidForSale(transactionResponse.getTransaction(), cardPayment, transactionType);

        // Make sure the database is good
        Transaction savedTransaction = getSqlSession().getMapper(TransactionMapper.class).findById(transactionResponse.getTransaction().getId());
        assertTransactionValidForSale(savedTransaction, cardPayment, transactionType);
        assertAffiliateChargesValid(savedTransaction, cardPayment);
        return transactionResponse;
    }

    private void checkBadSaleRequest(String path) {
        //magstripe or cardnumber and zip required
        performBadPostRequest(path,
                new CardPayment(null, getBadRequestPayeeId(), getTeamTwoId(), null, getTax(), getSubtotal(), null, null, null, null, null, null, null));
        // TODO check magstripe
    }

    private void checkBadAffiliatePayoutRequest(String path) {
        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        CardPayment cardPayment = new CardPayment("5", "6", getTeamTwoId(), "First Name",
                "Last Name", BigDecimal.valueOf(1.20), BigDecimal.ZERO, BigDecimal.valueOf(20.00), null, null, affiliatePayouts,
                "84057", "4111111111111111", "0919");

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
