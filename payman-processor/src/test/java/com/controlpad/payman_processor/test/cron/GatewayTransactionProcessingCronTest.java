/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_processor.test.cron;

import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.affiliate_charge.AffiliateChargeMapper;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.consignment.Consignment;
import com.controlpad.payman_common.consignment.ConsignmentMapper;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.fee.FeeIds;
import com.controlpad.payman_common.fee.FeeMapper;
import com.controlpad.payman_common.fee.TeamFeeSet;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionType;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.team.PayoutMethod;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamConfig;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.controlpad.payman_common.util.GsonUtil;
import com.controlpad.payman_processor.CronTest;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import com.controlpad.payman_processor.gateway.GatewayUtil;
import com.controlpad.payman_processor.transaction_processing.TransactionProcessingTask;
import org.joda.time.DateTime;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;

import java.math.BigDecimal;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import static com.controlpad.payman_common.transaction.TransactionType.*;

public class GatewayTransactionProcessingCronTest extends CronTest {

    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    GatewayUtil gatewayUtil;

    private List<Transaction> companyTransactions = new ArrayList<>();
    private List<Transaction> repTransactions = new ArrayList<>();
    private List<Transaction> consignmentTransactions = new ArrayList<>();
    private List<Transaction> noTaxTransactions = new ArrayList<>();
    private List<Transaction> feeTransactions = new ArrayList<>();
    private Transaction affiliateTransaction;
    private Transaction cashTransaction;

    private List<Transaction> existingTransactions = new ArrayList<>();
    private Consignment consignmentUserConsignment;
    private AffiliateCharge affiliateCharge;
    private TransactionBatch transactionBatch;
    private GatewayConnection llcGatewayConnection;

    private Map<String, UserBalances> userBalanceCalculations = new HashMap<>();

    // Calculated as fees are added, deducted as payouts are analyzed
    private BigDecimal cashFeeBalance = new Money(0D);
    private BigDecimal cashTaxBalance = new Money(0D);
    private Fee ccFee = new Fee(BigDecimal.valueOf(3.25), true, "Credit Card Fee", null);
    private Fee ccSwipeFee = new Fee(new Money(2.9), true, "Card Swipe Fee", null);

    Team company = new Team("company-transaction-process", "Company transaction processing test",
            new TeamConfig(false, true, true, true,
                    false, null, null, PayoutMethod.FILE.getSlug(), PayoutMethod.FILE.getSlug(), null));
    Team rep = new Team("rep-transaction-process", "Rep transaction processing test",
            new TeamConfig(false, false, true, true,
                    false, null, null, PayoutMethod.FILE.getSlug(), PayoutMethod.FILE.getSlug(), null));
    Team noTax = new Team("no-tax-process", "Not processing tax test",
            new TeamConfig(false, false, false, false,
                    true, null, null, PayoutMethod.FILE.getSlug(), PayoutMethod.FILE.getSlug(), null));
    Team feeTeam = new Team("fee-transaction-process", "Fees processing test",
            new TeamConfig(false, false, false, false,
                    false, null, null, PayoutMethod.FILE.getSlug(), PayoutMethod.FILE.getSlug(), null));

    private String user1Id = "TPUser1";
    private String consignmentUserId = "TPUser2";
    private String llcUserId = "TPUser3";
    private String affiliateUserId = "TPUser4";

    private TransactionMapper transactionMapper;
    private EntryMapper entryMapper;
    private UserBalancesMapper userBalancesMapper;
    private ConsignmentMapper consignmentMapper;

    @Test
    public void fullCheck() {
        try {
            getClientSqlSession().getConnection().setAutoCommit(true);
        } catch (SQLException e) {
            e.printStackTrace();
            assert false;
        }

        transactionMapper = getClientSqlSession().getMapper(TransactionMapper.class);
        entryMapper = getClientSqlSession().getMapper(EntryMapper.class);
        userBalancesMapper = getClientSqlSession().getMapper(UserBalancesMapper.class);
        consignmentMapper = getClientSqlSession().getMapper(ConsignmentMapper.class);

        loadDummyData();

        // Run process
        new TransactionProcessingTask(sqlSessionUtil, gatewayUtil, getTestUtil().getMockData().getTestClient().getId(), null).run();

        // Check that no entries exist for existing(processed) transaction list because we are making sure that they don't process
        existingTransactions.forEach(transaction -> {
            assert entryMapper.listByTransactionId(transaction.getId()).isEmpty();
        });
        // Make sure cash transaction didn't process
        assert entryMapper.listByTransactionId(cashTransaction.getId()).isEmpty();

        for (Transaction companyTransaction : companyTransactions) {
            verifyTransaction(companyTransaction, true, false, false, false);
        }
        for (Transaction repTransaction : repTransactions) {
            verifyTransaction(repTransaction, true, false, false, false);
        }
        for (Transaction consignmentTransaction : consignmentTransactions) {
            verifyTransaction(consignmentTransaction, true, true, false, false);
        }
        for (Transaction noTaxTransaction : noTaxTransactions) {
            verifyTransaction(noTaxTransaction, false, false, false, false);
        }
        for (Transaction feeTransaction : feeTransactions) {
            verifyTransaction(feeTransaction, feeTeam.getConfig().getCollectSalesTax(), false, false, true);
        }
        verifyTransaction(affiliateTransaction, true, false, true, false);

        // user balance null team is when 'funds comany' is true for gateway connection
        // Verify rep balances
        UserBalances repDBBalance = userBalancesMapper.find(user1Id, rep.getId());
        assert repDBBalance != null;
        System.out.println("user1 BalanceCalculations: " + getGson().toJson(userBalanceCalculations.get(user1Id)));
        System.out.println("repDBBalance: " + getGson().toJson(repDBBalance));
        assert repDBBalance.getTransaction().compareTo(userBalanceCalculations.get(user1Id).getTransaction()) == 0;
        assert repDBBalance.getEWallet().compareTo(userBalanceCalculations.get(user1Id).getEWallet()) == 0;

        // Verify consignment user balances
        UserBalances consignmentDBBalance = userBalancesMapper.find(consignmentUserId, rep.getId());
        assert consignmentDBBalance != null;
        assert consignmentDBBalance.getTransaction().compareTo(userBalanceCalculations.get(consignmentUserId).getTransaction()) == 0;
        assert consignmentDBBalance.getEWallet().compareTo(userBalanceCalculations.get(consignmentUserId).getEWallet()) == 0;

        // noTax team should cause teamId to be set for user balance record
        UserBalances llcDBBalance = userBalancesMapper.find(llcUserId, noTax.getId());
        assert llcDBBalance != null;
        assert llcDBBalance.getTransaction().compareTo(userBalanceCalculations.get(llcUserId).getTransaction()) == 0;
        assert llcDBBalance.getEWallet().compareTo(userBalanceCalculations.get(llcUserId).getEWallet()) == 0;

        // Verify affiliate balance increase
        UserBalances affiliateBalance = userBalancesMapper.find(affiliateUserId, company.getId());
        assert affiliateBalance != null;
        assert affiliateBalance.getEWallet().compareTo(affiliateCharge.getAmount()) == 0;
        assert affiliateBalance.getTransaction().compareTo(affiliateCharge.getAmount()) == 0;
        // TODO verify affiliate charge not processed for auto payment setting

        // Make sure consignment updates as expected
        assert consignmentMapper.findForUserId(consignmentUserId).getBalance().compareTo(consignmentUserConsignment.getBalance()) == 0;
    }

    private void verifyTransaction(Transaction transaction, boolean expectTax, boolean expectConsignment,
                                   boolean expectAffiliate, boolean expectFee) {
        System.out.println("verifyTransaction():");
        System.out.println(GsonUtil.getGson().toJson(transaction));
        assert transactionMapper.isProcessed(transaction.getId());

        boolean merchantRecord = transaction.getPayeeUserId().equalsIgnoreCase("company");
        boolean salesTax = (!expectTax || transaction.getSalesTax().compareTo(BigDecimal.ZERO) == 0);
        boolean consignment = !expectConsignment;
        boolean fee = !expectFee;
        boolean affiliateIn = !expectAffiliate;
        boolean affiliateOut = !expectAffiliate || transaction.getPayeeUserId().equalsIgnoreCase("company");
        List<Entry> entries = entryMapper.listByTransactionId(transaction.getId());
        System.out.println("Entries:");
        System.out.println(GsonUtil.getGson().toJson(entries));
        for (Entry entry : entries) {
            PaymentType paymentType = PaymentType.findForSlug(entry.getType());
            switch (paymentType) {
                case AFFILIATE:
                    if (userBalancesMapper.findForId(entry.getBalanceId()).getUserId().equals(affiliateUserId)) {
                        assert !affiliateIn;
                        assert entry.getAmount().compareTo(affiliateCharge.getAmount()) == 0;
                        affiliateIn = true;
                    } else {
                        assert userBalancesMapper.findForId(entry.getBalanceId()).getUserId().equals("Company");
                        assert !affiliateOut;
                        assert entry.getAmount().negate().compareTo(affiliateCharge.getAmount()) == 0;
                        affiliateOut = true;
                    }
                    assert entry.getProcessed();
                    break;
                case FEE:
                    assert !fee;
                    if (entry.getFeeId().equals(ccSwipeFee.getId())) {
                        assert entry.getAmount().negate().compareTo(ccSwipeFee.calculateChargeAmount(transaction)) == 0;
                    } else {
                        assert entry.getAmount().negate().compareTo(ccFee.calculateChargeAmount(transaction)) == 0;
                    }
                    fee = true;
                    break;
                case CONSIGNMENT:
                    assert !consignment;
                    assert entry.getAmount().negate().compareTo(consignmentUserConsignment.calculateChargeForSubtotal(transaction)) == 0;
                    consignment = true;
                    consignmentUserConsignment.setBalance(consignmentUserConsignment.getBalance().add(entry.getAmount()));
                    break;
                case SALES_TAX:
                    assert !salesTax;
                    assert entry.getAmount().negate().compareTo(transaction.getSalesTax()) == 0;
                    salesTax = true;
                    break;
                case MERCHANT:
                    assert !merchantRecord;
                    assert entry.getAmount().compareTo(transaction.getAmount()) == 0;
                    merchantRecord = true;
                    userBalanceCalculations.get(transaction.getPayeeUserId()).addTransaction(entry.getAmount());
                    assert entry.getProcessed();
                    break;
            }
            userBalanceCalculations.get(transaction.getPayeeUserId()).addEWallet(entry.getAmount());
        }

        assert merchantRecord;
        assert fee;
        assert consignment;
        assert salesTax;
        assert affiliateIn;
        assert affiliateOut;
    }

    private void loadDummyData() {
        TeamMapper teamMapper = getClientSqlSession().getMapper(TeamMapper.class);
        FeeMapper feeMapper = getClientSqlSession().getMapper(FeeMapper.class);

        teamMapper.insert(company);
        teamMapper.insert(rep);
        teamMapper.insert(noTax);
        teamMapper.insert(feeTeam);

        llcGatewayConnection = new GatewayConnection(noTax.getId(), llcUserId, "Some LLC", null, "Fake key",
                null, null, GatewayConnectionType.MOCK.slug, true, false, true, false, false, true);
        getClientSqlSession().getMapper(GatewayConnectionMapper.class).insert(llcGatewayConnection);

        transactionBatch = new TransactionBatch(1L, null, null, 3, null, null);
        TransactionBatchMapper transactionBatchMapper = getClientSqlSession().getMapper(TransactionBatchMapper.class);
        transactionBatchMapper.insert(transactionBatch);
        // Make sure this batch will process outside the 4 hour buffer
        transactionBatchMapper.markSettledForId(transactionBatch.getId(), DateTime.now().minusHours(5));

        userBalanceCalculations.put(user1Id, new UserBalances(user1Id, rep.getId(), BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO));
        userBalanceCalculations.put(consignmentUserId, new UserBalances(user1Id, rep.getId(), BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO));
        userBalanceCalculations.put(llcUserId, new UserBalances(llcUserId, noTax.getId(), BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO));
        userBalanceCalculations.put("Company", new UserBalances("Company", null, BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO));

        feeMapper.insertFee(ccFee);
        feeMapper.insertFee(ccSwipeFee);
        feeMapper.insertTeamFeeSet(new TeamFeeSet(feeTeam.getId(), CREDIT_CARD_SALE.slug, "Credit Card Sale",
                new FeeIds(ccFee.getId())));
        feeMapper.insertTeamFeeSet(new TeamFeeSet(feeTeam.getId(), CARD_SWIPE_SALE.slug, "Card Swipe Sale",
                new FeeIds(ccSwipeFee.getId())));

        consignmentUserConsignment = new Consignment(consignmentUserId, new Money(200D), new Money(10D), true);

        consignmentMapper.insert(consignmentUserConsignment);

        companyTransactions.add(new Transaction(newID(), "Company", "User 1", company.getId(), "1", CREDIT_CARD_SUB.slug,
                new Money(30.00D), new Money(0D), "S", 1, 1L, null));
        companyTransactions.add(new Transaction(newID(), "Company", "User 2", company.getId(), "1", DEBIT_CARD_SUB.slug,
                new Money(30.00D), new Money(0D), "S", 1, 1L, null));
        companyTransactions.add(new Transaction(newID(), "Company", "User 3", company.getId(), "1", CHECK_SUB.slug,
                new Money(30.00D), new Money(0D), "S", 1, 1L, null));
        companyTransactions.add(new Transaction(newID(), "Company", "User 5", company.getId(), "1", CREDIT_CARD_SALE.slug,
                new Money(10.60D), new Money(0.60D), "S", 1, 1L, null));
        companyTransactions.add(new Transaction(newID(), "Company", "User 6", company.getId(), "1", DEBIT_CARD_SALE.slug,
                new Money(15.90D), new Money(0.90D), "S", 1, 1L, null));
        companyTransactions.add(new Transaction(newID(), "Company", "User 7", company.getId(), "1", CHECK_SALE.slug,
                new Money(12.72D), new Money(0.72D), "S", 1, 1L, null));


        affiliateTransaction = (new Transaction(newID(), "Company", "Customer", company.getId(), null, CREDIT_CARD_SALE.slug,
                new Money(42.76), new Money(2.78), "S", 1, 1L, null));

        repTransactions.add(new Transaction(newID(), user1Id, "Customer 1", rep.getId(), "2", CREDIT_CARD_SALE.slug,
                new Money(16.96D), new Money(0.96D), "S", 1, 2L, null));
        repTransactions.add(new Transaction(newID(), user1Id, "Customer 2", rep.getId(), "2", DEBIT_CARD_SALE.slug,
                new Money(19.08D), new Money(1.08D), "S", 1, 2L, null));
        repTransactions.add(new Transaction(newID(), user1Id, "Customer 3", rep.getId(), "2", CHECK_SALE.slug,
                new Money(23.32D), new Money(1.32D), "S", 1, 2L, null));

        consignmentTransactions.add(new Transaction(newID(), consignmentUserId, "Customer 4", rep.getId(), "2", CREDIT_CARD_SALE.slug,
                new Money(39.22D), new Money(2.22D), "S", 1, 2L, null));
        consignmentTransactions.add(new Transaction(newID(), consignmentUserId, "Customer 5", rep.getId(), "2", DEBIT_CARD_SALE.slug,
                new Money(29.68D), new Money(1.68D), "S", 1, 2L, null));
        consignmentTransactions.add(new Transaction(newID(), consignmentUserId, "Customer 6", rep.getId(), "2", CHECK_SALE.slug,
                new Money(34.98D), new Money(1.98D), "S", 1, 2L, null));

        // This transaction shouldn't retract tax
        noTaxTransactions.add(new Transaction(newID(), llcUserId, "Customer 22", noTax.getId(), null, CREDIT_CARD_SALE.slug,
                new Money(25.00D), new Money(1.5), "S", 1, llcGatewayConnection.getId(), null));

        // Fee transactions
        feeTransactions.add(new Transaction(newID(), "Company", "Customer", feeTeam.getId(), null, CREDIT_CARD_SALE.slug,
                new Money(33.87), new Money(2.00), "S", 1, 1L, null));
        Transaction swipedTransaction = new Transaction(newID(), "Company", "Customer", feeTeam.getId(), null, CREDIT_CARD_SALE.slug,
                new Money(33.66), new Money(2.00), "S", 1, 1L, null);
        swipedTransaction.setSwiped(true);
        feeTransactions.add(swipedTransaction);

        existingTransactions.add(new Transaction(newID(), "Company", "User 4", company.getId(), "1", CREDIT_CARD_SUB.slug,
                new Money(30.00D), new Money(0D), "S", 1, 1L, null));
        existingTransactions.add(new Transaction(newID(), "User 5", "Customer 9", rep.getId(), "2", CREDIT_CARD_SALE.slug,
                new Money(58.30D), new Money(3.3D), "S", 1, 2L, null));
        existingTransactions.add(new Transaction(newID(), "User 6", "Customer 10", rep.getId(), "2", DEBIT_CARD_SALE.slug,
                new Money(51.94D), new Money(2.94D), "S", 1, 2L, null));

        cashTransaction = new Transaction(newID(), "User 7", "Customer", company.getId(), null, CASH_SALE.slug,
                new Money(15D), new Money(0.90), "S", 1, null, null);

        // TODO add a batch and transaction that isn't at a good settled_at date

        Transaction declineTransaction = new Transaction(newID(), "Company", "User 1", company.getId(), "1", CREDIT_CARD_SUB.slug,
                new Money(50.00D), new Money(3D), "D", 20, 1L, null);

        // Insert records
        insertTransactionList(companyTransactions);
        insertTransactionList(repTransactions);
        insertTransactionList(consignmentTransactions);
        insertTransactionList(noTaxTransactions);
        insertTransactionList(feeTransactions);
        insertTransactionList(existingTransactions);
        insertTransaction(affiliateTransaction);
        insertTransaction(declineTransaction);

        // Mark these ones as processed, we test that they aren't picked up by the processor
        transactionMapper.markProcessedForList(existingTransactions);

        affiliateCharge = new AffiliateCharge(affiliateTransaction.getId(), affiliateUserId, new Money(22D));
        getClientSqlSession().getMapper(AffiliateChargeMapper.class).insert(affiliateCharge);
    }

    private void insertTransactionList(List<Transaction> transactions) {
        transactions.forEach(this::insertTransaction);
    }

    private void insertTransaction(Transaction transaction) {
        transactionMapper.insert(transaction);
        transaction.setBatchId(transactionBatch.getId());
        transactionMapper.updateBatchId(transaction);
    }

    private String newID() {
        return getIdUtil().generateId();
    }
}