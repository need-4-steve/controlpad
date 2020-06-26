package com.controlpad.pay_fac.transaction;

import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction.TransactionResult;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import com.controlpad.payman_common.transaction_charge.TransactionChargeMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.controlpad.payman_common.util.GsonUtil;
import com.google.gson.reflect.TypeToken;
import org.junit.Test;

import java.math.BigDecimal;
import java.util.List;

public class TaxPaymentTransactionTest extends TransactionControllerTest {

    @Override
    public boolean isAutoCommit() {
        return true;
    }

    @Test
    public void testACHTaxPayment() {
        // TODO this feature might need reworked
//        String path = "/transactions";
//        String userId = "ACHTaxPaymentUser";

//        Payment payment = new Payment(userId, userId, getTeamTwoId(), null, null, null, getTax(), "ACH tax payment");
        // Under construction
//        performBadPostRequest(path, payment, status().isMethodNotAllowed());

//        EntryMapper entryMapper = getSqlSession().getMapper(EntryMapper.class);
//        TransactionMapper transactionMapper = getSqlSession().getMapper(TransactionMapper.class);
//        UserBalancesMapper userTaxBalanceMapper = getSqlSession().getMapper(UserBalancesMapper.class);
//        UserAccountMapper userAccountMapper = getSqlSession().getMapper(UserAccountMapper.class);
//
//        UserBalances userBalances = new UserBalances(userId, getTeamTwoId());
//        userTaxBalanceMapper.insert(userBalances);
//        userTaxBalanceMapper.add(userBalances.getId(), BigDecimal.ZERO, getTax(), getTax());
//
//        createCashTransaction(userId, transactionMapper);
//
//        UserAccount userAccount = new UserAccount(userId, userId, "324377516", "123456789", "checking", null, true);
//        userAccountMapper.insert(userAccount);
//
//        Payment payment = new Payment(userId, userId, getTeamTwoId(), null, null, null, getTax(), "ACH tax payment");
//        TransactionResponse transactionResponse = performPost(path, payment, new TypeToken<TransactionResponse>(){});
//        assert transactionResponse.getSuccess();
//
//        Transaction savedTransaction = transactionMapper.findById(transactionResponse.getTransactionId());
//        System.out.println("Saved Transaction: " + GsonUtil.getGson().toJson(savedTransaction));
//        assert savedTransaction != null;
//        assert savedTransaction.getPayeeUserId().equals(userId);
//        assert savedTransaction.getPayerUserId().equals(userId);
//        assert savedTransaction.getAmount().equals(payment.getTotal());
//        assert savedTransaction.getProcessed();
//        assert savedTransaction.getSalesTax() == null;
//        assert savedTransaction.getShipping() == null;
//        assert TransactionType.findBySlug(savedTransaction.getTransactionType()) == TransactionType.ACH_PAYMENT_TAX;
//        assert savedTransaction.getDescription().equals(payment.getDescription());
//
//        List<Entry> payouts = entryMapper.listByTransactionId(savedTransaction.getId());
//        BigDecimal totalFees = assertFees(savedTransaction, payouts);
//        assertSinglePayoutTypeCreated(payouts, PaymentType.SALES_TAX,
//                payment.getTotal().subtract(totalFees));
//
//        performUnsuccessfulRequest(path,
//                new TransferPayment(new Money(201D), "ACH tax payment", userId, userId, "2"),
//                TransactionResult.Balance_Lower.getStatusCode());
//
//        userAccountMapper.markAccountInvalid(userId);
//        performUnsuccessfulRequest(path,
//                new TransferPayment(new Money(20D), "ACH tax payment", userId, userId, "2"),
//                TransactionResult.Account_Not_Validated.getStatusCode());

    }

    @Test
    public void testECheckTaxPayment() {
        String path = "/transactions";
        String userId = "ECheckTaxPaymentUser2";

        TransactionMapper transactionMapper = getSqlSession().getMapper(TransactionMapper.class);
        UserBalancesMapper userTaxBalanceMapper = getSqlSession().getMapper(UserBalancesMapper.class);
        UserBalances userTaxBalance = new UserBalances(userId, getTeamTwoId(), 0D, 0D, 0D);
        userTaxBalanceMapper.insert(userTaxBalance);

        createCashTransaction(userId, transactionMapper);

        Transaction checkTransaction = new Transaction("company", userId, getTeamTwoId(), TransactionType.E_CHECK_PAYMENT_TAX.slug,
                getTax(), null, null, "ECheck tax payment");
        checkTransaction.setBankAccount(new Account(userId, "324377516", "123456789", "checking"));

        Transaction transactionResponse = performPost(path, checkTransaction, new TypeToken<Transaction>(){});
        assert transactionResponse.getResultCode() == 1;

        Transaction savedTransaction = transactionMapper.findById(transactionResponse.getId());
        System.out.println("Saved Transaction: " + GsonUtil.getGson().toJson(savedTransaction));
        assert savedTransaction != null;
        assert savedTransaction.getPayeeUserId().equals("company");
        assert savedTransaction.getPayerUserId().equals(userId);
        assert savedTransaction.getAmount().equals(checkTransaction.getAmount());
        assert !savedTransaction.getProcessed();
        assert savedTransaction.getSalesTax().compareTo(BigDecimal.ZERO) == 0;
        assert savedTransaction.getShipping().compareTo(BigDecimal.ZERO) == 0;
        assert TransactionType.findBySlug(savedTransaction.getTransactionType()) == TransactionType.E_CHECK_PAYMENT_TAX;
        assert savedTransaction.getDescription().equals(checkTransaction.getDescription());

        // TODO verify validations
    }

    @Test
    public void testEWalletTaxPayment() {
        String path = "/transactions";
        String userId = "EWalletTaxPaymentUser2";

        EntryMapper entryMapper = getSqlSession().getMapper(EntryMapper.class);
        TransactionMapper transactionMapper = getSqlSession().getMapper(TransactionMapper.class);
        UserBalancesMapper userBalancesMapper = getSqlSession().getMapper(UserBalancesMapper.class);

        // Set up initial test data
        UserBalances userBalances = new UserBalances(userId, getTeamTwoId(), BigDecimal.ZERO, new Money(100D), new Money(100D));
        userBalancesMapper.insert(userBalances);

        createCashTransaction(userId, transactionMapper);

        // Check insufficient funds
        Transaction taxPayment = new Transaction("company", userId, getTeamTwoId(), TransactionType.E_WALLET_PAYMENT_TAX.slug,
                getTax(), null, null, "Ewallet tax payment");

        performPostRequest(path, taxPayment, TransactionResult.Insufficient_Funds.getResultCode(), false);

        // Bump balances
        userBalancesMapper.add(userBalances.getId(), userBalances.getSalesTax(), userBalances.getEWallet(), userBalances.getTransaction());

        // Check good transaction
        Transaction transactionResponse = performPost(path, taxPayment, new TypeToken<Transaction>(){});
        assert transactionResponse.getResultCode() == 1;

        Transaction savedTransaction = transactionMapper.findById(transactionResponse.getId());
        System.out.println("Saved Transaction: " + GsonUtil.getGson().toJson(savedTransaction));
        assert savedTransaction != null;
        assert savedTransaction.getPayeeUserId().equals("company");
        assert savedTransaction.getPayerUserId().equals(userId);
        assert savedTransaction.getAmount().equals(taxPayment.getAmount());
        assert savedTransaction.getProcessed();
        assert savedTransaction.getSalesTax().compareTo(BigDecimal.ZERO) == 0;
        assert savedTransaction.getShipping().compareTo(BigDecimal.ZERO) == 0;
        assert TransactionType.findBySlug(savedTransaction.getTransactionType()) == TransactionType.E_WALLET_PAYMENT_TAX;
        assert savedTransaction.getDescription().equals(taxPayment.getDescription());

        List<Entry> payouts = entryMapper.listByTransactionId(savedTransaction.getId());
        assertSinglePayoutTypeCreated(payouts, PaymentType.SALES_TAX,
                taxPayment.getAmount().negate());

        UserBalances dbBalances = userBalancesMapper.find(userId, getTeamTwoId());
        assert userBalances.getSalesTax().add(taxPayment.getAmount()).compareTo(dbBalances.getSalesTax()) == 0;
        assert userBalances.getEWallet().subtract(taxPayment.getAmount()).compareTo(dbBalances.getEWallet()) == 0;
        assert userBalances.getTransaction().subtract(taxPayment.getAmount()).compareTo(dbBalances.getTransaction()) == 0;

        // Check balance too low
        performPostRequest(path, taxPayment, TransactionResult.Balance_Lower.getResultCode(), false);
    }

    private void createCashTransaction(String userId, TransactionMapper transactionMapper) {
        Transaction cashTransaction = new Transaction(getIdUtil().generateId(), userId, "Fake Customer", getTeamTwoId(), null, TransactionType.CASH_SALE.slug,
                getSubtotal().add(getTax()), getTax(), null, "S", TransactionResult.Success.getResultCode(), null, null, null);
        transactionMapper.insert(cashTransaction);
        getSqlSession().getMapper(TransactionChargeMapper.class).insert(new TransactionCharge(userId, cashTransaction.getId(),
                null, getTax(), PaymentType.SALES_TAX.slug));
    }

}
