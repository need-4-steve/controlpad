package com.controlpad.payman_processor.transaction_processing;

import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.affiliate_charge.AffiliateChargeMapper;
import com.controlpad.payman_common.consignment.Consignment;
import com.controlpad.payman_common.consignment.ConsignmentMapper;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.fee.FeeMapper;
import com.controlpad.payman_common.fee.TeamFeeSet;
import com.controlpad.payman_common.fee.TeamFeeSetMap;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.merchant.Merchant;
import com.controlpad.payman_common.merchant.MerchantMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.team.PayoutMethod;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.controlpad.payman_processor.gateway.FeeEntry;
import com.controlpad.payman_processor.gateway.GatewayUtil;
import org.apache.commons.lang3.BooleanUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

class TransactionProcessHelper {

    private static final Logger logger = LoggerFactory.getLogger(TransactionProcessHelper.class);

    private GatewayUtil gatewayUtil;

    private Map<String, Team> teamMap;
    private Map<Long, Fee> feeMap;
    private TeamFeeSetMap teamFeeSetMap;
    private List<Transaction> transactions;

    private List<Entry> entryList;
    private Map<Long, UserBalances> balanceAdjustment;

    private TransactionMapper transactionMapper;
    private AffiliateChargeMapper affiliateChargeMapper;
    private ConsignmentMapper consignmentMapper;
    private GatewayConnectionMapper gatewayConnectionMapper;
    private UserBalancesMapper userBalancesMapper;
    private EntryMapper entryMapper;

    private boolean processed = false;

    private HashMap<String, Merchant> companyMerchants;

    TransactionProcessHelper(SqlSession sqlSession, GatewayUtil gatewayUtil) {
        this.gatewayUtil = gatewayUtil;
        entryList = new ArrayList<>();
        transactions = new ArrayList<>();
        balanceAdjustment = new HashMap<>();

        FeeMapper feeMapper = sqlSession.getMapper(FeeMapper.class);
        transactionMapper = sqlSession.getMapper(TransactionMapper.class);
        affiliateChargeMapper = sqlSession.getMapper(AffiliateChargeMapper.class);
        consignmentMapper = sqlSession.getMapper(ConsignmentMapper.class);
        gatewayConnectionMapper = sqlSession.getMapper(GatewayConnectionMapper.class);
        userBalancesMapper = sqlSession.getMapper(UserBalancesMapper.class);
        entryMapper = sqlSession.getMapper(EntryMapper.class);

        teamMap = sqlSession.getMapper(TeamMapper.class).mapTeams();
        feeMap = feeMapper.mapAllFees();
        teamFeeSetMap = feeMapper.mapTeamFeeSets();
        companyMerchants = sqlSession.getMapper(MerchantMapper.class).mapAllCompanyMerchants();
    }

    void process() {
        if (processed) {
            throw new RuntimeException("Tried to process transactions more than once");
        }
        for(Team team: teamMap.values()) {
            DateTime endDate = DateTime.now().minusHours(4); // Lets try and buffer in case there is an ongoing transaction update for a current batch
            // TODO probably remove the 4 hour buffer when we can figure out a way to make sure transactions have all updated before batches are labeled settled
            if (team.getPayoutSchedule() != null) {
                endDate = endDate.minusDays(team.getPayoutSchedule().getDaysBuffer());
                endDate = endDate.withHourOfDay(team.getPayoutSchedule().getBufferHourOfDay());
            }
            transactions.addAll(transactionMapper.listForProcessing(team.getId(), endDate.toString("YYYY-MM-dd HH:mm:ss")));
        }

        transactions.forEach(this::processTransaction);

        if (entryList.size() > 0) {
            entryMapper.insertList(entryList);
        }

        if (transactions.size() > 0) {
            transactionMapper.markProcessedForList(transactions);
        }

        for(UserBalances userBalances: balanceAdjustment.values()) {
            if (userBalances.getEWallet().compareTo(BigDecimal.ZERO) != 0) {
                userBalancesMapper.addEWallet(userBalances.getId(), userBalances.getEWallet());
            }
            if (userBalances.getTransaction().compareTo(BigDecimal.ZERO) != 0) {
                userBalancesMapper.addTransaction(userBalances.getId(), userBalances.getTransaction());
            }
        }
        processed = true;
    }

    private boolean processTransaction(Transaction transaction) {
        TransactionType transactionType = TransactionType.findBySlug(transaction.getTransactionType());
        switch (transactionType) {
            case CHECK_SALE:
            case CREDIT_CARD_SALE:
            case DEBIT_CARD_SALE:
                processSale(transaction);
                break;
            case CREDIT_CARD_SUB:
            case DEBIT_CARD_SUB:
            case CHECK_SUB:
                processSubscription(transaction);
                break;
            case REFUND:
                processRefund(transaction);
                break;
            case CARD_PAYMENT_TAX:
            case E_CHECK_PAYMENT_TAX:
                processTaxPayment(transaction);
                break;
            case ACH_PAYMENT_TAX:
                // Not implemented yet, not sure if legal
            default:
                logger.error("Unknown or unsupported transaction type for transaction: {}", transaction.getId());
                return false;
        }
        return true;
    }

    private void processSale(Transaction transaction) {
        GatewayConnection gatewayConnection = gatewayConnectionMapper.findById(transaction.getGatewayConnectionId());
        Long userBalanceId = getOrCreateUserBalance(
                transaction.getPayeeUserId(),
                transaction.getTeamId());

        Team team = teamMap.get(transaction.getTeamId());
        boolean shouldProcessFees = (PayoutMethod.findBySlug(team.getConfig().getCompanyPayoutMethod()) != PayoutMethod.NONE);
        boolean companyMerchant = companyMerchants.containsKey(transaction.getPayeeUserId());

        createMerchantEntry(transaction.getId(), transaction.getAmount(), userBalanceId, companyMerchant);

        cutSalesTaxCharge(transaction.getId(), transaction.getSalesTax().negate(), userBalanceId, team,
                shouldProcessFees, companyMerchant); // subtract sales tax

        cutConsignmentPayment(transaction, userBalanceId, shouldProcessFees, companyMerchant);

        cutInternalFees(gatewayConnection, transaction, userBalanceId);

        cutFeesPayments(transaction, userBalanceId, shouldProcessFees, companyMerchant);

        cutAffiliateCharges(transaction, userBalanceId, gatewayConnection.fundsCompany());
    }

    private void processTaxPayment(Transaction transaction) {
        GatewayConnection gatewayConnection = gatewayConnectionMapper.findById(transaction.getGatewayConnectionId());
        Long userBalanceId = getOrCreateUserBalance(
                transaction.getPayerUserId(),
                transaction.getTeamId());

        Team team = teamMap.get(transaction.getTeamId());
        boolean shouldProcessFees = (PayoutMethod.findBySlug(team.getConfig().getCompanyPayoutMethod()) != PayoutMethod.NONE);
        boolean companyMerchant = companyMerchants.containsKey(transaction.getPayeeUserId());

        userBalancesMapper.addSalesTax(userBalanceId, transaction.getAmount());

        cutFeesPayments(transaction, userBalanceId, shouldProcessFees, companyMerchant);

        cutInternalFees(gatewayConnection, transaction, userBalanceId);
    }

    private void processSubscription(Transaction transaction) {
        GatewayConnection gatewayConnection = gatewayConnectionMapper.findById(transaction.getGatewayConnectionId());
        Long userBalanceId = getOrCreateUserBalance(
                transaction.getPayeeUserId(),
                transaction.getTeamId());

        Team team = teamMap.get(transaction.getTeamId());
        boolean shouldProcessFees = (PayoutMethod.findBySlug(team.getConfig().getCompanyPayoutMethod()) != PayoutMethod.NONE);
        boolean companyMerchant = companyMerchants.containsKey(transaction.getPayeeUserId());

        createMerchantEntry(transaction.getId(), transaction.getAmount(), userBalanceId, companyMerchant);

        cutSalesTaxCharge(transaction.getId(), transaction.getSalesTax().negate(), userBalanceId, team,
                shouldProcessFees, companyMerchant); // subtract sales tax

        cutInternalFees(gatewayConnection, transaction, userBalanceId);

        cutFeesPayments(transaction, userBalanceId, shouldProcessFees, companyMerchant);

        cutAffiliateCharges(transaction, userBalanceId, gatewayConnection.fundsCompany());
    }

    private void processRefund(Transaction transaction) {
        GatewayConnection gatewayConnection = gatewayConnectionMapper.findById(transaction.getGatewayConnectionId());

        Long userBalanceId = getOrCreateUserBalance(
                transaction.getPayeeUserId(),
                transaction.getTeamId());

        Team team = teamMap.get(transaction.getTeamId());
        boolean shouldProcessFees = (PayoutMethod.findBySlug(team.getConfig().getCompanyPayoutMethod()) != PayoutMethod.NONE);
        boolean companyMerchant = companyMerchants.containsKey(transaction.getPayeeUserId());

        createMerchantEntry(transaction.getId(), transaction.getAmount().negate(), userBalanceId, companyMerchant);

        cutSalesTaxCharge(transaction.getId(), transaction.getSalesTax(), userBalanceId, team,
                shouldProcessFees, companyMerchant); // add sales tax

        cutAffiliateCharges(transaction, userBalanceId, gatewayConnection.fundsCompany());
    }

    private void cutSalesTaxCharge(String transactionId, BigDecimal salesTax, Long userBalanceId, Team team,
                                   boolean shouldProcess, boolean isCompanyMerchant) {
        if (salesTax != null && salesTax.compareTo(BigDecimal.ZERO) != 0 && team.getConfig().getCollectSalesTax()) {
            // Collecting sales tax means create a record
            entryList.add(new Entry(userBalanceId, salesTax, transactionId,
                    null, null, PaymentType.SALES_TAX.slug,
                    !shouldProcess)); // If not moving money, mark processed
            if (!isCompanyMerchant) {
                getBalanceAdjustment(userBalanceId).addEWallet(salesTax);
                if (!shouldProcess) {
                    getBalanceAdjustment(userBalanceId).addTransaction(salesTax);
                }
            } else if (shouldProcess) {
                // If a company merchant needs to process the tax, we will fake their transaction balance
                getBalanceAdjustment(userBalanceId).addTransaction(salesTax.negate());
            }
        }
    }

    private void cutFeesPayments(Transaction transaction, Long userBalanceId, boolean shouldProcess, boolean isCompanyMerchant) {
        if (teamFeeSetMap == null || !teamFeeSetMap.containsKey(transaction.getTeamId())) {
            return;
        }
        TeamFeeSet teamFeeSet;
        // Temporary workaround to allow pulling fees for card swipes until fee structure can be reworked
        if (transaction.getTransactionType().equals(TransactionType.CREDIT_CARD_SALE.slug) &&
                BooleanUtils.isTrue(transaction.getSwiped()) &&
                teamFeeSetMap.get(transaction.getTeamId()).containsKey(TransactionType.CARD_SWIPE_SALE.slug)) {
            teamFeeSet = teamFeeSetMap.get(transaction.getTeamId()).get(TransactionType.CARD_SWIPE_SALE.slug);
        } else {
            teamFeeSet = teamFeeSetMap.get(transaction.getTeamId()).get(transaction.getTransactionType());
        }
        if (teamFeeSet == null) {
            return;
        }

        Fee fee;
        BigDecimal feeAmount;
        for (Long feeId : teamFeeSet.getFeeIds()) {
            fee = feeMap.get(feeId);
            if (fee == null)
                continue;

            feeAmount = fee.calculateChargeAmount(transaction).negate();

            if (feeAmount.compareTo(BigDecimal.ZERO) != 0) {
                if (fee.getAccountId() != null && shouldProcess) {
                    entryList.add(new Entry(userBalanceId, feeAmount, transaction.getId(), feeId, null, PaymentType.FEE.slug, false));
                } else {
                    // If there isn't an account id, money will not be moving, go ahead and deduct transaction balance
                    entryList.add(new Entry(userBalanceId, feeAmount, transaction.getId(), feeId, null, PaymentType.FEE.slug, true));
                    if (!isCompanyMerchant) {
                        // add negative fee to transaction amount if not processing and not company merchant
                        getBalanceAdjustment(userBalanceId).addTransaction(feeAmount);
                    }
                }
                if (!isCompanyMerchant) {
                    // Non company merchants reduce e-wallet balance
                    getBalanceAdjustment(userBalanceId).addEWallet(feeAmount);
                } else if(fee.getAccountId() != null && shouldProcess) {
                    // Add money to allow processing on company merchant
                    getBalanceAdjustment(userBalanceId).addTransaction(feeAmount.negate());
                }
            }
        }
    }

    private void cutInternalFees(GatewayConnection gatewayConnection, Transaction transaction, Long userBalanceId) {
        List<FeeEntry> fees = gatewayUtil.getGatewayApi(gatewayConnection).getInternalFees(gatewayConnection, transaction, userBalanceId);
        UserBalances balanceAdjust = getBalanceAdjustment(userBalanceId);
        for (FeeEntry fee : fees) {
            balanceAdjust.addTransaction(fee.getAmount());
            balanceAdjust.addEWallet(fee.getAmount());
            fee.setFeeId(findFeeIdForReferenceId(fee.getReferenceId()));
            entryList.add(fee);
        }
    }

    /**
     *  Assumes a single account is used to manage payouts, ewallet, credits
     */
    private void cutConsignmentPayment(Transaction transaction, Long userBalanceId,
                                       boolean shouldProcess, boolean isCompanyMerchant) {
        Consignment consignment = consignmentMapper.findForUserId(transaction.getPayeeUserId());
        if (consignment != null && consignment.getBalance().compareTo(BigDecimal.ZERO) > 0) {
            BigDecimal consignmentChargeAmount = consignment.calculateChargeAmount(transaction.getSubTotal());

            if (consignmentChargeAmount.compareTo(consignment.getBalance()) > 0) {
                consignmentChargeAmount = consignment.getBalance();
            }

            // Update consignment balance up front to prevent over payment
            int updateCount = consignmentMapper.subtractBalance(consignment.getUserId(), consignmentChargeAmount);

            if (updateCount == 1) {
                entryList.add(new Entry(userBalanceId, consignmentChargeAmount.negate(), transaction.getId(),
                        null, null, PaymentType.CONSIGNMENT.slug, !shouldProcess));
                if (!isCompanyMerchant) {
                    getBalanceAdjustment(userBalanceId).addEWallet(consignmentChargeAmount.negate());
                    if (!shouldProcess) {
                        getBalanceAdjustment(userBalanceId).addTransaction(consignmentChargeAmount.negate());
                    }
                }
            }
        }
    }

    private void cutAffiliateCharges(Transaction transaction, Long payeeBalanceId, boolean fundsCompany) {
        List<AffiliateCharge> affiliateCharges = affiliateChargeMapper.listForTransactionId(transaction.getId());
        for(AffiliateCharge affiliateCharge: affiliateCharges) {
            if (affiliateCharge.getAmount().compareTo(BigDecimal.ZERO) != 0) {
                Long affiliateBalanceId = getOrCreateUserBalance(affiliateCharge.getPayeeUserId(), transaction.getTeamId());
                if (!companyMerchants.containsKey(transaction.getPayeeUserId())) {
                    // Company payee won't create entry
                    // Subtract affiliate charge from the payee
                    entryList.add(new Entry(payeeBalanceId, affiliateCharge.getAmount().negate(), transaction.getId(),
                            null, null, PaymentType.AFFILIATE.slug, fundsCompany));
                    if (fundsCompany) {
                        getBalanceAdjustment(payeeBalanceId).addTransaction(affiliateCharge.getAmount().negate());
                        getBalanceAdjustment(payeeBalanceId).addEWallet(affiliateCharge.getAmount().negate());
                    }
                }

                if (!companyMerchants.containsKey(affiliateCharge.getPayeeUserId())) {
                    // Company payee won't create entry
                    // Add the affiliate charge to the affiliate
                    entryList.add(new Entry(affiliateBalanceId, affiliateCharge.getAmount(), transaction.getId(),
                            null, null, PaymentType.AFFILIATE.slug, fundsCompany));
                    if (fundsCompany) {
                        getBalanceAdjustment(affiliateBalanceId).addTransaction(affiliateCharge.getAmount());
                        getBalanceAdjustment(affiliateBalanceId).addEWallet(affiliateCharge.getAmount());
                    }
                }

            }
        }
    }

    private UserBalances getBalanceAdjustment(Long id) {
        if (balanceAdjustment.containsKey(id)) {
            return balanceAdjustment.get(id);
        } else {
            UserBalances balance = new UserBalances(id, null, null, BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO);
            balanceAdjustment.put(id, balance);
            return balance;
        }
    }

    private Long getOrCreateUserBalance(String userId, String teamId) {
        UserBalances balance = userBalancesMapper.find(userId, teamId);
        if (balance == null) {
            balance = new UserBalances(userId, teamId, BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO);
            userBalancesMapper.insert(balance);
        }
        return balance.getId();
    }

    private void createMerchantEntry(String transactionId, BigDecimal amount, Long userBalanceId, boolean isCompanyMerchant) {
        // Add to transaction balance
        if (isCompanyMerchant) {
            // Don't use e-wallet for company
            return;
        }
        entryList.add(new Entry(userBalanceId, amount, transactionId, null,
                null, PaymentType.MERCHANT.slug, true));
        getBalanceAdjustment(userBalanceId).addTransaction(amount);
        getBalanceAdjustment(userBalanceId).addEWallet(amount);
    }

    private Long findFeeIdForReferenceId(String referenceId) {
        if (referenceId == null) {
            return null;
        }
        for (Map.Entry<Long, Fee> longFeeEntry : feeMap.entrySet()) {
            if (referenceId.equals(longFeeEntry.getValue().getReferenceId()))
                return longFeeEntry.getValue().getId();
        }
        return null;
    }
}