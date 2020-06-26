package com.controlpad.pay_fac.transaction;

import com.controlpad.pay_fac.ControllerTest;
import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.affiliate_charge.AffiliateChargeMapper;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import org.apache.commons.lang3.StringUtils;
import org.junit.Before;

import java.math.BigDecimal;
import java.math.RoundingMode;
import java.util.List;
import java.util.Locale;
import java.util.Random;

import static org.hamcrest.Matchers.*;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.jsonPath;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

public abstract class TransactionControllerTest extends ControllerTest {

    private Random rnd = new Random();
    private BigDecimal subtotal = BigDecimal.ZERO;
    private BigDecimal tax = BigDecimal.ZERO;
    private String badRequestPayeeId = "100";

    @Before
    public void beforeTransactionControllerTest() {
        generateSubtotalAndTax();
    }

    protected void generateSubtotalAndTax() {
        subtotal = BigDecimal.valueOf(rnd.nextDouble() * 10 + 50).setScale(2, RoundingMode.HALF_UP);
        tax = subtotal.multiply(BigDecimal.valueOf(0.06)).setScale(2, RoundingMode.HALF_UP);
    }

    protected BigDecimal getSubtotal() {
        return subtotal;
    }

    protected BigDecimal getTotal() {
        return subtotal.add(tax);
    }

    protected BigDecimal getTax() {
        return tax;
    }

    public String getBadRequestPayeeId() {
        return badRequestPayeeId;
    }

    protected Transaction performPostRequest(String path, Object body, int expectedResult, boolean useAdmin) {
        String authorization =
                (useAdmin ?
                        getTestUtil().getMockData().getAdminClientSession().getId():
                        getTestUtil().getMockData().getTestApiKey().getId()
                );
        try {
            return getGson().fromJson(getMockMvc().perform(post(path)
                    .header((useAdmin ? "SessionKey" : "APIKey"), authorization)
                    .contentType(getJsonContentType())
                    .content(getGson().toJson(body)))
                    .andExpect(status().isOk())
                    .andExpect(jsonPath("$['resultCode']", is(expectedResult)))
                    .andExpect(jsonPath("$['id']", not(isEmptyOrNullString())))
                    .andReturn().getResponse().getContentAsString(), Transaction.class);
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    protected void assertTransactionValidForSale(Transaction resultTransaction, Transaction originalTransaction) {
        System.out.println("assertTransactionValidForSale");
        System.out.println("Transaction: " + resultTransaction.toString());
        System.out.println("Payment: " + originalTransaction.toString());
        TransactionMapper transactionMapper = getNewSqlSession(true).getMapper(TransactionMapper.class);

        assert transactionMapper.existsForId(resultTransaction.getId());
        assert resultTransaction.getId().length() == 15;
        // Transaction amounts are correct
        if (originalTransaction.getSalesTax() != null) {
            assert resultTransaction.getSalesTax().compareTo(originalTransaction.getSalesTax()) == 0;
        }
        assert resultTransaction.getAmount().compareTo(originalTransaction.getAmount()) == 0;
        assert originalTransaction.getTransactionType().equals(resultTransaction.getTransactionType());
    }

    protected void assertTransactionValidForTransfer(Transaction transaction, Transaction originalTransaction) {
        System.out.println("assertTransactionValidForSale");
        System.out.println("Transaction: " + transaction.toString());
        System.out.println("Payment: " + originalTransaction.toString());
        TransactionMapper transactionMapper = getNewSqlSession(true).getMapper(TransactionMapper.class);

        assert transactionMapper.existsForId(transaction.getId());
        assert transaction.getId().length() == 15;
        // Transaction amounts are correct
        assert transaction.getSalesTax() == null;
        assert transaction.getAmount().equals(originalTransaction.getAmount());
        assert originalTransaction.getTransactionType().equals(transaction.getTransactionType());
    }

    protected void assertAffiliateChargesValid(String transactionId, List<AffiliateCharge> originalAffiliates) {
        if (originalAffiliates == null) {
            return;
        }
        AffiliateChargeMapper affiliateChargeMapper = getSqlSession().getMapper(AffiliateChargeMapper.class);


        List<AffiliateCharge> charges = affiliateChargeMapper.listForTransactionId(transactionId);
        assert charges.size() == originalAffiliates.size();
        AffiliateCharge charge;
        AffiliateCharge payout;
        for (int i = 0; i < charges.size(); i++) {
            charge = charges.get(i);
            payout = originalAffiliates.get(i);
            assert charge.getAmount().compareTo(payout.getAmount()) == 0;
            assert StringUtils.equals(charge.getPayeeUserId(), payout.getPayeeUserId());
            assert StringUtils.equals(transactionId, charge.getTransactionid());
        }
    }

    protected Transaction internalPaymentTest(String path, Transaction transaction, boolean useAdmin) {
        Transaction transactionResponse = performPostRequest(path, transaction, 1, useAdmin);

        assertTransactionValidForTransfer(transactionResponse, transaction);

        return transactionResponse;
    }

    protected void assertThatEwalletDecremented(UserBalances userBalances, Transaction transaction) {
        assertThatEwalletDecremented(userBalances, transaction.getAmount());
    }

    protected void assertThatEwalletIncremented(UserBalances userBalances, Transaction transaction) {
        assertThatEwalletIncremented(userBalances, transaction.getAmount());
    }

    protected void assertThatEwalletDecremented(UserBalances userBalances, BigDecimal amount) {
        BigDecimal balance = getSqlSession().getMapper(UserBalancesMapper.class).find(userBalances.getUserId(), userBalances.getTeamId()).getEWallet();
        System.out.println("assertEwalletDecremented: " + balance + ", " + userBalances.getEWallet());
        assert balance != null;
        assert balance.compareTo(userBalances.getEWallet().subtract(amount)) == 0;
        userBalances.setEWallet(balance);
    }

    protected void assertThatEwalletIncremented(UserBalances userBalances, BigDecimal amount) {
        BigDecimal balance = getSqlSession().getMapper(UserBalancesMapper.class).find(userBalances.getUserId(), userBalances.getTeamId()).getEWallet();
        System.out.println("assertEwalletIncremented: " + balance + ", " + userBalances.getEWallet());
        assert balance != null;
        assert balance.compareTo(userBalances.getEWallet().add(amount)) == 0;
        userBalances.setEWallet(balance);
    }

    protected BigDecimal assertFees(Transaction transaction) {
        List<Entry> entries = getSqlSession().getMapper(EntryMapper.class).listByTransactionId(transaction.getId());
        return assertFees(transaction, entries);
    }

    protected BigDecimal assertFees(Transaction transaction, List<Entry> entries) {
        List<Fee> fees = getMockData().getFeesForTeamAndType(getTeamTwoId(), transaction.getTransactionType());
        BigDecimal totalFees = BigDecimal.ZERO.setScale(2, RoundingMode.HALF_UP);
        for (Fee fee: fees) {
            totalFees = totalFees.add(fee.calculateChargeAmount(transaction));
        }
        BigDecimal totalPayouts = BigDecimal.ZERO.setScale(2, RoundingMode.HALF_UP);
        for (Entry entry : entries) {
            totalPayouts = totalPayouts.add(entry.getAmount());
            if (entry.getFeeId() != null) {
                Fee fee = getMockData().getFeeForId(entry.getFeeId());
                assert fee != null;
                assert fees.contains(fee);
                assert entry.getAmount().compareTo(fee.getAmount()) == 0;
                assert PaymentType.findForSlug(entry.getType()) == PaymentType.FEE;
                fees.remove(fee);
            }
        }
        System.out.println(String.format(Locale.US, "Assert fees amounts| totalFees: %.2f | totalPayouts: %.2f | transaction: %.2f",
                totalFees, totalPayouts, transaction.getAmount()));
        assert totalPayouts.compareTo(transaction.getAmount()) == 0;
        assert fees.isEmpty(); // Because we take them out as they are identified
        return totalFees;
    }

    protected void assertSinglePayoutTypeCreated(List<Entry> entries, PaymentType type, BigDecimal amount) {
        boolean singlePayoutCreated = false;
        for (Entry entry : entries) {
            if (PaymentType.findForSlug(entry.getType()) == type) {
                assert !singlePayoutCreated;
                singlePayoutCreated = true;
                if (amount != null)
                    assert amount.compareTo(entry.getAmount()) == 0;
            }
        }
        assert singlePayoutCreated;
    }
}
