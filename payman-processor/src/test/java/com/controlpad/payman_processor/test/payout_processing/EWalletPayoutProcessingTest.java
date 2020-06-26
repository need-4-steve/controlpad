package com.controlpad.payman_processor.test.payout_processing;

import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
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
import com.controlpad.payman_processor.CronTest;
import com.controlpad.payman_processor.client.ClientConfigUtil;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import com.controlpad.payman_processor.gateway.GatewayUtil;
import com.controlpad.payman_common.payout_job.PayoutJob;
import com.controlpad.payman_common.payout_job.PayoutJobMapper;
import com.controlpad.payman_processor.payout_processing.PayoutProcessingTask;
import com.controlpad.payman_processor.util.IDUtil;
import org.joda.time.DateTime;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;

import java.math.BigDecimal;
import java.util.List;

public class EWalletPayoutProcessingTest extends CronTest {

    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    ClientConfigUtil clientConfigUtil;
    @Autowired
    GatewayUtil gatewayUtil;
    @Autowired
    IDUtil idUtil;

    private Team team;
    private PayoutJob payoutJob;

    private String userOverLimitId = "ewallet-payout-user-limit";
    private String userNoEwalletId = "ewallet-payout-user-none";
    private String teamId = "ewallet-withdraw-team";
    private BigDecimal overLimitAmount = new Money(200D);
    private Entry eWalletWithdrawEntry;
    private Entry filteredTaxEntry;
    private Entry filteredFeeEntry;
    private UserBalances overLimitUserBalance;
    private UserBalances noEwalletUserBalance;

    private EntryMapper entryMapper;
    private PaymentMapper paymentMapper;
    private TransactionMapper transactionMapper;
    private UserBalancesMapper userBalancesMapper;

    @Test
    public void eWalletPayoutProcessingTest() {
        loadData();

        new PayoutProcessingTask(
                sqlSessionUtil, clientConfigUtil, gatewayUtil, idUtil,
                getTestUtil().getMockData().getTestClient().getId(),
                payoutJob.getId()
        ).run();

        Entry withdrawResult = entryMapper.findById(eWalletWithdrawEntry.getId());
        assert withdrawResult.getProcessed();
        assert withdrawResult.getPaymentId() != null;
        UserBalances withdrawBalanceResult = userBalancesMapper.findForId(overLimitUserBalance.getId());
        assert withdrawBalanceResult.getEWallet().compareTo(BigDecimal.ZERO) == 0;
        assert withdrawBalanceResult.getTransaction().compareTo(new Money(50D)) == 0;

        Payment withdrawPayment = paymentMapper.findPaymentById(withdrawResult.getPaymentId());
        assert withdrawPayment != null;
        // payments are batched together, so overlimit entry is in the payout
        assert withdrawPayment.getAmount().compareTo(eWalletWithdrawEntry.getAmount().negate().add(overLimitAmount)) == 0;
        assert withdrawPayment.getType().equals(PaymentType.WITHDRAW.slug);
        assert withdrawPayment.getUserId().equals(userOverLimitId);
        assert withdrawPayment.getPaymentFileId() != null;
        assert withdrawPayment.getTeamId().equals(teamId);

        List<Transaction> userTransactions = transactionMapper.listSuccessfulForUser(userOverLimitId);
        assert userTransactions.size() == 2;
        Transaction overLimitTransaction = userTransactions.get(1);
        assert overLimitTransaction.getAmount().compareTo(overLimitAmount) == 0;
        assert overLimitTransaction.getProcessed();
        assert overLimitTransaction.getTransactionType().equals(TransactionType.E_WALLET_WITHDRAW.slug);
        assert overLimitTransaction.getStatusCode().equals("S");
        assert overLimitTransaction.getTeamId().equals(teamId);

        List<Entry> overLimitEntries = entryMapper.listByTransactionId(overLimitTransaction.getId());
        assert overLimitEntries.size() == 1;
        Entry overLimitEntry = overLimitEntries.get(0);
        assert overLimitEntry.getBalanceId().equals(overLimitUserBalance.getId());
        assert overLimitEntry.getPaymentId() != null;
        assert overLimitEntry.getProcessed();
        assert overLimitEntry.getAmount().compareTo(overLimitAmount.negate()) == 0;
        assert overLimitEntry.getType().equals(PaymentType.WITHDRAW.slug);

        List<Payment> payments = paymentMapper.search(null, userOverLimitId, null, null, null, null,
                null, null, null, 0L, 10);
        assert payments.size() == 1;
        assert !entryMapper.findById(filteredTaxEntry.getId()).getProcessed();
        assert !entryMapper.findById(filteredFeeEntry.getId()).getProcessed();

        // TODO check payment file/stats

        // Check non processed user
        UserBalances noEwalletBalanceResult = userBalancesMapper.findForId(noEwalletUserBalance.getId());
        assert noEwalletBalanceResult.getTransaction().compareTo(new Money(50D)) == 0;
        assert noEwalletBalanceResult.getEWallet().compareTo(new Money(50D)) == 0;

        List<Payment> noEwalletPayments = paymentMapper.search(null, userNoEwalletId, null, null, null, null,
                null, null, null, 0L, 10);
        assert noEwalletPayments.size() == 0;
    }

    private void loadData() {
        team = new Team(teamId, "E wallet withdraw team",
                new TeamConfig(true, true, true, true,
                        false, new Money(100D), PayoutScheme.AUTO_SCHEDULE_DAILY_WITHDRAW.getSlug()));
        team.getConfig().setCompanyPayoutMethod(PayoutMethod.FILE.getSlug());
        team.getConfig().setMerchantPayoutMethod(PayoutMethod.FILE.getSlug());
        getClientSqlSession().getMapper(TeamMapper.class).insert(team);

        payoutJob = new PayoutJob(DateTime.now().toString("yyyy-MM-dd HH:mm:ss"),
                "queued", team.getId(), PayoutScheme.AUTO_SCHEDULE_DAILY_WITHDRAW.getSlug());
        getClientSqlSession().getMapper(PayoutJobMapper.class).insert(payoutJob);

        getClientSqlSession().getMapper(UserAccountMapper.class).insert(
                new UserAccount(userOverLimitId, "Ewallet processing over limit user", "324377516",
                        "123456789", "checking", null, true));
        getClientSqlSession().getMapper(UserAccountMapper.class).insert(
                new UserAccount(userNoEwalletId, "No Ewallet Processing User", "324377516",
                        "123456789", "checking", null, true));

        // Going to have a withdraw of 300 and a balance of 200 remaining which is over limit of 100
        userBalancesMapper = getClientSqlSession().getMapper(UserBalancesMapper.class);
        overLimitUserBalance = new UserBalances(userOverLimitId, team.getId());
        userBalancesMapper.insert(overLimitUserBalance);
        userBalancesMapper.add(overLimitUserBalance.getId(), new Money(10D), overLimitAmount, new Money(550D));

        entryMapper = getClientSqlSession().getMapper(EntryMapper.class);
        eWalletWithdrawEntry = new Entry(overLimitUserBalance.getId(), new Money(-300D), null, null, null,
                PaymentType.WITHDRAW.slug, false);
        entryMapper.insert(eWalletWithdrawEntry);

        filteredTaxEntry = new Entry(overLimitUserBalance.getId(), new Money(-5D), null, null,
                null, PaymentType.SALES_TAX.slug, false);
        entryMapper.insert(filteredTaxEntry);
        filteredFeeEntry = new Entry(overLimitUserBalance.getId(), new Money(-10D), null, null,
                null, PaymentType.FEE.slug, false);
        entryMapper.insert(filteredFeeEntry);

        // Insert a couple stray cash tax charge
        transactionMapper = getClientSqlSession().getMapper(TransactionMapper.class);
        String transactionId = "ewalletcashtax";
        transactionMapper.insert(
                new Transaction(transactionId, userOverLimitId, teamId, TransactionType.CASH_SALE.slug, new Money(30D),
                        BigDecimal.ZERO, "S", 1, null, null)
        );
        transactionMapper.markProcessed(transactionId);
        getClientSqlSession().getMapper(TransactionChargeMapper.class).insert(
                new TransactionCharge(userOverLimitId, transactionId, null,
                        new Money(10D), PaymentType.SALES_TAX.slug)
        );
        getClientSqlSession().getMapper(TransactionChargeMapper.class).insert(
                new TransactionCharge(userOverLimitId, transactionId, null,
                        new Money(20D), PaymentType.SALES_TAX.slug)
        );

        // User that should not create any payments while having a balance and no entries to test auto pay is skipped
        noEwalletUserBalance = new UserBalances(userNoEwalletId, teamId);
        userBalancesMapper.insert(noEwalletUserBalance);
        userBalancesMapper.add(noEwalletUserBalance.getId(), BigDecimal.ZERO, new Money(50D), new Money(50D));

        paymentMapper = getClientSqlSession().getMapper(PaymentMapper.class);
        getClientSqlSession().commit();
    }
}