package com.controlpad.pay_fac.transaction;

import com.controlpad.pay_fac.payment_info.CardPayment;
import com.controlpad.payman_common.address.Address;
import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.transaction.Card;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction.TransactionType;
import org.junit.Test;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

public class CardTransactionTest extends TransactionControllerTest {

    private Card card = new Card("4000100211112222", 9, 2019, "555");

    @Test
    public void creditCardSaleTest() {

        String path = "/transactions";

        Transaction cardTransaction = new Transaction("20", "5", getTeamTwoId(), TransactionType.CREDIT_CARD_SALE.slug,
                getSubtotal().add(getTax()), getSubtotal(), getTax(), null);
        cardTransaction.setCard(card);
        cardTransaction.setBillingAddress(new Address(null, null, null, null, null, "Credit TokenRequest Customer"));

        cardSaleTest(path, cardTransaction);

        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        affiliatePayouts.add(new AffiliateCharge(null, "6", BigDecimal.valueOf(6.55)));
        affiliatePayouts.add(new AffiliateCharge(null, "7", BigDecimal.valueOf(5.23)));

        cardTransaction = new Transaction("20", "5", getTeamTwoId(), TransactionType.CREDIT_CARD_SALE.slug,
                getTotal(), getSubtotal(), getTax(), null);
        cardTransaction.setCard(card);
        cardTransaction.setBillingAddress(new Address(null, null, null, null, null, "Credit TokenRequest Customer"));
        cardTransaction.setAffiliatePayouts(affiliatePayouts);

        cardSaleTest(path, cardTransaction);

        checkBadSaleRequest(path);
    }

    @Test
    public void repConnectionTest() {
        String path = "/transactions";
        String userId = getMockData().getRepGatewayConnection().getUserId();
        BigDecimal total = getTotal();

        Transaction cardTransaction = new Transaction(userId, "5", getTeamFourId(), TransactionType.CREDIT_CARD_SALE.slug,
                total, getSubtotal(), getTax(), "rep connection test");
        cardTransaction.setCard(new Card("4000100211112222", 12, 2099, "999"));
        cardTransaction.setBillingAddress(new Address(null, null, null, null, null, "Credit TokenRequest Customer"));

        Transaction response = cardSaleTest(path, cardTransaction);
        assert response.getAmount().equals(total);
        assert response.getGatewayConnectionId().equals(getMockData().getRepGatewayConnection().getId());

        // check that affiliates is blocked
        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        affiliatePayouts.add(new AffiliateCharge(null, "6", BigDecimal.valueOf(6.55)));

        cardTransaction = new Transaction(userId, "5", getTeamFourId(), TransactionType.CREDIT_CARD_SALE.slug,
                total, getSubtotal(), getTax(), "rep connection test");
        cardTransaction.setCard(new Card("4000100211112222", 12, 2099, "999"));
        cardTransaction.setBillingAddress(new Address(null, null, null, null, "84096", "Credit TokenRequest", "Customer"));
        cardTransaction.setAffiliatePayouts(affiliatePayouts);

        checkBadSaleRequest(path);
    }

    @Test
    public void creditCardSubscriptionTest() {

        String path = "/transactions";

        Transaction cardTransaction = new Transaction("copmany", "30", getTeamTwoId(), TransactionType.CREDIT_CARD_SUB.slug,
                getTotal(), getSubtotal(), getTax(), null);
        cardTransaction.setCard(card);
        cardTransaction.setBillingAddress(new Address(null, null, null, null, "84096", "Credit Card Customer"));

        cardSaleTest(path, cardTransaction);

        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        affiliatePayouts.add(new AffiliateCharge(null, "6", BigDecimal.valueOf(4.38)));
        affiliatePayouts.add(new AffiliateCharge(null, "7", BigDecimal.valueOf(5.03)));

        cardTransaction = new Transaction("copmany", "30", getTeamTwoId(), TransactionType.CREDIT_CARD_SUB.slug,
                getTotal(), getSubtotal(), getTax(), null);
        cardTransaction.setCard(card);
        cardTransaction.setBillingAddress(new Address(null, null, null, null, "84096", "Credit Card Customer"));
        cardTransaction.setAffiliatePayouts(affiliatePayouts);
        
        cardSaleTest(path, cardTransaction);

        checkBadSaleRequest(path);
    }

    @Test
    public void debitCardSaleTest() {
        String path = "/transactions";

        Transaction cardTransaction = new Transaction("6", "22", getTeamTwoId(), TransactionType.DEBIT_CARD_SALE.slug,
                getTotal(), getSubtotal(), getTax(), null);
        cardTransaction.setCard(card);
        cardTransaction.setBillingAddress(new Address(null, null, null, null, "84096", "Debit Card Customer"));

        cardSaleTest(path, cardTransaction);

        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        affiliatePayouts.add(new AffiliateCharge(null, "6", BigDecimal.valueOf(4.88)));
        affiliatePayouts.add(new AffiliateCharge(null, "7", BigDecimal.valueOf(7.03)));
        cardTransaction = new Transaction("6", "22", getTeamTwoId(), TransactionType.DEBIT_CARD_SALE.slug,
                getTotal(), getSubtotal(), getTax(), null);
        cardTransaction.setCard(card);
        cardTransaction.setBillingAddress(new Address(null, null, null, null, "84096", "Debit Card Customer"));
        cardTransaction.setAffiliatePayouts(affiliatePayouts);

        cardSaleTest(path, cardTransaction);

        checkBadSaleRequest(path);
    }

    @Test
    public void debitCardSubscriptionTest() {
        String path = "/transactions";

        Transaction cardTransaction = new Transaction("company", "16", getTeamTwoId(), TransactionType.DEBIT_CARD_SUB.slug,
                getTotal(), getSubtotal(), getTax(), "Subscription payment");
        cardTransaction.setCard(card);
        cardTransaction.setBillingAddress(new Address(null, null, null, null, "84096", "Debit Card Customer"));

        cardSaleTest(path, cardTransaction);

        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        affiliatePayouts.add(new AffiliateCharge(null, "6", BigDecimal.valueOf(3.33)));
        affiliatePayouts.add(new AffiliateCharge(null, "7", BigDecimal.valueOf(4.87)));

        cardTransaction = new Transaction("company", "16", getTeamTwoId(), TransactionType.DEBIT_CARD_SUB.slug,
                getTotal(), getSubtotal(), getTax(), "Subscription payment");
        cardTransaction.setCard(card);
        cardTransaction.setBillingAddress(new Address(null, null, null, null, "84096", "Debit Card Customer"));
        cardTransaction.setAffiliatePayouts(affiliatePayouts);

        cardSaleTest(path, cardTransaction);

        checkBadSaleRequest(path);
    }

    private Transaction cardSaleTest(String path, Transaction cardTransaction) {
        Transaction resultTransaction = performPostRequest(path, cardTransaction, 1, false);

        // Make sure the response is good
        assertTransactionValidForSale(resultTransaction, cardTransaction);

        // Make sure the database is good
        Transaction savedTransaction = getSqlSession().getMapper(TransactionMapper.class).findById(resultTransaction.getId());
        assertTransactionValidForSale(savedTransaction, cardTransaction);
        assertAffiliateChargesValid(savedTransaction.getId(), cardTransaction.getAffiliatePayouts());
        return resultTransaction;
    }

    private void checkBadSaleRequest(String path) {
        //magstripe or cardnumber and zip required
        performBadPostRequest(path,
                new CardPayment(null, getBadRequestPayeeId(), getTeamTwoId(), null, getTax(), getSubtotal(), null, null, null, null, null, null, null));
        // TODO check magstripe
    }

}
