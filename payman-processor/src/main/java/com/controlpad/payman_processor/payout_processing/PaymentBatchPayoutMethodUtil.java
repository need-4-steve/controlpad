package com.controlpad.payman_processor.payout_processing;

import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.fee.FeeMapper;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.payment.PaymentMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.payment_batch.PaymentBatch;
import com.controlpad.payman_common.payment_batch.PaymentBatchMapper;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import com.controlpad.payman_common.transaction_charge.TransactionChargeMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.controlpad.payman_processor.util.IDUtil;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.IOException;
import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class PaymentBatchPayoutMethodUtil extends PaymentMethodUtilBase {

    private static final Logger logger = LoggerFactory.getLogger(PaymentBatchPayoutMethodUtil.class);

    private List<Long> cashChargeIds;
    private Payment consignmentPayment = null;
    private Map<Long, Fee> feeMap;
    private Map<Long, Payment> feePayments = null;
    private Map<Long, UserBalances> userBalanceUpdateMap; // To be added to user balances, expected to be negative values
    private Map<String, Payment> withdrawPaymentsMap;
    private Payment taxPayment = null;
    private Map<Payment, List<Long>> paymentEntryIdMap; // Entry ids per payment for updating paymentId
    private PaymentBatch paymentBatch;

    private boolean paymentsCreated = false;
    private boolean isComplete = false;


    public PaymentBatchPayoutMethodUtil(SqlSession clientSession, IDUtil idUtil, ControlPadClient client, Team team) {
        super(clientSession, idUtil, client, team);
        this.cashChargeIds = new ArrayList<>();
        this.feePayments = new HashMap<>();
        this.paymentEntryIdMap = new HashMap<>();
        this.userBalanceUpdateMap = new HashMap<>();
        this.withdrawPaymentsMap = new HashMap<>();

        this.paymentBatch = new PaymentBatch(idUtil.generateId(), null, team.getId(), "open", BigDecimal.ZERO, 0);

        feeMap = clientSession.getMapper(FeeMapper.class).mapAllFees();
    }

    @Override
    void processWithdraws(UserBalances userBalance, List<Entry> withdraws) {
        if (withdraws == null || withdraws.isEmpty()) {
            return;
        }
        Payment userWithdrawPayment = withdrawPaymentsMap.computeIfAbsent(userBalance.getUserId(),
                k -> new Payment(getIdUtil().generateId(), getTeam().getId(), k, null, BigDecimal.ZERO,
                        paymentBatch.getId(), PaymentType.WITHDRAW.slug)
        );
        UserBalances updateBalance = findUserBalanceUpdate(userBalance.getId());
        batchEntriesToPayment(userWithdrawPayment, withdraws, updateBalance);
    }

    @Override
    void processTaxes(List<Entry> taxes, UserBalances userBalance) {
        if (taxes == null || taxes.isEmpty()) {
            return;
        }
        if (taxPayment == null) {
            taxPayment = new Payment(getIdUtil().generateId(), getTeam().getId(), null, getTeam().getTaxAccountId(),
                    BigDecimal.ZERO, paymentBatch.getId(), PaymentType.SALES_TAX.slug);
        }
        UserBalances updateBalance = findUserBalanceUpdate(userBalance.getId());
        batchEntriesToPayment(taxPayment, taxes, updateBalance);
    }

    @Override
    void processTaxCharges(List<TransactionCharge> taxCharges, UserBalances userBalance) {
        if (taxCharges == null || taxCharges.isEmpty()) {
            return;
        }
        if (taxPayment == null) {
            taxPayment = new Payment(getIdUtil().generateId(), getTeam().getId(), null, getTeam().getTaxAccountId(),
                    BigDecimal.ZERO, paymentBatch.getId(), PaymentType.SALES_TAX.slug);
        }
        UserBalances updateBalance = findUserBalanceUpdate(userBalance.getId());
        for (TransactionCharge charge : taxCharges) {
            taxPayment.addAmount(charge.getAmount());
            // Add withdraw to payment/entry map for updating payment id later
            cashChargeIds.add(charge.getId());
            updateBalance.addSalesTax(charge.getAmount().negate());
        }
    }

    @Override
    void processConsignment(List<Entry> consignments, UserBalances userBalance) {
        if (consignments == null || consignments.isEmpty()) {
            return;
        }
        if (consignmentPayment == null) {
            consignmentPayment = new Payment(getIdUtil().generateId(), getTeam().getId(), null, getTeam().getConsignmentAccountId(),
                    BigDecimal.ZERO, paymentBatch.getId(), PaymentType.CONSIGNMENT.slug);
        }
        UserBalances updateBalance = findUserBalanceUpdate(userBalance.getId());
        batchEntriesToPayment(consignmentPayment, consignments, updateBalance);
    }

    @Override
    void processFees(Long feeId, List<Entry> fees, UserBalances userBalance) {
        if (fees == null || fees.isEmpty()) {
            return;
        }
        if (!feeMap.containsKey(feeId)) {
            logger.error("Balance({}) feeId didn't exist in feeMap. Client({})", userBalance.getId(), getClient().getId());
            return;
        }
        Long accountId = feeMap.get(feeId).getAccountId();
        if (accountId == null) {
            logger.error("Balance({}) fee didn't have an accountId. Client({})", userBalance.getId(), getClient().getId());
            return;
        }
        Payment feePayment = feePayments.computeIfAbsent(feeId,
                k -> new Payment(getIdUtil().generateId(), getTeam().getId(), null, accountId,
                        BigDecimal.ZERO, paymentBatch.getId(), PaymentType.FEE.slug));
        UserBalances updateBalance = findUserBalanceUpdate(userBalance.getId());
        batchEntriesToPayment(feePayment, fees, updateBalance);
    }

    @Override
    boolean isPaymentsCreated() {
        return paymentsCreated;
    }

    @Override
    public void close() throws IOException {

    }

    @Override
    void onComplete() throws Exception {
        if (isComplete || paymentsCreated) {
            // This can call multiple times, filtering additional calls
            return;
        }
        EntryMapper entryMapper = getClientSession().getMapper(EntryMapper.class);
        PaymentMapper paymentMapper = getClientSession().getMapper(PaymentMapper.class);
        PaymentBatchMapper paymentBatchMapper = getClientSession().getMapper(PaymentBatchMapper.class);
        TransactionChargeMapper transactionChargeMapper = getClientSession().getMapper(TransactionChargeMapper.class);
        UserBalancesMapper userBalancesMapper = getClientSession().getMapper(UserBalancesMapper.class);

        List<Payment> payments = new ArrayList<>();

        // Update database
        if (!feePayments.isEmpty()) {
            feePayments.forEach((aLong, payment) -> paymentBatch.setNetAmount(paymentBatch.getNetAmount().add(payment.getAmount())));
            payments.addAll(feePayments.values());
        }

        if (consignmentPayment != null && consignmentPayment.getAmount().compareTo(BigDecimal.valueOf(0.01)) >= 0) {
            paymentBatch.setNetAmount(paymentBatch.getNetAmount().add(consignmentPayment.getAmount()));
            payments.add(consignmentPayment);
        }
        if (taxPayment != null && taxPayment.getAmount().compareTo(BigDecimal.valueOf(0.01)) >= 0) {
            paymentBatch.setNetAmount(paymentBatch.getNetAmount().add(taxPayment.getAmount()));
            payments.add(taxPayment);
        }

        if (!withdrawPaymentsMap.isEmpty()) {
            withdrawPaymentsMap.forEach((s, payment) -> paymentBatch.setNetAmount(paymentBatch.getNetAmount().add(payment.getAmount())));
            payments.addAll(withdrawPaymentsMap.values());
        }

        if (payments.size() > 0) {
            paymentBatch.setPaymentCount(payments.size());
            paymentBatchMapper.insert(paymentBatch);
            paymentMapper.insertList(payments);
            paymentsCreated = true;
        } else {
            paymentsCreated = false;
            return;
        }

        // Update entries.payment_id
        paymentEntryIdMap.forEach((payment, longs) -> entryMapper.setProcessedAndBatchIdForList(longs, payment.getId()));

        if (!cashChargeIds.isEmpty()) {
            transactionChargeMapper.setPaidForList(taxPayment.getId(), cashChargeIds);
        }

        // Update balances
        if (!userBalanceUpdateMap.isEmpty()) {
            for (UserBalances userBalances : userBalanceUpdateMap.values()) {
                userBalancesMapper.add(userBalances.getId(), userBalances.getSalesTax(), userBalances.getEWallet(), userBalances.getTransaction());
            }
        }

        isComplete = true;
    }

    private UserBalances findUserBalanceUpdate(Long id) {
        if (!userBalanceUpdateMap.containsKey(id)) {
            UserBalances userBalances = new UserBalances(id, null, null, BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO);
            userBalanceUpdateMap.put(id, userBalances);
            return userBalances;
        } else {
            return userBalanceUpdateMap.get(id);
        }
    }

    private void batchEntriesToPayment(Payment payment, List<Entry> entries, UserBalances updateBalance) {
        for (Entry entry : entries) {
            payment.addAmount(entry.getAmount());
            // Add withdraw to payment/entry map for updating payment id later
            paymentEntryIdMap.computeIfAbsent(payment, k -> new ArrayList<>()).add(entry.getId());
            updateBalance.addTransaction(entry.getAmount().negate());
        }
    }
}
