package com.controlpad.payman_processor.payout_processing;

import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.merchant.Merchant;
import com.controlpad.payman_common.merchant.MerchantMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.team.PayoutMethod;
import com.controlpad.payman_common.team.PayoutScheme;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import com.controlpad.payman_common.transaction_charge.TransactionChargeMapper;
import com.controlpad.payman_common.user_account.UserAccount;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.controlpad.payman_processor.client.ClientConfigUtil;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import com.controlpad.payman_processor.gateway.GatewayUtil;
import com.controlpad.payman_common.payout_job.PayoutJob;
import com.controlpad.payman_common.payout_job.PayoutJobMapper;
import com.controlpad.payman_processor.util.IDUtil;
import com.controlpad.payman_processor.util.TransactionUtil;
import com.paypal.api.payments.PayoutBatch;
import org.apache.commons.lang3.BooleanUtils;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;

import java.io.IOException;
import java.math.BigDecimal;
import java.math.RoundingMode;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class PayoutProcessingTask implements Runnable {

    private static final Logger logger = LoggerFactory.getLogger(PayoutProcessingTask.class);
    private DateTimeFormatter sqlDateFormat = DateTimeFormat.forPattern("YYYY-MM-dd HH:mm:ss.S");

    private SqlSessionUtil sqlSessionUtil;
    private ClientConfigUtil clientConfigUtil;
    private GatewayUtil gatewayUtil;
    private IDUtil idUtil;
    private String clientId;
    private ControlPadClient client;
    private Long payoutJobId;

    private EntryMapper entryMapper;
    private PayoutJobMapper payoutJobMapper;
    private TransactionMapper transactionMapper;
    private TransactionChargeMapper transactionChargeMapper;
    private UserBalancesMapper userBalancesMapper;

    private Team team;
    private PayoutJob payoutJob;

    private Map<String, UserAccount> userAccountMap;
    private Map<String, Merchant> merchants;

    private boolean autoPayCashTax = false;
    private PayoutScheme payoutType;
    private PayoutMethod merchantPayoutMethod;

    private BigDecimal cashTaxMin = new BigDecimal("0.01");

    public PayoutProcessingTask(SqlSessionUtil sqlSessionUtil, ClientConfigUtil clientConfigUtil, GatewayUtil gatewayUtil,
                                IDUtil idUtil, String clientId, Long payoutJobId) {
        this.sqlSessionUtil = sqlSessionUtil;
        this.clientConfigUtil = clientConfigUtil;
        this.gatewayUtil = gatewayUtil;
        this.idUtil = idUtil;
        this.clientId = clientId;
        this.payoutJobId = payoutJobId;
    }

    @Override
    public void run() {
        PaymentMethodUtilBase merchantPaymentUtil = null;
        PaymentMethodUtilBase companyPaymentUtil = null;
        MDC.put("clientId", clientId);
        try(SqlSession clientSession = sqlSessionUtil.openSession(clientId, false)) {
            logger.info("Processing payouts for client: {}\r\n\tPayoutJobId: {}", clientId, payoutJobId);

            // Set up initial data and mappers
            if (!init(clientSession)) {
                return;
            }

            // Create payments
            // Currently only supporting one balance per user&team
            List<String> userIds = userBalancesMapper.listUsersForTeamWithTransactionBalance(team.getId());

            // Check if not the same day for daily withdraw, we will skip company payouts
            boolean withdrawsOnly = payoutType == PayoutScheme.AUTO_SCHEDULE_DAILY_WITHDRAW &&
                    (team.getPayoutSchedule() == null ||
                    !team.getPayoutSchedule().isSameDay(DateTime.parse(payoutJob.getStartAt(), sqlDateFormat)));

            if (!withdrawsOnly) {
                // !!! The following functions will perform commits to the session

                // Check if cash tax should be auto paid
                if (autoPayCashTax) {
                    for (String userId : userIds) {
                        createCashTaxEWalletTransaction(clientSession, userBalancesMapper.find(userId, team.getId()));
                    }
                }

                // check settings for an automatic payment
                if (BooleanUtils.isTrue(team.getConfig().getAutoMerchantPayout())) {

                    UserBalances userBalances;

                    for (String userId : userIds) {
                        userBalances = userBalancesMapper.find(userId, team.getId());
                        if (!userBalances.getUserId().equals("company") && !userBalances.getUserId().equals("1")) {
                            createAutoWithdraws(clientSession, userBalances);
                        }
                    }

                }
            }

            // Check e_wallet limits and dump money if reached
            if ((withdrawsOnly || !BooleanUtils.isTrue(team.getConfig().getAutoMerchantPayout())) &&
                    team.getConfig().geteWalletLimit().compareTo(BigDecimal.ZERO) > 0) {
                UserBalances userBalance;
                for (String userId : userIds) {
                    userBalance = userBalancesMapper.find(userId, team.getId());
                    if (userBalance.getEWallet().compareTo(team.getConfig().geteWalletLimit()) >= 0) {
                        createAutoWithdraws(clientSession, userBalance);
                    }
                }
            }

            // Get utilities for creating payments, can be the same reference when using the same type
            merchantPaymentUtil = getPaymentUtil(clientSession, team.getConfig().getMerchantPayoutMethod());
            if (StringUtils.equals(team.getConfig().getMerchantPayoutMethod(), team.getConfig().getCompanyPayoutMethod()) &&
                    merchantPayoutMethod != PayoutMethod.PAYMENT_BATCH && merchantPayoutMethod != PayoutMethod.PAYMENT_BATCH_MANUAL) {
                companyPaymentUtil = merchantPaymentUtil;
            } else {
                companyPaymentUtil = getPaymentUtil(clientSession, team.getConfig().getCompanyPayoutMethod());
            }

            for (String userId : userIds) {
                entryMapper.resetEntryTotal();
                processEntries(userBalancesMapper.find(userId, team.getId()), withdrawsOnly, merchantPaymentUtil, companyPaymentUtil);
            }

            if (merchantPaymentUtil != null) {
                merchantPaymentUtil.onComplete();
            }
            if (companyPaymentUtil != null) {
                companyPaymentUtil.onComplete();
            }

            if ((companyPaymentUtil == null || !companyPaymentUtil.isPaymentsCreated()) &&
                    (merchantPaymentUtil == null || !merchantPaymentUtil.isPaymentsCreated())) {
                // No payments created, we don't want to save anything
                clientSession.rollback();
                clientSession.close();
                // mark job as skipped
                try (SqlSession quickSession = sqlSessionUtil.openSession(clientId, true)) {
                    quickSession.getMapper(PayoutJobMapper.class).markSkipped(payoutJobId);
                } catch (Exception e) {
                    logger.error("Failed to mark job skipped: {} {}", clientId, payoutJobId);
                }
                return;
            }

            if (payoutJobMapper.markProcessed(payoutJobId) == 0) {
                logger.error("PayoutProcessingTask ran while job was in wrong state for client: " + clientId + " | job: " + payoutJob.getId());
                return;
            }

            clientSession.commit();
        } catch (Exception e) {
            logger.error(String.format("Failed to payout for client: %s", clientId), e);
            if (merchantPaymentUtil != null) {
                try {
                    merchantPaymentUtil.close();
                } catch (IOException ioe) {
                    logger.error(String.format("Failed to payout for client: %s", clientId), ioe);
                }
            }
            if (companyPaymentUtil != null) {
                try {
                    companyPaymentUtil.close();
                } catch (IOException ioe) {
                    logger.error(String.format("Failed to payout for client: %s", clientId), ioe);
                }
            }
        }
    }

    private boolean init(SqlSession clientSession) {
        payoutJobMapper = clientSession.getMapper(PayoutJobMapper.class);
        payoutJob = payoutJobMapper.findById(payoutJobId);
        if (payoutJobMapper.markProcessing(payoutJobId) == 0) {
            logger.error("Team: " + payoutJob.getTeamId() + " Payout Job ID: " + payoutJobId +" status is wrong.");
            return false;
        }
        payoutType = PayoutScheme.findBySlug(payoutJob.getPayoutScheme());
        clientSession.commit();

        team = clientSession.getMapper(TeamMapper.class).findById(payoutJob.getTeamId());
        if (team == null) {
            logger.error("Team: " + payoutJob.getTeamId() + " was null for payout processing");
            return false;
        }
        merchantPayoutMethod = PayoutMethod.findBySlug(team.getConfig().getMerchantPayoutMethod());

        entryMapper = clientSession.getMapper(EntryMapper.class);
        transactionMapper = clientSession.getMapper(TransactionMapper.class);
        transactionChargeMapper = clientSession.getMapper(TransactionChargeMapper.class);
        userBalancesMapper = clientSession.getMapper(UserBalancesMapper.class);
        if (clientConfigUtil.getClientMap().containsKey(clientId)) {
            client = clientConfigUtil.getClientMap().get(clientId);
        }

        if (BooleanUtils.isTrue(team.getConfig().getAutoDeductOwedTax())) {
            autoPayCashTax = true;
        }

        merchants = clientSession.getMapper(MerchantMapper.class).mapAll();
        userAccountMap = clientSession.getMapper(UserAccountMapper.class).mapUserAccounts();

        return true;
    }

    private void processEntries(UserBalances balance, boolean withdrawsOnly, PaymentMethodUtilBase merchantPaymentUtil,
                             PaymentMethodUtilBase companyPaymentUtil) {
        // Get entries that aren't paid, filter by e wallet withdraw if that is the payout type
        List<Entry> entries = entryMapper.searchUnpaidEntries(
                balance.getId(),
                null,
                (withdrawsOnly ? PaymentType.WITHDRAW.id : null),
                balance.getTransaction()
        );

        List<Entry> taxEntries = new ArrayList<>();
        List<Entry> consignmentEntries = new ArrayList<>();
        List<Entry> withdrawEntries = new ArrayList<>();
        Map<Long, List<Entry>> feeEntriesMap = new HashMap<>();
        List<Entry> currentFeeEntryList;

        for (Entry entry : entries) {
            switch (PaymentType.findForSlug(entry.getType())) {
                case CONSIGNMENT:
                    consignmentEntries.add(entry);
                    break;
                case FEE:
                    if (entry.getFeeId() == null) {
                        logger.error("Entry({}) didn't have a feeId. Client({})", entry.getId(), clientId);
                        continue;
                    }

                    currentFeeEntryList = feeEntriesMap.computeIfAbsent(entry.getFeeId(), k -> new ArrayList<>());
                    currentFeeEntryList.add(entry);
                    break;
                case SALES_TAX:
                    taxEntries.add(entry);
                    break;
                case WITHDRAW:
                    withdrawEntries.add(entry);
                    break;
                default:
                case UNKNOWN:
                    MDC.put("entryId", String.valueOf(entry.getId()));
                    MDC.put("entryType", String.valueOf(entry.getType()));
                    logger.error("Entry type unexpected.");
                    MDC.remove("entryId");
                    MDC.remove("entryType");
                    continue;
            }
        }


        if (!withdrawsOnly &&
                balance.getSalesTax().compareTo(BigDecimal.ZERO) > 0) {
            // If we have sales tax money to play with try and pay cash sales
            transactionChargeMapper.resetTransactionChargeTotal();
            List<TransactionCharge> cashTaxCharges =
                    transactionChargeMapper.findUnpaidTaxChargesForUserAndTotal(balance.getUserId(), balance.getSalesTax());

            if (companyPaymentUtil != null) {
                companyPaymentUtil.processTaxCharges(cashTaxCharges, balance);
            }
        }

        if (companyPaymentUtil != null) {
            companyPaymentUtil.processTaxes(taxEntries, balance);
            companyPaymentUtil.processConsignment(consignmentEntries, balance);
            for (Map.Entry<Long, List<Entry>> longListEntry : feeEntriesMap.entrySet()) {
                companyPaymentUtil.processFees(longListEntry.getKey(), longListEntry.getValue(), balance);
            }
        }
        if (merchantPaymentUtil != null) {
            merchantPaymentUtil.processWithdraws(balance, withdrawEntries);
        }
    }

    // Creates a transaction, processes and calls commit
    private void createCashTaxEWalletTransaction(SqlSession clientSession, UserBalances balance) {
        if (balance.getEWallet().compareTo(cashTaxMin) < 0) {
            return; // Not enough money to pay cash tax
        }
        // Calculate tax owed from cash sales
        BigDecimal cashTaxOwed = transactionChargeMapper.sumUnpaidTaxChargesForUser(balance.getUserId());
        if (cashTaxOwed == null) {
            cashTaxOwed = BigDecimal.ZERO;
        }

        // Subtract total sales tax balances across all teams, because card payments go into company team
        BigDecimal salesTaxBalance = userBalancesMapper.salesTaxBalanceTotal(balance.getUserId());
        if (salesTaxBalance != null) {
            cashTaxOwed = cashTaxOwed.subtract(salesTaxBalance);
        }

        BigDecimal pendingTaxPayments = transactionMapper.getPendingTaxPaymentsForUserId(balance.getUserId());
        if (pendingTaxPayments != null) {
            cashTaxOwed = cashTaxOwed.subtract(pendingTaxPayments);
        }

        if (cashTaxOwed.compareTo(BigDecimal.ZERO) <= 0) {
            return; // No taxes owed
        }

        BigDecimal amountToPay = (cashTaxOwed.compareTo(balance.getEWallet()) > 0 ? balance.getEWallet() : cashTaxOwed);
        // Clip to nearest penny
        amountToPay = amountToPay.setScale(2, RoundingMode.FLOOR);

        if (userBalancesMapper.subtractEWalletSafe(balance.getId(), amountToPay) > 0) {
            Transaction transaction = new Transaction(null, balance.getUserId(), balance.getUserId(), balance.getTeamId(),
                    null, TransactionType.E_WALLET_PAYMENT_TAX.slug, amountToPay, BigDecimal.ZERO, BigDecimal.ZERO,
                    "S", 1, null, "Auto cash tax payment", null);
            if (!TransactionUtil.insertTransaction(transactionMapper, transaction, idUtil, null, null)) {
                return;
            }

            // Create entry
            entryMapper.insert(new Entry(balance.getId(), transaction.getAmount().negate(), transaction.getId(), null,
                    null, PaymentType.SALES_TAX.slug, true));

            // Add to sales tax balance
            // Balance already deducted for ewallet, so just update transaction balance
            userBalancesMapper.add(balance.getId(), transaction.getAmount(), BigDecimal.ZERO, transaction.getAmount().negate());

            transactionMapper.markProcessed(transaction.getId());

            clientSession.commit();
        }
    }

    private void createAutoWithdraws(SqlSession clientSession, UserBalances balance) {
        // Check that user account is valid before creating a withdraw
        if (!isUserAccountValid(balance.getUserId())) {
            return;
        }
        BigDecimal eWalletMoney = balance.getEWallet().setScale(2, RoundingMode.FLOOR);
        if (eWalletMoney.compareTo(BigDecimal.ZERO) > 0 &&
                userBalancesMapper.subtractEWalletSafe(balance.getId(), eWalletMoney) > 0) {

            Transaction transaction = new Transaction(null, balance.getUserId(), balance.getUserId(), balance.getTeamId(),
                    null, TransactionType.E_WALLET_WITHDRAW.slug, eWalletMoney, BigDecimal.ZERO, BigDecimal.ZERO,
                    "S", 1, null, "Auto withdraw", null);
            if (!TransactionUtil.insertTransaction(transactionMapper, transaction, idUtil, null, null)) {
                return;
            }

            // Create entry
            entryMapper.insert(new Entry(balance.getId(), transaction.getAmount().negate(), transaction.getId(), null,
                    null, PaymentType.WITHDRAW.slug, false));

            transactionMapper.markProcessed(transaction.getId());

            clientSession.commit();
        }
    }

    private PaymentMethodUtilBase getPaymentUtil(SqlSession clientSession, String type) throws Exception {
        switch (PayoutMethod.findBySlug(type)) {
            case FILE:
                return new FilePayoutMethodUtil(clientSession, idUtil, client, payoutJob, team, userAccountMap);
            case SUB_ACCOUNT:
                return new SubAccountPayoutMethodUtil(clientSession, idUtil, gatewayUtil, client, team, userAccountMap);
            case PAYMENT_PROVIDER:
                return new PaymentProviderPayoutMethodUtil(clientSession, idUtil, client, team);
            case PAYMENT_BATCH:
            case PAYMENT_BATCH_MANUAL:
                return new PaymentBatchPayoutMethodUtil(clientSession, idUtil, client, team);
            case NONE:
                return null; // No payouts
            default:
                throw new RuntimeException("PayoutMethod type invalid: " + type);
        }
    }

    private boolean isUserAccountValid(String userId) {
        if (merchantPayoutMethod == PayoutMethod.PAYMENT_BATCH_MANUAL) {
            return true;
        } else if (merchantPayoutMethod == PayoutMethod.PAYMENT_BATCH){
            return merchants.containsKey(userId) && merchants.get(userId).getEmail() != null;
        }else if (BooleanUtils.isTrue(client.getConfig().getFeatures().getAccountValidation())) {
            return userAccountMap.containsKey(userId) && userAccountMap.get(userId).getValidated();
        } else {
            return true;
        }
    }
}