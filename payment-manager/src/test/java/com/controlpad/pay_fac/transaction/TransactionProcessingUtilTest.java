/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.transaction;

import com.controlpad.pay_fac.SqlSessionTest;
import com.controlpad.pay_fac.transaction_processing.TransactionProcessUtil;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.consignment.Consignment;
import com.controlpad.payman_common.consignment.ConsignmentMapper;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import com.controlpad.payman_common.transaction_charge.TransactionChargeMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.controlpad.payman_common.util.GsonUtil;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;

import java.math.BigDecimal;
import java.util.List;

public class TransactionProcessingUtilTest extends SqlSessionTest {

    @Autowired
    TransactionProcessUtil transactionProcessUtil;

    @Override
    public boolean isAutoCommit() {
        return true;
    }

    @Test
    public void processCashSaleTest() {
        String payeeUserId = "CashProcessUser1";
        TransactionMapper transactionMapper = getSqlSession().getMapper(TransactionMapper.class);
        Transaction transaction = new Transaction(null, payeeUserId, getMockData().getTeamTwo().getId(), TransactionType.CASH_SALE.slug,
                new Money(58.3D), new Money(3.3D), "S", 1, null, null);
        TransactionUtil.insertTransaction(getSqlSession(), transaction, getIdUtil(), null);

        Consignment consignment = new Consignment(payeeUserId, new Money(100D), new Money(10D), true);
        getSqlSession().getMapper(ConsignmentMapper.class).insert(consignment);

        transactionProcessUtil.processCashSale(getTestUtil().getMockData().getTestClient().getId(), getSqlSession(), transaction);

        // Transaction charges properly saved
        TransactionChargeMapper transactionChargeMapper = getSqlSession().getMapper(TransactionChargeMapper.class);
        List<TransactionCharge> transactionCharges = transactionChargeMapper.listForTransactionId(transaction.getId());
        System.out.println("TransactionCharge list size: " + transactionCharges.size());

        // Check that fees are saved and correct amounts
        List<Fee> fees = getTestUtil().getMockData().getFeesForTeamAndType(getMockData().getTeamTwo().getId(), TransactionType.CASH_SALE.slug);
        System.out.println("Fee list size: " + fees.size());
        for(Fee fee : fees) {
            System.out.println("Checking fee: " + fee);
            boolean found = false;
            for(TransactionCharge transactionCharge : transactionCharges) {
                if (transactionCharge.getFeeId() != null && transactionCharge.getFeeId().equals(fee.getId())) {
                    found = true;
                    assert fee.calculateChargeAmount(transaction).compareTo(transactionCharge.getAmount()) == 0;
                    break;
                }
            }
            assert found;
        }

        boolean taxChargeCreated = false;
        for(TransactionCharge transactionCharge : transactionCharges) {
            System.out.println("Checking transaction charge: " + transactionCharge);
            switch (PaymentType.findForSlug(transactionCharge.getType())) {
                case SALES_TAX:
                    taxChargeCreated = true;
                    assert transactionCharge.getAmount().compareTo(transaction.getSalesTax()) == 0;
                    break;
            }
        }
        assert taxChargeCreated;

        // Make sure we only have as many Transaction charges as there are fees + 1 tax
        assert transactionCharges.size() == fees.size() + 1;
    }

    @Test
    public void processEWalletSaleTest() {
        String payeeUserId = "EWalletSaleProcessUser";
        String payerUserId = "EWalletSaleProcessCustomer";

        UserBalancesMapper userBalancesMapper = getSqlSession().getMapper(UserBalancesMapper.class);
        UserBalances payeeBalances = new UserBalances(payeeUserId, getMockData().getTeamTwo().getId());
        userBalancesMapper.insert(payeeBalances);
        UserBalances payerUserBalance = new UserBalances(payerUserId, getMockData().getTeamTwo().getId(),
                BigDecimal.ZERO, new Money(100D), new Money(100D));
        userBalancesMapper.insert(payerUserBalance);
        userBalancesMapper.add(payerUserBalance.getId(), BigDecimal.ZERO, new Money(100D), new Money(100D));
        Transaction transaction = new Transaction(null, payeeUserId, payerUserId, getMockData().getTeamTwo().getId(), null,
                TransactionType.E_WALLET_SALE.slug, new Money(46.64D), new Money(2.64D), null, "S", 1, null, null, null);

        // Insert test data
        TransactionUtil.insertTransaction(getSqlSession(), transaction, getIdUtil(), null);

        // Run process for sale
        transactionProcessUtil.processEWalletSale(getSqlSession(), getMockData().getTestClient().getId(), transaction, payerUserBalance);

        assertTransfer(transaction, payeeBalances, payerUserBalance);
    }

    @Test
    public void processEWalletTransferTest() {
        String payeeUserId = "EWalletTransferPayee";
        String payerUserId = "EWalletTransferPayer";

        UserBalancesMapper userBalancesMapper = getSqlSession().getMapper(UserBalancesMapper.class);
        UserBalances payeeBalances = new UserBalances(payeeUserId, getMockData().getTeamTwo().getId());
        userBalancesMapper.insert(payeeBalances);

        UserBalances payerBalances = new UserBalances(payerUserId, getMockData().getTeamTwo().getId(),
                BigDecimal.ZERO, new Money(75D), new Money(100D));
        userBalancesMapper.insert(payerBalances);
        userBalancesMapper.add(payerBalances.getId(), BigDecimal.ZERO, new Money(75D), new Money(100D));

        Transaction transaction = new Transaction(null, payeeUserId, payerUserId, getMockData().getTeamTwo().getId(), null,
                TransactionType.E_WALLET_TRANSFER.slug, new Money(25D), null, null, "S", 1, null, null, null);

        // Insert test data
        TransactionUtil.insertTransaction(getSqlSession(), transaction, getIdUtil(), null);

        // Run process for transfer
        transactionProcessUtil.processEWalletTransfer(getSqlSession(), getMockData().getTestClient().getId(), transaction);

        assertTransfer(transaction, payeeBalances, payerBalances);
    }

    @Test
    public void processEWalletCreditTest() {
        String payeeUserId = "EWalletCreditPayee";

        UserBalances payeeBalances = new UserBalances(payeeUserId, getMockData().getTeamTwo().getId(), BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO);
        Transaction transaction = new Transaction(null, payeeUserId, "COMPANY", getMockData().getTeamTwo().getId(), null,
                TransactionType.E_WALLET_CREDIT.slug, new Money(30D), null, null, "S", 1, null, null, null);

        // Insert test data
        TransactionUtil.insertTransaction(getSqlSession(), transaction, getIdUtil(), null);
        UserBalancesMapper userBalancesMapper = getSqlSession().getMapper(UserBalancesMapper.class);
        userBalancesMapper.insert(payeeBalances);

        // Run process for credit
        transactionProcessUtil.processEWalletCredit(getSqlSession(), transaction);

        // Check entries
        List<Entry> entries = getSqlSession().getMapper(EntryMapper.class).listByTransactionId(transaction.getId());
        assert entries.size() == 1;
        assert entries.get(0).getAmount().compareTo(transaction.getAmount()) == 0;
        UserBalances payeeResultBalance = userBalancesMapper.findForId(payeeBalances.getId());
        assert payeeResultBalance.getEWallet().compareTo(transaction.getAmount()) == 0;
        assert payeeResultBalance.getTransaction().compareTo(transaction.getAmount()) == 0;
    }

    @Test
    public void processEWalletWithdrawTest() {
        String userId = "EWalletWithdrawUser";

        UserBalances userBalances = new UserBalances(userId, getMockData().getTeamTwo().getId(), BigDecimal.ZERO, new Money(100D), new Money(100D));
        getSqlSession().getMapper(UserBalancesMapper.class).insert(userBalances);

        Transaction transaction = new Transaction(null, userId, userId, getMockData().getTeamTwo().getId(), null,
                TransactionType.E_WALLET_WITHDRAW.slug, new Money(40D), null, null, "S", 1, null, null, null);

        // Insert test data
        TransactionUtil.insertTransaction(getSqlSession(), transaction, getIdUtil(), null);

        // Run process for withdraw
        transactionProcessUtil.processEWalletWithdraw(getSqlSession(), getMockData().getTestClient().getId(), transaction, userBalances);

        // Check payouts
        assertPayoutsForTransaction(transaction, userBalances);
    }

    private void assertPayoutsForTransaction(Transaction transaction, UserBalances userBalances) {
        List<Entry> entries = getSqlSession().getMapper(EntryMapper.class).listByTransactionId(transaction.getId());
        assertPayoutFeesForTransaction(transaction, entries);
        assertPayoutAmountForTransaction(transaction, entries, userBalances);
    }

    private void assertPayoutFeesForTransaction(Transaction transaction, List<Entry> entries) {
        // Check that fees are saved and correct amounts
        List<Fee> fees = getTestUtil().getMockData().getFeesForTeamAndType(getMockData().getTeamTwo().getId(), transaction.getTransactionType());
        System.out.println("Fee list size: " + fees.size());
        for(Fee fee : fees) {
            System.out.println("Checking fee: " + fee);
            boolean found = false;
            for(Entry entry : entries) {
                if (entry.getFeeId() != null && entry.getFeeId().equals(fee.getId())) {
                    found = true;
                    assert fee.calculateChargeAmount(transaction).equals(entry.getAmount());
                    break;
                }
            }
            assert found;
        }
    }

    private void assertPayoutAmountForTransaction(Transaction transaction, List<Entry> entries, UserBalances userBalances) {
        boolean taxPaid = transaction.getSalesTax() == null;
        boolean withdrawValid = TransactionType.findBySlug(transaction.getTransactionType()) != TransactionType.E_WALLET_WITHDRAW;
        BigDecimal payoutTotal = BigDecimal.ZERO;

        for(Entry entry : entries) {
            System.out.println("Checking payout: " + entry);
            switch (PaymentType.findForSlug(entry.getType())) {
                case SALES_TAX:
                    taxPaid = entry.getAmount().compareTo(transaction.getSalesTax()) == 0;
                    break;
                case WITHDRAW:
                    withdrawValid = true;
                    break;
                case MERCHANT:
                    continue;
            }
            payoutTotal = payoutTotal.add(entry.getAmount());
        }
        assert taxPaid;
        assert withdrawValid;
        assert payoutTotal.negate().compareTo(transaction.getAmount()) == 0;
    }

    private void assertThatEwalletIncremented(UserBalances userBalances, Entry entry) {
        BigDecimal balance = getSqlSession().getMapper(UserBalancesMapper.class).find(userBalances.getUserId(), userBalances.getTeamId()).getEWallet();
        assert balance != null;
        assert balance.equals(userBalances.getEWallet().add(entry.getAmount()));
        userBalances.setEWallet(balance);
    }

    private void assertTransfer(Transaction transaction, UserBalances payeeBalances, UserBalances payerUserBalance) {
        System.out.println("assertTransfer");
        System.out.println("payeeBalances:");
        System.out.println(GsonUtil.getGson().toJson(payeeBalances));
        System.out.println("payerBalances:");
        System.out.println(GsonUtil.getGson().toJson(payerUserBalance));
        UserBalancesMapper userBalancesMapper = getSqlSession().getMapper(UserBalancesMapper.class);
        // Check entries
        List<Entry> entries = getSqlSession().getMapper(EntryMapper.class).listByTransactionId(transaction.getId());
        boolean payeeValid = false;
        boolean payerValid = false;
        for (Entry entry : entries) {
            System.out.println("Entry:");
            System.out.println(GsonUtil.getGson().toJson(entry));
            switch(entry.getType()) {
                case "sales-tax":
                    assert entry.getAmount().compareTo(transaction.getSalesTax().negate()) == 0;
                    break;
                case "transfer":
                    if (entry.getBalanceId().compareTo(payerUserBalance.getId()) == 0) {
                        assert !payerValid;
                        assert entry.getAmount().negate().compareTo(transaction.getAmount()) == 0;
                        UserBalances payerResultBalance = userBalancesMapper.findForId(payerUserBalance.getId());
                        System.out.println("payerResultBalance:");
                        System.out.println(GsonUtil.getGson().toJson(payerResultBalance));
                        assert payerUserBalance.getEWallet().compareTo(payerResultBalance.getEWallet()) == 0;
                        assert payerUserBalance.getTransaction().subtract(transaction.getAmount()).compareTo(payerResultBalance.getTransaction()) == 0;
                        payerValid = true;
                        break;
                    } else if (entry.getBalanceId().compareTo(payeeBalances.getId()) == 0) {
                        assert !payeeValid;
                        assert entry.getAmount().compareTo(transaction.getAmount()) == 0;
                        UserBalances payeeResultBalance = userBalancesMapper.findForId(payeeBalances.getId());
                        System.out.println("payeeResultBalance:");
                        System.out.println(GsonUtil.getGson().toJson(payeeResultBalance));
                        BigDecimal eWalletCheck = (transaction.getSalesTax() != null ?
                                entry.getAmount().subtract(transaction.getSalesTax()) :
                                entry.getAmount()
                        );
                        assert payeeBalances.getEWallet().add(eWalletCheck).compareTo(payeeResultBalance.getEWallet()) == 0;
                        assert payeeBalances.getTransaction().add(transaction.getAmount()).compareTo(payeeResultBalance.getTransaction()) == 0;
                        payeeValid = true;
                    }
                    break;
                default:
                    assert false;
            }
        }
        assert payeeValid;
        assert payerValid;
    }
}