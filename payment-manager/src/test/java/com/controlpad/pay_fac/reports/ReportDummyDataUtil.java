/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.reports;

import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import com.controlpad.pay_fac.payment_file.PaymentFileDummyDataUtil;
import com.controlpad.pay_fac.test.MockData;
import com.controlpad.pay_fac.test.TestUtil;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.payment_file.PaymentFile;
import com.controlpad.payman_common.payment_file.PaymentFileMapper;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamConfig;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import com.controlpad.payman_common.transaction_charge.TransactionChargeMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import org.apache.ibatis.session.SqlSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

@Component
public class ReportDummyDataUtil {

    private List<Transaction> unpaidTransactions = new ArrayList<>();
    private List<Transaction> filteredTransactions = new ArrayList<>();
    private List<Transaction> cashTransactions = new ArrayList<>();
    private List<TransactionCharge> paidTransactionCharges = new ArrayList<>();
    private List<TransactionCharge> unpaidTransactionCharges = new ArrayList<>();

    private String payeeUserId = "ReportUser 1";
    private String teamId = "reports-test";

    private PaymentFileDummyDataUtil paymentFileDummyDataUtil;

    @Autowired
    public ReportDummyDataUtil(SqlSessionUtil sqlSessionUtil, PaymentFileDummyDataUtil paymentFileDummyDataUtil, TestUtil testUtil) {
        this.paymentFileDummyDataUtil = paymentFileDummyDataUtil;
        SqlSession sqlSession = sqlSessionUtil.openSession(testUtil.getMockData().getTestClient().getId(), true);
        loadDummyData(sqlSession, testUtil.getMockData(), testUtil.getIdUtil());
        sqlSession.close();
    }

    public String getPayeeUserId() {
        return payeeUserId;
    }

    public List<Transaction> getUnpaidTransactions() {
        return unpaidTransactions;
    }

    public List<Transaction> getFilteredTransactions() {
        return filteredTransactions;
    }

    public List<Transaction> getCashTransactions() {
        return cashTransactions;
    }

    public List<TransactionCharge> getPaidTransactionCharges() {
        return paidTransactionCharges;
    }

    public List<TransactionCharge> getUnpaidTransactionCharges() {
        return unpaidTransactionCharges;
    }

    private void loadDummyData(SqlSession sqlSession, MockData mockData, IDUtil idUtil) {
        TransactionChargeMapper transactionChargeMapper = sqlSession.getMapper(TransactionChargeMapper.class);
        TransactionMapper transactionMapper = sqlSession.getMapper(TransactionMapper.class);
        PaymentFileMapper paymentFileMapper = sqlSession.getMapper(PaymentFileMapper.class);
        TransactionBatchMapper transactionBatchMapper = sqlSession.getMapper(TransactionBatchMapper.class);
        EntryMapper entryMapper = sqlSession.getMapper(EntryMapper.class);
        UserBalancesMapper userBalancesMapper = sqlSession.getMapper(UserBalancesMapper.class);
        TeamMapper teamMapper = sqlSession.getMapper(TeamMapper.class);

        teamMapper.insert(new Team(teamId, "Processing Fee Test", new TeamConfig(true, false, true, false, false, new Money(3000D), "none")));

        Fee cpFee = mockData.getControlpadFee();

        PaymentFile submittedFile = paymentFileDummyDataUtil.getSubmittedFiles().get(0);

        TransactionBatch submittedTransactionBatch = new TransactionBatch(2L, "444", 444, 1, null, submittedFile.getId());
        transactionBatchMapper.insert(submittedTransactionBatch);

        PaymentFile paymentFile = paymentFileDummyDataUtil.getNonSubmittedFiles().get(0);

        TransactionBatch transactionBatch = new TransactionBatch(2L, "555", 555, 1, null, paymentFile.getId());
        transactionBatchMapper.insert(transactionBatch);

        unpaidTransactions.add(new Transaction(idUtil.generateId(), payeeUserId, teamId, TransactionType.CREDIT_CARD_SALE.slug,
                new Money(53D), new Money(3D), "P", 1, transactionBatch.getId(), 2L));
        unpaidTransactions.add(new Transaction(idUtil.generateId(), payeeUserId, teamId, TransactionType.CREDIT_CARD_SALE.slug,
                new Money(42.4D), new Money(2.4D), "P", 1, transactionBatch.getId(), 2L));
        unpaidTransactions.add(new Transaction(idUtil.generateId(), payeeUserId, teamId, TransactionType.DEBIT_CARD_SALE.slug,
                new Money(31.8D), new Money(1.8D), "P", 1, transactionBatch.getId(), 2L));
        unpaidTransactions.add(new Transaction(idUtil.generateId(), payeeUserId, teamId, TransactionType.CREDIT_CARD_SALE.slug,
                new Money(23.32D), new Money(1.32D), "P", 1, null, 1L));

        unpaidTransactions.forEach(transaction -> {
            transactionMapper.insert(transaction);
        });

        filteredTransactions.add(new Transaction(idUtil.generateId(), "Invalid User 1", teamId, TransactionType.CREDIT_CARD_SALE.slug,
                new Money(74.6D), new Money(4.2D), "P", 1, null, 2L));
        filteredTransactions.add(new Transaction(idUtil.generateId(), payeeUserId, teamId, TransactionType.DEBIT_CARD_SALE.slug,
                new Money(34.98D), new Money(1.98), "S", 1, submittedTransactionBatch.getId(), 2L));

        filteredTransactions.forEach(transaction -> {
            transactionMapper.insert(transaction);
        });
        transactionMapper.markProcessed(filteredTransactions.get(1).getId());

        cashTransactions.add(new Transaction(idUtil.generateId(), payeeUserId, null, teamId, null, TransactionType.CASH_SALE.slug,
                new Money(20D), new Money(1.2D), null, "S", 1, null, null, null));
        cashTransactions.add(new Transaction(idUtil.generateId(), payeeUserId, null, teamId, null, TransactionType.CASH_SALE.slug,
                new Money(30D), new Money(1.8D), null, "S", 1, null, null, null));
        cashTransactions.add(new Transaction(idUtil.generateId(), payeeUserId, null, teamId, null, TransactionType.CASH_SALE.slug,
                new Money(25D), new Money(1.5D), null, "S", 1, null, null, null));

        cashTransactions.forEach(transaction -> {
            transactionMapper.insert(transaction);
        });

        addCashCharges(paidTransactionCharges, cashTransactions.get(0), new Money(.25D), cpFee);
        addCashCharges(unpaidTransactionCharges, cashTransactions.get(1), new Money(.25D), cpFee);
        addCashCharges(unpaidTransactionCharges, cashTransactions.get(2), null, cpFee);

        paidTransactionCharges.forEach(transactionCharge -> {
            transactionChargeMapper.insert(transactionCharge);
            transactionChargeMapper.markPaid(transactionCharge.getId(), null);
            transactionCharge.setProcessed(true);
        });
        unpaidTransactionCharges.forEach(transactionCharge -> {
            transactionChargeMapper.insert(transactionCharge);
            transactionCharge.setProcessed(false);
        });

        addEntries(filteredTransactions.get(1), entryMapper, userBalancesMapper);
    }

    private void addCashCharges(List<TransactionCharge> list, Transaction cashTransaction, BigDecimal consignmentPercent, Fee cpFee) {
        list.add(new TransactionCharge(cashTransaction.getPayeeUserId(), cashTransaction.getId(),
                null, cashTransaction.getSalesTax(), PaymentType.SALES_TAX.slug));
        list.add(new TransactionCharge(cashTransaction.getPayeeUserId(), cashTransaction.getId(),
                cpFee.getAccountId(), cpFee.getAmount(), cpFee.getId(), PaymentType.SALES_TAX.slug));

        if (consignmentPercent != null) {
            list.add(new TransactionCharge(cashTransaction.getPayeeUserId(), cashTransaction.getId(),
                    null, (cashTransaction.getSubTotal().multiply(consignmentPercent)), PaymentType.CONSIGNMENT.slug));
        }
    }

    private void addEntries(Transaction transaction, EntryMapper entryMapper, UserBalancesMapper balancesMapper) {
        UserBalances userBalances = new UserBalances(transaction.getPayeeUserId(), transaction.getTeamId());
        balancesMapper.insert(userBalances);
        List<Entry> entries = new ArrayList<>();
        entries.add(new Entry(userBalances.getId(),transaction.getSalesTax(), transaction.getId(), null,
                null, PaymentType.SALES_TAX.slug, true));
        entries.add(new Entry(userBalances.getId(),new Money(0.15D), transaction.getId(), 3L,
                null, PaymentType.FEE.slug, false));
        entries.add(new Entry(userBalances.getId(), transaction.getSubTotal().subtract(new Money(0.15D)),
                transaction.getId(), null, null, PaymentType.MERCHANT.slug, true));
// TODO set entries or payments?
        entryMapper.insertList(entries);
        transaction.setEntries(entries);
    }
}