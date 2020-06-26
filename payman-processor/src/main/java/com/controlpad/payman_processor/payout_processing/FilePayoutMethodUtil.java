package com.controlpad.payman_processor.payout_processing;

import com.controlpad.payman_common.account.AccountMapper;
import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.fee.FeeMapper;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.payment.PaymentMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.payment_file.PaymentFile;
import com.controlpad.payman_common.payment_file.PaymentFileMapper;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import com.controlpad.payman_common.transaction_charge.TransactionChargeMapper;
import com.controlpad.payman_common.user_account.UserAccount;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.controlpad.payman_processor.payout.file.PayoutFileWriter;
import com.controlpad.payman_common.payout_job.PayoutJob;
import com.controlpad.payman_processor.util.IDUtil;
import org.apache.commons.lang3.BooleanUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.IOException;
import java.math.BigDecimal;
import java.math.RoundingMode;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class FilePayoutMethodUtil extends PaymentMethodUtilBase {

    private static final Logger logger = LoggerFactory.getLogger(FilePayoutMethodUtil.class);

    // Service class codes 200 mixed, 220 credits, 225 debits, 280 automated accounting services
    private PayoutFileWriter payoutFileWriter;
    private PaymentFile paymentFile;
    private Map<String, UserAccount> userAccountMap;
    private Map<Payment, List<Long>> paymentEntryIdMap; // Entry ids per payment for updating paymentId
    private Map<String, Payment> withdrawPaymentsMap;
    private List<Long> cashChargeIds;
    private Map<Long, UserBalances> userBalanceUpdateMap; // To be added to user balances, expected to be negative values
    private Map<Long, Fee> feeMap;

    private Payment consignmentPayment = null;
    private Payment taxPayment = null;
    private Map<Long, Payment> feePayments = null;

    private AccountMapper accountMapper;
    private boolean paymentsCreated = false;
    private boolean isComplete = false;

    FilePayoutMethodUtil(SqlSession clientSession, IDUtil idUtil, ControlPadClient client, PayoutJob payoutJob,
                         Team team, Map<String, UserAccount> userAccountMap) throws Exception {
        super(clientSession, idUtil, client, team);
        this.userAccountMap = userAccountMap;
        this.paymentEntryIdMap = new HashMap<>();
        this.cashChargeIds = new ArrayList<>();
        this.userBalanceUpdateMap = new HashMap<>();
        this.withdrawPaymentsMap = new HashMap<>();
        this.feePayments = new HashMap<>();
        // Create file and generate id so it can be assigned as we build payments
        String today = DateTime.now().toString("yyyy-MM-dd");
        String fileName = today + "_Payouts_" + payoutJob.getId() + ".tsv";
        paymentFile = new PaymentFile(fileName, "Payouts", BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO,
                BigDecimal.ZERO, 0, 0L, 0, team.getId());
        payoutFileWriter = new PayoutFileWriter(paymentFile.getFileName(), client.getId(),
                client.getPosition(), client.getName());
        accountMapper = clientSession.getMapper(AccountMapper.class);
        feeMap = clientSession.getMapper(FeeMapper.class).mapAllFees();
    }

    private void writeWithdraws() throws Exception {
        if (withdrawPaymentsMap.isEmpty()) {
            return;
        }
        // TODO I wonder if there can be auto debiting? Legal matter
        payoutFileWriter.writeBatchHeader("220", "CCD", "SALES PYMT");

        for (Payment merchantWithdraw : withdrawPaymentsMap.values()) {
            payoutFileWriter.writeEntry(userAccountMap.get(merchantWithdraw.getUserId()), merchantWithdraw.getAmount(), merchantWithdraw.getId());
        }
    }

    private void writeFees() throws Exception {
        if (feePayments == null || feePayments.isEmpty()) {
            return;
        }
        payoutFileWriter.writeBatchHeader("220", "CCD", "FEE PYMT");

        for (Map.Entry<Long, Payment> entry : feePayments.entrySet()) {
            // TODO can this go negative ever?
            // TODO check the rounding on this?
            entry.getValue().setAmount(entry.getValue().getAmount().setScale(2, RoundingMode.FLOOR));
            payoutFileWriter.writeEntry(
                    accountMapper.findForId(entry.getValue().getAccountId()),
                    entry.getValue().getAmount(),
                    entry.getValue().getId()
            );
        }
    }

    private void writeTax() throws Exception {
        if (taxPayment == null) {
            return;
        }
        if (taxPayment.getAccountId() == null) {
            logger.error("tax account missing: Client:{} Team:{}", getClient().getId(), getTeam().getId());
            return;
        }
        taxPayment.setAmount(taxPayment.getAmount().setScale(2, RoundingMode.FLOOR));
        String serviceClassCode;
        if (taxPayment.getAmount().compareTo(BigDecimal.ZERO) < 0) {
            serviceClassCode = "225";
        } else {
            serviceClassCode = "220";
        }
        payoutFileWriter.writeBatchHeader(serviceClassCode, "CCD", "TAX PYMT");

        payoutFileWriter.writeEntry(accountMapper.findForId(taxPayment.getAccountId()), taxPayment.getAmount().setScale(2, RoundingMode.FLOOR), taxPayment.getId());
    }

    private void writeConsignment() throws Exception {
        if (consignmentPayment == null) {
            return;
        }
        if (consignmentPayment.getAccountId() == null) {
            logger.error("Consignment Account missing: Client:{} Team:{}", getClient().getId(), getTeam().getId());
            return;
        }

        consignmentPayment.setAmount(consignmentPayment.getAmount().setScale(2, RoundingMode.FLOOR));
        payoutFileWriter.writeBatchHeader("220", "CCD", "CSMT PYMT");

        payoutFileWriter.writeEntry(accountMapper.findForId(consignmentPayment.getAccountId()),
                consignmentPayment.getAmount(), consignmentPayment.getId());
    }

    @Override
    void processWithdraws(UserBalances userBalance, List<Entry> withdraws) {
        if (withdraws == null || withdraws.isEmpty()) {
            return;
        }
        if (!isUserAccountValid(userBalance.getUserId())) {
            // Don't batch for invalid accounts
            return;
        }
        Payment userWithdrawPayment = withdrawPaymentsMap.computeIfAbsent(userBalance.getUserId(),
                k -> new Payment(getIdUtil().generateId(), getTeam().getId(), k, null, BigDecimal.ZERO,
                        null, null, PaymentType.WITHDRAW.slug)
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
                    BigDecimal.ZERO, null, null, PaymentType.SALES_TAX.slug);
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
                    BigDecimal.ZERO, null, null, PaymentType.SALES_TAX.slug);
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
                    BigDecimal.ZERO, null, null, PaymentType.CONSIGNMENT.slug);
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
                BigDecimal.ZERO, null, null, PaymentType.FEE.slug));
        UserBalances updateBalance = findUserBalanceUpdate(userBalance.getId());
        batchEntriesToPayment(feePayment, fees, updateBalance);
    }

    private void batchEntriesToPayment(Payment payment, List<Entry> entries, UserBalances updateBalance) {
        for (Entry entry : entries) {
            payment.addAmount(entry.getAmount());
            // Add withdraw to payment/entry map for updating payment id later
            paymentEntryIdMap.computeIfAbsent(payment, k -> new ArrayList<>()).add(entry.getId());
            updateBalance.addTransaction(entry.getAmount().negate());
        }
    }

    @Override
    void onComplete() throws Exception {
        if (isComplete || paymentsCreated) {
            // This can call multiple times, filtering additional calls
            return;
        }
        EntryMapper entryMapper = getClientSession().getMapper(EntryMapper.class);
        PaymentMapper paymentMapper = getClientSession().getMapper(PaymentMapper.class);
        PaymentFileMapper paymentFileMapper = getClientSession().getMapper(PaymentFileMapper.class);
        TransactionChargeMapper transactionChargeMapper = getClientSession().getMapper(TransactionChargeMapper.class);
        UserBalancesMapper userBalancesMapper = getClientSession().getMapper(UserBalancesMapper.class);

        writeTax();
        writeConsignment();
        writeFees();
        writeWithdraws();

        payoutFileWriter.close();
        if (payoutFileWriter.getFileEntries() > 0) {
            paymentFile.setBatchCount(payoutFileWriter.getBatchCount());
            paymentFile.setCredits(payoutFileWriter.getFileCredits());
            paymentFile.setDebits(BigDecimal.ZERO);
            paymentFile.setEntryCount(payoutFileWriter.getFileEntries());
            paymentFileMapper.insertPaymentFile(paymentFile);
            paymentsCreated = true;
        } else {
            // No file written, return
            paymentsCreated = false;
            return;
        }

        // Update database
        if (!feePayments.isEmpty()) {
            paymentMapper.insertListForFile(feePayments.values(), paymentFile.getId());
        }

        if (consignmentPayment != null && consignmentPayment.getAmount().compareTo(BigDecimal.valueOf(0.01)) >= 0) {
            consignmentPayment.setPaymentFileId(paymentFile.getId());
            paymentMapper.insert(consignmentPayment);
        }
        if (taxPayment != null && taxPayment.getAmount().compareTo(BigDecimal.valueOf(0.01)) >= 0) {
            taxPayment.setPaymentFileId(paymentFile.getId());
            paymentMapper.insert(taxPayment);
        }

        if (!withdrawPaymentsMap.isEmpty()) {
            paymentMapper.insertListForFile(withdrawPaymentsMap.values(), paymentFile.getId());
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

        payoutFileWriter = null;
        isComplete = true;
    }

    private boolean isUserAccountValid(String userId) {
        return !BooleanUtils.isTrue(getClient().getConfig().getFeatures().getAccountValidation()) ||
                userAccountMap.containsKey(userId) && userAccountMap.get(userId).getValidated();
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

    @Override
    boolean isPaymentsCreated() {
        return paymentsCreated;
    }

    @Override
    public void close() throws IOException {
        if (payoutFileWriter != null) {
            try {
                payoutFileWriter.close();
            } catch (Exception closeException) {
                logger.error("Failed to close payout file", closeException);
            }
            payoutFileWriter.delete();
        }
    }
}