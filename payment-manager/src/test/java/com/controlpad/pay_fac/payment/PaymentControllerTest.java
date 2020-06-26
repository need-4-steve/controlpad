package com.controlpad.pay_fac.payment;

import com.controlpad.pay_fac.ControllerTest;
import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.pay_fac.payment_file.PaymentFileDummyDataUtil;
import com.controlpad.pay_fac.transaction.TransactionUtil;
import com.controlpad.pay_fac.transaction_processing.TransactionProcessUtil;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.ewallet.EWallet;
import com.controlpad.payman_common.ewallet.EWalletMapper;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.payment.PaymentMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.payment_file.PaymentFile;
import com.controlpad.payman_common.payment_file.PaymentFileMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import com.controlpad.payman_common.transaction_charge.TransactionChargeMapper;
import com.controlpad.payman_common.transaction_debit.TransactionDebit;
import com.controlpad.payman_common.transaction_debit.TransactionDebitMapper;
import com.controlpad.payman_common.user_account.UserAccount;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.controlpad.payman_common.util.GsonUtil;
import com.google.gson.reflect.TypeToken;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;

import java.math.BigDecimal;
import java.math.RoundingMode;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

public class PaymentControllerTest extends ControllerTest {

    @Autowired
    TransactionProcessUtil transactionProcessUtil;
    @Autowired
    PaymentFileDummyDataUtil paymentFileDummyDataUtil;

    @Override
    public boolean isAutoCommit() {
        return true;
    }

    @Test
    public void testGet() {
        String path = "/payments/%s";
        Payment testPayment = new Payment(getIdUtil().generateId(), getTeamTwoId(), "paymentGetUser",
                null, new Money(10D), null, null, PaymentType.WITHDRAW.slug);
        getSqlSession().getMapper(PaymentMapper.class).insert(testPayment);

        Payment resultPayment = performGet(String.format(path, testPayment.getId()), new TypeToken<Payment>(){});
        assert resultPayment != null;
        assert resultPayment.getId().equals(testPayment.getId());
        assert resultPayment.getUserId().equals(testPayment.getUserId());
        assert resultPayment.getType().equals(testPayment.getType());
    }

    @Test
    public void testList() {
        Long paymentFileId = paymentFileDummyDataUtil.getDownloadFile().getId();
        String userId = "paymentListUser";
        String paymentType = PaymentType.WITHDRAW.slug;

        String path = String.format("/payments?paymentFileId=%s&userId=%s&teamId=%s&type=%s&returned=%s&page=1&count=50",
                paymentFileId, userId, getTeamTwoId(), paymentType, false);

        PaymentMapper paymentMapper = getSqlSession().getMapper(PaymentMapper.class);

        List<Payment> filteredPayments = new ArrayList<>();
        // Invalid user
        filteredPayments.add(new Payment(getIdUtil().generateId(), getTeamTwoId(), "invalidUser",
                null, new Money(10D), paymentFileId, null, paymentType));
        // Wrong type
        filteredPayments.add(new Payment(getIdUtil().generateId(), getTeamTwoId(), userId,
                null, new Money(10D), paymentFileId, null, PaymentType.SALES_TAX.slug));
        // returned
        Payment returnedPayment = new Payment(getIdUtil().generateId(), getTeamTwoId(), userId,
                null, new Money(10D), paymentFileId, null, paymentType);
        filteredPayments.add(returnedPayment);
        // wrong team
        filteredPayments.add(new Payment(getIdUtil().generateId(), getTeamOneId(), userId,
                null, new Money(10D), paymentFileId, null, paymentType));
        // null file
        filteredPayments.add(new Payment(getIdUtil().generateId(), getTeamTwoId(), userId,
                null, new Money(10D), null, null, paymentType));

        paymentMapper.insertList(filteredPayments);
        paymentMapper.markReturned(returnedPayment.getId());

        List<Payment> expectedPayments = new ArrayList<>();
        expectedPayments.add(new Payment(getIdUtil().generateId(), getTeamTwoId(), userId,
                null, new Money(10D), paymentFileId, null, paymentType));
        expectedPayments.add(new Payment(getIdUtil().generateId(), getTeamTwoId(), userId,
                null, new Money(20D), paymentFileId, null, paymentType));

        paymentMapper.insertList(expectedPayments);

        PaginatedResponse<Payment> resultPayments = performGet(path, new TypeToken<PaginatedResponse<Payment>>(){});
        assert resultPayments.getTotal().equals(2L);
        assert resultPayments.getData().size() == 2;
        assert resultPayments.getData().get(0).getId().equals(expectedPayments.get(0).getId());
        assert resultPayments.getData().get(1).getId().equals(expectedPayments.get(1).getId());
    }

    @Test
    public void testValidationsForDebitReturn() {
        String userId = "debitReturnValidationUser";

        PaymentFileMapper paymentFileMapper = getSqlSession().getMapper(PaymentFileMapper.class);
        TransactionDebitMapper debitMapper = getSqlSession().getMapper(TransactionDebitMapper.class);

        // Create fake data for testing
        Transaction transaction = new Transaction(getIdUtil().generateId(), userId, userId, getTeamTwoId(), null, TransactionType.ACH_PAYMENT_TAX.slug,
                BigDecimal.valueOf(100D), BigDecimal.ZERO, BigDecimal.ZERO, "S", 1, null, null, null);
        TransactionUtil.insertTransaction(getSqlSession(), transaction, getIdUtil(), null);
        transactionProcessUtil.processACHTaxPayment(getSqlSession(), getMockData().getTestClient().getId(), transaction);

        PaymentFile paymentFile = new PaymentFile("paymentValidationTestFile", "payment returned validation test", BigDecimal.ZERO,
                BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.valueOf(100D), 1, 1L, 1, getTeamTwoId());
        paymentFileMapper.insertPaymentFile(paymentFile);

        // Set debit to non submitted file
        TransactionDebit debit = debitMapper.listForTransactionid(transaction.getId()).get(0);
        debit.setPaymentFileId(paymentFile.getId());
        debitMapper.setPaymentFileId(debit);

        String path = String.format(Locale.US, "/payments/%s/returned", debit.getId());

        // Check for payment doesn't exist code:-1
        performGet("/payments/invalid/returned", false, -1);

        // check payment not submitted yet code:-2
        performGet(path, false, -2);

        // check payment already returned code:-3
        paymentFileMapper.markSubmitted(paymentFile.getId());
        paymentFileDummyDataUtil.addSubmittedFile(paymentFile);
        debitMapper.markReturned(debit.getId());
        performGet(path, false, -3);

    }

    @Test
    public void testValidationsForPayoutReturn() {
        String userId = "payoutReturnValidationUser";

        PaymentFileMapper paymentFileMapper = getSqlSession().getMapper(PaymentFileMapper.class);
        PaymentMapper paymentMapper = getSqlSession().getMapper(PaymentMapper.class);

        // Check for payment doesn't exist code:-1
        performGet("/payments/invalid/returned", false, -1);

        PaymentFile paymentFile = new PaymentFile("debitValidationTestFile", "debit returned validation test", BigDecimal.ZERO,
                BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.valueOf(55.55), 1, 1L, 1, getTeamTwoId());
        paymentFileMapper.insertPaymentFile(paymentFile);
        Payment payment = new Payment(getIdUtil().generateId(), getTeamTwoId(), userId, null,
                BigDecimal.valueOf(55.55), paymentFile.getId(), null, PaymentType.WITHDRAW.slug);
        paymentMapper.insert(payment);

        String path = String.format(Locale.US, "/payments/%s/returned", payment.getId());

        // check payment not submitted yet code:-2
        performGet(path, false, -2);

        // check payment already returned code:-3
        paymentFileMapper.markSubmitted(paymentFile.getId());
        paymentFileDummyDataUtil.addSubmittedFile(paymentFile);
        paymentMapper.markPaidForFileId(paymentFile.getId());
        paymentMapper.markReturned(payment.getId());
        performGet(path, false, -3);
    }

    @Test
    public void testPayoutReturn() {
        String userId = "payoutReturnedUser";
        BigDecimal amount = BigDecimal.valueOf(100D).setScale(5, RoundingMode.HALF_UP);

        PaymentMapper paymentMapper = getSqlSession().getMapper(PaymentMapper.class);
        PaymentFileMapper paymentFileMapper = getSqlSession().getMapper(PaymentFileMapper.class);

        // Make sure there is an unvalidated account for the merchant return to invalidate
        UserAccount userAccount = new UserAccount(userId, userId, "324377516", "123456789", "checking", "", true);
        getSqlSession().getMapper(UserAccountMapper.class).insert(userAccount);

        // Create a file and mark submitted
        PaymentFile paymentFile = new PaymentFile("payoutReturnedTestFile", "payout return test", amount,
                BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO, 1, 1L, 1, getTeamTwoId());
        paymentFileMapper.insertPaymentFile(paymentFile);
        paymentFileMapper.markSubmitted(paymentFile.getId());
        paymentFileDummyDataUtil.addSubmittedFile(paymentFile);

        // Assign a merchant payout batch to the file
        Payment payment = new Payment(getIdUtil().generateId(), getTeamTwoId(), userId, null,
                amount, paymentFile.getId(), null, PaymentType.WITHDRAW.slug);
        paymentMapper.markPaidForFileId(paymentFile.getId());
        System.out.println("Payment: " + GsonUtil.getGson().toJson(payment));

        // Perform return
        String path = String.format(Locale.US, "/payments/%s/returned", payment.getId());
        performGet(path, true, 2);

        // TODO verify that entries and user balance updates properly
    }

    @Test
    public void testDebitTaxPaymentReturn() {
        String userId = "achTaxPaymentReturnUser";
        BigDecimal amount = BigDecimal.valueOf(150D);

        TransactionDebitMapper debitMapper = getSqlSession().getMapper(TransactionDebitMapper.class);
        UserBalancesMapper userBalancesMapper = getSqlSession().getMapper(UserBalancesMapper.class);

        UserBalances userBalances = new UserBalances(userId, getTeamTwoId(), 0D, 0D, 0D);
        userBalancesMapper.insert(userBalances);

        Transaction transaction = new Transaction(getIdUtil().generateId(), userId, userId, getTeamTwoId(), null, TransactionType.ACH_PAYMENT_TAX.slug,
                amount, BigDecimal.ZERO, BigDecimal.ZERO, "S", 1, null, null, null);
        TransactionUtil.insertTransaction(getSqlSession(), transaction, getIdUtil(), null);
        transactionProcessUtil.processACHTaxPayment(getSqlSession(), getMockData().getTestClient().getId(), transaction);

        // Get payouts to compare to charges
        EntryMapper entryMapper = getSqlSession().getMapper(EntryMapper.class);
        List<Entry> entries = entryMapper.listByTransactionId(transaction.getId());

        // Create payment file, mark submitted
        PaymentFile paymentFile = new PaymentFile("TaxPaymentFile", "tax payment test", BigDecimal.ZERO, BigDecimal.ZERO,
                BigDecimal.ZERO, BigDecimal.ZERO, 1, 1L, 1, getTeamTwoId());
        PaymentFileMapper fileMapper = getSqlSession().getMapper(PaymentFileMapper.class);
        fileMapper.insertPaymentFile(paymentFile);
        fileMapper.markSubmitted(paymentFile.getId());
        paymentFileDummyDataUtil.addSubmittedFile(paymentFile);

        // Get created debit record and set to file
        TransactionDebit debit = debitMapper.listForTransactionid(transaction.getId()).get(0);
        debit.setPaymentFileId(paymentFile.getId());
        debitMapper.setPaymentFileId(debit);

        // Perform a return on the debit
        String path = String.format("/payments/%s/returned", debit.getId());
        performGet(path, true, 1);

        List<TransactionCharge> charges = getSqlSession().getMapper(TransactionChargeMapper.class).listForTransactionId(transaction.getId());
        assert charges.size() == entries.size();
        boolean taxCharged = false;
        for(int i = 0; i < charges.size(); i++) {
            assert entries.get(i).getType().equals(charges.get(i).getType());
            assert entries.get(i).getAmount().equals(charges.get(i).getAmount());
            if (charges.get(i).getType().equals(PaymentType.SALES_TAX.slug)) {
                assert !taxCharged; // Make sure tax is only charged once
                taxCharged = true;
                UserBalances databaseBalance = userBalancesMapper.find(userId, getTeamTwoId());
                System.out.println("Balances:");
                System.out.println(getGson().toJson(databaseBalance));
                System.out.println("Starter Balance:");
                System.out.println(getGson().toJson(userBalances));
                System.out.println("Charge: ");
                System.out.println(getGson().toJson(charges.get(i)));
                BigDecimal charge = charges.get(i).getAmount();
                System.out.println("Converted charge amount: " + charge.toString());
                assert databaseBalance.getSalesTax().compareTo(userBalances.getSalesTax().add(charge)) == 0;
            }
        }
        assert taxCharged; // Make sure ewallet was charged
    }

    @Test
    public void testDebitEWalletDepositReturn() {
        String userId = "debitEwalletDepositReturnUser";
        BigDecimal amount = BigDecimal.valueOf(100D);

        TransactionDebitMapper debitMapper = getSqlSession().getMapper(TransactionDebitMapper.class);
        EWalletMapper eWalletMapper = getSqlSession().getMapper(EWalletMapper.class);

        // Create an ewallet with less money than transaction amount to see that ewallet can go negative
        EWallet eWallet = new EWallet(userId, getTeamTwoId(), amount.divide(BigDecimal.valueOf(2.00), 2, RoundingMode.HALF_UP), BigDecimal.ZERO, true);
        eWalletMapper.insert(eWallet);

        // Create and process a transaction for deposit
        Transaction transaction = new Transaction(getIdUtil().generateId(), userId, userId, getTeamTwoId(), null, TransactionType.ACH_DEPOSIT_E_WALLET.slug,
                amount, BigDecimal.ZERO, BigDecimal.ZERO, "S", 1, null, null, null);
        TransactionUtil.insertTransaction(getSqlSession(), transaction, getIdUtil(), null);
        transactionProcessUtil.processEWalletDepositACH(getSqlSession(), getMockData().getTestClient().getId(), transaction);

        // Get payouts to compare to charges
        EntryMapper entryMapper = getSqlSession().getMapper(EntryMapper.class);
        List<Entry> payouts = entryMapper.listByTransactionId(transaction.getId());

        // Create payment file, mark submitted
        PaymentFile paymentFile = new PaymentFile("EwalletDepostFile", "ewallet depost return test",
                BigDecimal.ZERO, BigDecimal.valueOf(100D), BigDecimal.ZERO, BigDecimal.ZERO, 1, 1L,
                1, getTeamTwoId());
        PaymentFileMapper fileMapper = getSqlSession().getMapper(PaymentFileMapper.class);
        fileMapper.insertPaymentFile(paymentFile);
        fileMapper.markSubmitted(paymentFile.getId());
        paymentFileDummyDataUtil.addSubmittedFile(paymentFile);

        // Get created debit record and set to file
        TransactionDebit debit = debitMapper.listForTransactionid(transaction.getId()).get(0);
        debit.setPaymentFileId(paymentFile.getId());
        debitMapper.setPaymentFileId(debit);

        String path = String.format("/payments/%s/returned", debit.getId());

        performGet(path, true, 1);

        // TODO verify that entries and user balances are updated
    }
}
