package com.controlpad.pay_fac.transaction;

import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.address.Address;
import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction.TransactionType;
import org.junit.Test;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

public class CheckTransactionTest extends TransactionControllerTest {

    String url = "/transactions";

    @Test
    public void checkSaleTest() {
        checkTest(TransactionType.CHECK_SALE);
    }

    @Test
    public void checkSubTest() {
        checkTest(TransactionType.CHECK_SUB);
    }

    private void checkTest(TransactionType transactionType) {
        Account account = new Account("Account Holder Name", "324377516", "123456789", "checking");
        Address billingAddress = new Address(null, null, null, null, null, "Check Customer");

        Transaction checkPayment = new Transaction("12", "55", getTeamTwoId(),
                transactionType.slug, getTotal(), getSubtotal(), getTax(), "Sold my grandpas glasses.");
        checkPayment.setBankAccount(account);
        checkPayment.setBillingAddress(billingAddress);

        Transaction transactionResponse = performPostRequest(url, checkPayment, 1, false);

        //check sale works
        assertTransactionValidForSale(transactionResponse, checkPayment);
        //check database
        Transaction savedTransaction = getSqlSession().getMapper(TransactionMapper.class).findById(transactionResponse.getId());
        assertTransactionValidForSale(savedTransaction, checkPayment);

        // affiliatePayouts work
        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        affiliatePayouts.add(new AffiliateCharge(null, "19", BigDecimal.valueOf(1.77)));
        affiliatePayouts.add(new AffiliateCharge(null, "20", BigDecimal.valueOf(4.39)));
        checkPayment = new Transaction("12", "55", getTeamTwoId(),
                transactionType.slug, new Money(21.20), new Money(20.00), new Money(1.20), null);
        checkPayment.setBankAccount(account);
        checkPayment.setBillingAddress(billingAddress);
        checkPayment.setAffiliatePayouts(affiliatePayouts);

        transactionResponse = performPostRequest(url, checkPayment, 1, false);
        assertTransactionValidForSale(transactionResponse, checkPayment);
        savedTransaction = getSqlSession().getMapper(TransactionMapper.class).findById(transactionResponse.getId());
        assertTransactionValidForSale(savedTransaction, checkPayment);

        assertAffiliateChargesValid(savedTransaction.getId(), checkPayment.getAffiliatePayouts());

        checkPayment.setAffiliatePayouts(null);
        //account name required
        checkPayment.setBankAccount(new Account(null, "324377516", "123456789", "checking"));
        performBadPostRequest(url, checkPayment);

        //account routing required
        checkPayment.setBankAccount(new Account("Account Holder", null, "123456789", "checking"));
        performBadPostRequest(url, checkPayment);

        //account number required
        checkPayment.setBankAccount(new Account("Account Holder", "324377516", null, "checking"));
        performBadPostRequest(url, checkPayment);

        //Check bad affiliate payout requests
        checkBadAffiliatePayoutRequest(checkPayment);
    }

    private void checkBadAffiliatePayoutRequest(Transaction checkPayment) {
        List<AffiliateCharge> affiliatePayouts = new ArrayList<>();
        checkPayment.setAffiliatePayouts(affiliatePayouts);

        // Check payee
        affiliatePayouts.add(new AffiliateCharge(null, null, BigDecimal.valueOf(10.00)));
        performBadPostRequest(url, checkPayment);

        // Check amount
        affiliatePayouts.clear();
        affiliatePayouts.add(new AffiliateCharge(null, "5", null));
        performBadPostRequest(url, checkPayment);

        // Check amount limit
        affiliatePayouts.clear();
        affiliatePayouts.add(new AffiliateCharge(null, "5", BigDecimal.valueOf(20.01)));
        performBadPostRequest(url, checkPayment);
    }
}
