package com.controlpad.payman_processor.test.payout_processing;


import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.account.AccountMapper;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.consignment.Consignment;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.fee.FeeMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.merchant.Merchant;
import com.controlpad.payman_common.merchant.MerchantMapper;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.payment.PaymentMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.team.*;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import com.controlpad.payman_common.transaction_charge.TransactionChargeMapper;
import com.controlpad.payman_common.user_account.UserAccount;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.controlpad.payman_common.util.GsonUtil;
import com.controlpad.payman_processor.CronTest;
import com.controlpad.payman_processor.client.ClientConfigUtil;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import com.controlpad.payman_processor.gateway.GatewayUtil;
import com.controlpad.payman_common.payout_job.PayoutJob;
import com.controlpad.payman_common.payout_job.PayoutJobMapper;
import com.controlpad.payman_processor.payout_processing.PayoutProcessingTask;
import com.controlpad.payman_processor.util.IDUtil;
import org.joda.time.DateTime;
import org.junit.Before;
import org.springframework.beans.factory.annotation.Autowired;

import java.math.BigDecimal;
import java.math.RoundingMode;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;
import java.util.Random;

public abstract class PayoutProcessingTestBase extends CronTest {

    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    IDUtil idUtil;
    @Autowired
    GatewayUtil gatewayUtil;
    @Autowired
    ClientConfigUtil clientConfigUtil;

    private Random random = new Random();

    private EntryMapper entryMapper;
    private MerchantMapper merchantMapper;
    private PaymentMapper paymentMapper;
    private PayoutJobMapper payoutJobMapper;
    private TransactionMapper transactionMapper;
    private TransactionChargeMapper transactionChargeMapper;
    private UserBalancesMapper userBalancesMapper;

    private List<UserAccount> userAccounts = new ArrayList<>();
    private List<UserBalances> userBalances = new ArrayList<>();
    private BigDecimal taxPaymentExpected = BigDecimal.ZERO;
    private BigDecimal consignmentPaymentExpected = BigDecimal.ZERO;
    private BigDecimal feePaymentExpected = BigDecimal.ZERO;
    PayoutJob payoutJob;

    private Fee fee;
    private Consignment consignment;

    private String notAutoteamId;
    private String autoPaymentTeamId;
    private String userProcessAllId;
    private String userProcessSome;
    private String userAutoMerchantPayout;
    private String userInvalidAccount;

    private Transaction partialPayoutTransaction = null;

    void runNormalTest() {
        loadDummyData(false);

        assert payoutJobMapper.markQueued(payoutJob.getId()) == 1;

        new PayoutProcessingTask(sqlSessionUtil, clientConfigUtil, gatewayUtil, idUtil,
                getTestUtil().getMockData().getTestClient().getId(), payoutJob.getId()).run();

        verifyPayments(paymentMapper.findByTeamId(notAutoteamId), false, false);

        UserBalances allProcessBalance = userBalancesMapper.findForId(userBalances.get(0).getId());
        System.out.println("allProcessBalance: " + getGson().toJson(allProcessBalance));
        System.out.println("userBalance: " + getGson().toJson(userBalances.get(0)));
        assert allProcessBalance.getEWallet().compareTo(allProcessBalance.getTransaction()) == 0;
        assert allProcessBalance.getEWallet().compareTo(userBalances.get(0).getEWallet()) == 0;

        UserBalances partialBalance = userBalancesMapper.findForId(userBalances.get(1).getId());
        System.out.println("partialBalance: " + GsonUtil.getGson().toJson(partialBalance));
        System.out.println("userBalance: " + GsonUtil.getGson().toJson(userBalances.get(1)));
        System.out.println("transactionTax: " + partialPayoutTransaction.getSalesTax());
        assert partialBalance.getTransaction().compareTo(userBalances.get(1).getTransaction()
                .subtract(partialPayoutTransaction.getSalesTax())
                .setScale(5, RoundingMode.HALF_UP)) == 0;

        assert partialBalance.getEWallet().compareTo(userBalances.get(1).getEWallet().setScale(5, RoundingMode.HALF_UP)) == 0;

        // TODO verify entries are updated properly
    }

    void runAutoPayTest() {
        loadDummyData(true);

        assert payoutJobMapper.markQueued(payoutJob.getId()) == 1;

        new PayoutProcessingTask(sqlSessionUtil, clientConfigUtil, gatewayUtil, idUtil,
                getTestUtil().getMockData().getTestClient().getId(), payoutJob.getId()).run();

        verifyPayments(paymentMapper.findByTeamId(autoPaymentTeamId), true, true);

        UserBalances autoPaidbalance = userBalancesMapper.findForId(userBalances.get(0).getId());
        assert autoPaidbalance.getEWallet().compareTo(BigDecimal.valueOf(0.01)) < 0;
        assert autoPaidbalance.getTransaction().compareTo(BigDecimal.valueOf(0.01)) < 0;
        assert autoPaidbalance.getTransaction().compareTo(BigDecimal.ZERO) >= 0;
        assert autoPaidbalance.getEWallet().compareTo(BigDecimal.ZERO) >= 0;

        UserBalances invalidAccountBalance = userBalancesMapper.findForId(userBalances.get(1).getId());
        assert invalidAccountBalance.getTransaction().compareTo(userBalances.get(1).getEWallet()) == 0;
        assert invalidAccountBalance.getEWallet().compareTo(userBalances.get(1).getEWallet()) == 0;
        // TODO verify entries are updated properly
    }

    @Before
    public void init() {
        try {
            System.out.println("Init running before test");
            getClientSqlSession().getConnection().setAutoCommit(true);
        } catch (SQLException e) {
            e.printStackTrace();
            assert false;
        }
        payoutJobMapper = getClientSqlSession().getMapper(PayoutJobMapper.class);
        paymentMapper = getClientSqlSession().getMapper(PaymentMapper.class);
        entryMapper = getClientSqlSession().getMapper(EntryMapper.class);
        merchantMapper = getClientSqlSession().getMapper(MerchantMapper.class);
        transactionMapper = getClientSqlSession().getMapper(TransactionMapper.class);
        transactionChargeMapper = getClientSqlSession().getMapper(TransactionChargeMapper.class);
        userBalancesMapper = getClientSqlSession().getMapper(UserBalancesMapper.class);

    }

    private void loadDummyData(boolean autoPayoutMode){
        AccountMapper accountMapper = getClientSqlSession().getMapper(AccountMapper.class);
        UserAccountMapper userAccountMapper = getClientSqlSession().getMapper(UserAccountMapper.class);
        TeamMapper teamMapper = getClientSqlSession().getMapper(TeamMapper.class);

        Account consignmentAccount = new Account("Consignment Account", "987654321", "987654321", "checking", "Some Bank");
        accountMapper.insert(consignmentAccount);
        Account salesTaxAccount = new Account("Tax Account", "389216432", "123409875", "checking", "Some Bank");
        accountMapper.insert(salesTaxAccount);

        consignment = new Consignment(BigDecimal.valueOf(200D), BigDecimal.valueOf(10D), true);

        Account feeAccount = new Account("Fee Account", "123456789", "123456789", "checking", "Some Bank");
        accountMapper.insert(feeAccount);
        fee = new Fee(BigDecimal.valueOf(3.25), true, "Credit Card Fee", null);
        fee.setAccountId(feeAccount.getId());
        getClientSqlSession().getMapper(FeeMapper.class).insertFee(fee);

        if (autoPayoutMode) {
            autoPaymentTeamId = getTestDataPrefix() + "2";
            userAutoMerchantPayout = getTestDataPrefix() + "_3";
            userInvalidAccount = getTestDataPrefix() + "_4";
            Team autoTeam = new Team(autoPaymentTeamId, "Payout Processing Task Test auto payment",
                    new TeamConfig(true, true, true, true,
                            false, BigDecimal.ZERO, "none"));
            autoTeam.setConsignmentAccountId(consignmentAccount.getId());
            autoTeam.setTaxAccountId(salesTaxAccount.getId());
            autoTeam.getConfig().setMerchantPayoutMethod(getMerchantPayoutType());
            autoTeam.getConfig().setCompanyPayoutMethod(getCompanyPayoutType());
            overrideTeamSetting(autoTeam); // Allow updating stuff before insert
            teamMapper.insert(autoTeam);

            if (shouldCreateMerchants()) {
                merchantMapper.insert(new Merchant(userAutoMerchantPayout, "derp@example.com", "rep"));
            }
            userAccounts.add(new UserAccount(userAutoMerchantPayout, userAutoMerchantPayout, "324377516", "777777", "checking", null, true));
            userAccounts.add(new UserAccount(userInvalidAccount, userInvalidAccount, "324377516", "999999", "checking", null, false));

            userBalances.add(new UserBalances(userAutoMerchantPayout, autoPaymentTeamId, BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO));
            userBalances.add(new UserBalances(userInvalidAccount, autoPaymentTeamId, BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO));
            for (UserBalances userBalance : userBalances) {
                userBalancesMapper.insert(userBalance);
            }

            generateTransactionAndEntries(userBalances.get(0), autoPaymentTeamId, false, false,
                    false, true, true); // Auto payout
            generateTransactionAndEntries(userBalances.get(1), autoPaymentTeamId, false, false,
                    false, false, true); // Invalid account auto payout

        } else {
            notAutoteamId = getTestDataPrefix() + "1";
            userProcessAllId = getTestDataPrefix() + "_1";
            userProcessSome = getTestDataPrefix() + "_2";

            Team notAutoTeam = new Team(notAutoteamId, "Payout Processing Task Test",
                    new TeamConfig(true, false, true, false,
                            false, BigDecimal.ZERO, "none"));
            notAutoTeam.setConsignmentAccountId(consignmentAccount.getId());
            notAutoTeam.setTaxAccountId(salesTaxAccount.getId());
            notAutoTeam.getConfig().setMerchantPayoutMethod(getMerchantPayoutType());
            notAutoTeam.getConfig().setCompanyPayoutMethod(getCompanyPayoutType());
            overrideTeamSetting(notAutoTeam);
            teamMapper.insert(notAutoTeam);

            if (shouldCreateMerchants()) {
                merchantMapper.insert(new Merchant(userProcessAllId, "derp@example.com", "rep"));
                merchantMapper.insert(new Merchant(userProcessSome, "derp@example.com", "rep"));
            }
            userAccounts.add(new UserAccount(userProcessAllId, userProcessAllId, "324377516", "100000", "checking", null, true));
            userAccounts.add(new UserAccount(userProcessSome, userProcessSome, "324377516", "555555", "checking", null, true));

            userBalances.add(new UserBalances(userProcessAllId, notAutoteamId, BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO));
            userBalances.add(new UserBalances(userProcessSome, notAutoteamId, BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO));
            for (UserBalances userBalance : userBalances) {
                userBalancesMapper.insert(userBalance);
            }
            // Create normal entries
            generateTransactionAndEntries(userBalances.get(0), notAutoteamId, true, true, true,
                    true, false); // Perfect
            partialPayoutTransaction = generateTransactionAndEntries(userBalances.get(1), notAutoteamId, true,
                    true, true, false, false); // not enough transaction balance

            // Take away 5% from partial balance
            UserBalances lessBalance = userBalances.get(1);
            System.out.println("LessBalance:");
            System.out.println(GsonUtil.getGson().toJson(lessBalance));
            BigDecimal ewalletRedux = lessBalance.getEWallet().multiply(BigDecimal.valueOf(.93)).negate();
            BigDecimal transactionRedux = lessBalance.getTransaction().multiply(BigDecimal.valueOf(.93)).negate();
            userBalancesMapper.add(lessBalance.getId(),
                    BigDecimal.ZERO,
                    ewalletRedux,
                    transactionRedux);
            lessBalance.addTransaction(transactionRedux);
            lessBalance.addEWallet(ewalletRedux);

            // Rig amounts for partial
            if (supportFees()) {
                feePaymentExpected = feePaymentExpected.subtract(fee.calculateChargeAmount(partialPayoutTransaction));
            }
            consignmentPaymentExpected = consignmentPaymentExpected.subtract(consignment.calculateChargeForSubtotal(partialPayoutTransaction).setScale(2, RoundingMode.HALF_UP));
        }

        payoutJob = new PayoutJob(DateTime.now().toString("YYYY-MM-dd HH:mm:ss"), "inactive",
                (autoPayoutMode ? autoPaymentTeamId : notAutoteamId), PayoutScheme.MANUAL_SCHEDULE.getSlug());
        payoutJobMapper.insert(payoutJob);
        payoutJob.setId(payoutJobMapper.getGeneratedId());

        for (UserAccount userAccount : userAccounts) {
            userAccountMapper.insert(userAccount);
        }
    }

    private Transaction generateTransactionAndEntries(UserBalances userBalances, String teamId, boolean taxEntry,
                                                      boolean consignmentEntry, boolean feeEntry, boolean cashTaxEntry, boolean autoPay) {
        // TODO save entries so we can check if they are marked processed?
        double randomSubtotal = random.nextDouble() * 50 + 10;
        double tax = randomSubtotal * 0.06;
        Transaction currentTransaction = new Transaction(getIdUtil().generateId(), userBalances.getUserId(), "Customer", teamId, null,
                TransactionType.CREDIT_CARD_SALE.slug, new Money(randomSubtotal),
                new Money(tax), null, "S", 1, getConnectionForUser(userBalances.getUserId()).getId(), null, null);
        transactionMapper.insert(currentTransaction);
        BigDecimal ewalletDeduction = BigDecimal.ZERO;
        BigDecimal transactionDeduction = BigDecimal.ZERO;

        if (taxEntry) {
            Entry entryTax = new Entry(userBalances.getId(), currentTransaction.getSalesTax().negate(), currentTransaction.getId(),
                    null, null, PaymentType.SALES_TAX.slug, false);
            entryMapper.insert(entryTax);
            ewalletDeduction = ewalletDeduction.add(entryTax.getAmount());
            taxPaymentExpected = taxPaymentExpected.add(currentTransaction.getSalesTax());
        }
        if (cashTaxEntry) {
            BigDecimal cashTaxAmount = new Money(5D);
            TransactionCharge cashTaxCharge = new TransactionCharge(userBalances.getUserId(), currentTransaction.getId(),
                    null, cashTaxAmount, PaymentType.SALES_TAX.slug);
            transactionChargeMapper.insert(cashTaxCharge);
            taxPaymentExpected = taxPaymentExpected.add(cashTaxAmount);
            if (!autoPay) {
                userBalancesMapper.addSalesTax(userBalances.getId(), cashTaxAmount);
                userBalances.addSalesTax(cashTaxAmount);
            }
        }
        if (consignmentEntry) {
            Entry entryConsignment = new Entry(userBalances.getId(), consignment.calculateChargeForSubtotal(currentTransaction).setScale(2, RoundingMode.HALF_UP).negate(),
                    currentTransaction.getId(), null, null, PaymentType.CONSIGNMENT.slug, false);
            entryMapper.insert(entryConsignment);
            ewalletDeduction = ewalletDeduction.add(entryConsignment.getAmount());
            consignmentPaymentExpected = consignmentPaymentExpected.add(entryConsignment.getAmount().negate());
        }
        if (feeEntry) {
            Entry entryFee = new Entry(userBalances.getId(), fee.calculateChargeAmount(currentTransaction).negate(), currentTransaction.getId(),
                    fee.getId(), null, PaymentType.FEE.slug, !supportFees());
            entryMapper.insert(entryFee);
            ewalletDeduction = ewalletDeduction.add(entryFee.getAmount());
            if (supportFees()) {
                feePaymentExpected = feePaymentExpected.add(entryFee.getAmount().negate());
            } else {
                transactionDeduction = transactionDeduction.add(entryFee.getAmount());
            }
        }
        Entry merchantEntry = new Entry(userBalances.getId(), currentTransaction.getAmount(), currentTransaction.getId(),
                null, null, PaymentType.MERCHANT.slug, true);
        entryMapper.insert(merchantEntry);

        userBalances.setEWallet(currentTransaction.getAmount().add(ewalletDeduction));
        userBalances.setTransaction(currentTransaction.getAmount().add(transactionDeduction));
        userBalancesMapper.addTransaction(userBalances.getId(), userBalances.getTransaction());
        userBalancesMapper.addEWallet(userBalances.getId(), userBalances.getEWallet());
        return currentTransaction;
    }

    abstract boolean checkReferenceId(PaymentType paymentType);

    private void verifyPayments(List<Payment> payments, boolean autoPay, boolean cashTax) {
        BigDecimal taxAmountPaid = BigDecimal.ZERO;
        BigDecimal consignmentAmountPaid = BigDecimal.ZERO;
        BigDecimal feeAmountPaid = BigDecimal.ZERO;
        for (Payment payment : payments) {
            System.out.println("Payment:");
            System.out.println(GsonUtil.getGson().toJson(payment));
            PaymentType paymentType = PaymentType.findForSlug(payment.getType());
            switch (paymentType) {
                case FEE:
                    assert supportFees();
                    System.out.println("feePaymentExpected: " + feePaymentExpected);
                    feeAmountPaid = feeAmountPaid.add(payment.getAmount());
                    break;
                case CONSIGNMENT:
                    consignmentAmountPaid = consignmentAmountPaid.add(payment.getAmount());
                    break;
                case SALES_TAX:
                    taxAmountPaid = taxAmountPaid.add(payment.getAmount());
                    break;
                case WITHDRAW:
                    assert autoPay;
                    if (cashTax) {
                        assert payment.getAmount().add(new Money(5D)).compareTo(userBalances.get(0).getTransaction()) == 0;
                    } else {
                        assert payment.getAmount().compareTo(userBalances.get(0).getTransaction()) == 0;
                    }
                    System.out.println("TransactionBalance: " + userBalances.get(0).getTransaction());
                    break;
                default:
                    System.out.println("Bad type captured:");
                    System.out.println(GsonUtil.getGson().toJson(payment));
                    assert false;
            }
            if (checkReferenceId(paymentType)) {
                assert payment.getPaidAt() != null;
                assert payment.getReferenceId() != null;
            }
        }
        System.out.println("feePaymentExpected: " + feePaymentExpected);
        System.out.println("feeAmountPaid: " + feeAmountPaid);
        assert feeAmountPaid.compareTo(feePaymentExpected.setScale(2, RoundingMode.FLOOR)) == 0;
        System.out.println("consignmentExpected: " + consignmentPaymentExpected);
        System.out.println("consignmentPaid: " + consignmentAmountPaid);
        assert consignmentAmountPaid.compareTo(consignmentPaymentExpected.setScale(2, RoundingMode.FLOOR)) == 0;
        System.out.println("taxExpected: " + taxPaymentExpected);
        System.out.println("taxPaid: " + taxAmountPaid);
        assert taxAmountPaid.compareTo(taxPaymentExpected) == 0;
    }

    protected abstract String getTestDataPrefix();

    // True will cause fees to be part of the processing, false will make them marked processed
    protected abstract boolean supportFees();

    protected abstract GatewayConnection getConnectionForUser(String userId);

    protected abstract String getMerchantPayoutType();

    protected abstract String getCompanyPayoutType();
    
    protected abstract boolean shouldCreateMerchants();

    protected void overrideTeamSetting(Team team) {

    }
}