/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.transaction_processing;

import com.controlpad.pay_fac.client.ClientConfigUtil;
import com.controlpad.pay_fac.fee.FeeSetUtil;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction.TransactionResult;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import com.controlpad.payman_common.transaction_charge.TransactionChargeMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

import java.math.BigDecimal;
import java.util.List;

@Component
public class TransactionProcessUtil {

    @Autowired
    FeeSetUtil feeSetUtil;
    @Autowired
    ClientConfigUtil clientConfigUtil;

    private static final Logger logger = LoggerFactory.getLogger(TransactionProcessUtil.class);

    public void processCashSale(String clientId, SqlSession sqlSession, Transaction transaction) {
        TransactionChargeMapper transactionChargeMapper = sqlSession.getMapper(TransactionChargeMapper.class);
        TeamMapper teamMapper = sqlSession.getMapper(TeamMapper.class);
        BigDecimal balance = transaction.getAmount();
        BigDecimal taxAmount = transaction.getSalesTax();

        if (balance.compareTo(BigDecimal.ZERO) > 0 && taxAmount != null && taxAmount.compareTo(BigDecimal.ZERO) > 0) {

            if (taxAmount.compareTo(balance) > 0) {
                logger.error("Balance is not enough for tax. Transaction ID:" + transaction.getId() + " Balance:" + balance + " tax:" + taxAmount);
                return;
            }
            balance = balance.subtract(taxAmount);

            Long taxAccountId = teamMapper.getTaxAccountId(transaction.getTeamId());

            TransactionCharge salesTaxCharge = new TransactionCharge(transaction.getPayeeUserId(), transaction.getId(), taxAccountId,
                    taxAmount, PaymentType.SALES_TAX.slug);
            transactionChargeMapper.insert(salesTaxCharge);

        }

        List<Fee> fees = feeSetUtil.getFeesForSet(clientId, transaction.getTeamId(), TransactionType.CASH_SALE.slug);

        BigDecimal feeAmount;
        BigDecimal totalFees = BigDecimal.ZERO;
        TransactionCharge feeCharge;
        for (Fee fee : fees) {
            if (balance.compareTo(BigDecimal.ZERO) <= 0) {
                break;
            }

            feeAmount = fee.calculateChargeAmount(transaction);

            if (feeAmount.compareTo(balance) > 0) {
                feeAmount = balance;
            }
            balance = balance.subtract(feeAmount);
            totalFees = totalFees.add(feeAmount);
            feeCharge = new TransactionCharge(transaction.getPayeeUserId(), transaction.getId(), fee.getAccountId(),
                    feeAmount, fee.getId(), PaymentType.FEE.slug);
            transactionChargeMapper.insert(feeCharge);
        }

        sqlSession.getMapper(TransactionMapper.class).markProcessed(transaction.getId());
    }

    public void processEWalletSale(SqlSession sqlSession, String clientId, Transaction transaction, UserBalances payerBalances) {
        EntryMapper entryMapper = sqlSession.getMapper(EntryMapper.class);
        UserBalancesMapper userBalancesMapper = sqlSession.getMapper(UserBalancesMapper.class);

        UserBalances payeeBalances = getUserBalances(userBalancesMapper, transaction);

        entryMapper.insert(new Entry(payeeBalances.getId(), transaction.getAmount(), transaction.getId(), null,
                null, PaymentType.TRANSFER.slug, true));
        entryMapper.insert(new Entry(payerBalances.getId(), transaction.getAmount().negate(), transaction.getId(), null,
                null, PaymentType.TRANSFER.slug, true));

        userBalancesMapper.add(payeeBalances.getId(), BigDecimal.ZERO, transaction.getAmount(), transaction.getAmount());
        userBalancesMapper.add(payerBalances.getId(), BigDecimal.ZERO, BigDecimal.ZERO, transaction.getAmount().negate());

        cutTax(entryMapper, transaction, userBalancesMapper, payeeBalances);

        cutFees(sqlSession, clientId, transaction, userBalancesMapper, payeeBalances);

        sqlSession.getMapper(TransactionMapper.class).markProcessed(transaction.getId());
    }

    public void processCashRefund(SqlSession clientSession, Transaction refundTransaction, Transaction originalTransaction) {
        TransactionChargeMapper transactionChargeMapper = clientSession.getMapper(TransactionChargeMapper.class);

        if (refundTransaction.getSalesTax() != null) {
            TransactionCharge taxCharge = new TransactionCharge(originalTransaction.getPayeeUserId(), refundTransaction.getId(),
                    null, refundTransaction.getSalesTax().negate(), null, PaymentType.SALES_TAX.slug, false);
            transactionChargeMapper.insert(taxCharge);
        }

        clientSession.getMapper(TransactionMapper.class).markProcessed(refundTransaction.getId());
    }

    public void processEWalletRefund(SqlSession sqlSession, Transaction refundTransaction, Transaction originalTransaction) {
        EntryMapper entryMapper = sqlSession.getMapper(EntryMapper.class);

        UserBalancesMapper userBalancesMapper = sqlSession.getMapper(UserBalancesMapper.class);
        UserBalances payeeBalances = getUserBalances(userBalancesMapper, originalTransaction);
        userBalancesMapper.subtractEWallet(payeeBalances.getId(), refundTransaction.getAmount());
        userBalancesMapper.subtractTransaction(payeeBalances.getId(), refundTransaction.getAmount());

        entryMapper.insert(new Entry(payeeBalances.getId(), refundTransaction.getAmount().negate(), refundTransaction.getId(), null,
                null, PaymentType.TRANSFER.slug, true));

        UserBalances payerBalances = getUserBalances(userBalancesMapper, originalTransaction.getPayerUserId(), originalTransaction.getTeamId());
        userBalancesMapper.add(payerBalances.getId(), BigDecimal.ZERO, refundTransaction.getAmount(), refundTransaction.getAmount());
        entryMapper.insert(new Entry(payerBalances.getId(), refundTransaction.getAmount(), refundTransaction.getId(), null,
                null, PaymentType.TRANSFER.slug, true));
        // TODO figure out tax handling
    }

    public void processEWalletTransfer(SqlSession sqlSession, String clientId, Transaction transaction) {
        if (!isGood(transaction)) {
            return;
        }
        UserBalancesMapper userBalancesMapper = sqlSession.getMapper(UserBalancesMapper.class);
        EntryMapper entryMapper = sqlSession.getMapper(EntryMapper.class);

        UserBalances payeeUserBalance = getUserBalances(userBalancesMapper, transaction.getPayeeUserId(), transaction.getTeamId());
        UserBalances payerUserBalance = getUserBalances(userBalancesMapper, transaction.getPayerUserId(), transaction.getTeamId());

        // Insert payee entry then deduct amount
        entryMapper.insert(new Entry(payeeUserBalance.getId(), transaction.getAmount(), transaction.getId(), null,
                null, PaymentType.TRANSFER.slug, true));
        // Ewallet balance already negated
        userBalancesMapper.add(payeeUserBalance.getId(), BigDecimal.ZERO, transaction.getAmount(), transaction.getAmount());

        // Insert payer entry then add amount
        entryMapper.insert(new Entry(payerUserBalance.getId(), transaction.getAmount().negate(), transaction.getId(), null,
                null, PaymentType.TRANSFER.slug, true));
        userBalancesMapper.add(payerUserBalance.getId(), BigDecimal.ZERO, BigDecimal.ZERO, transaction.getAmount().negate());

        // Take fees for transfer from payee
        cutFees(sqlSession, clientId, transaction, userBalancesMapper, payeeUserBalance);

        sqlSession.getMapper(TransactionMapper.class).markProcessed(transaction.getId());
    }

    public void processEWalletCredit(SqlSession sqlSession, Transaction transaction) {
        UserBalancesMapper userBalancesMapper = sqlSession.getMapper(UserBalancesMapper.class);
        EntryMapper entryMapper = sqlSession.getMapper(EntryMapper.class);

        UserBalances userBalances = getUserBalances(userBalancesMapper, transaction);

        entryMapper.insert(new Entry(userBalances.getId(), transaction.getAmount(), transaction.getId(),
                null, null, PaymentType.TRANSFER.slug, true));

        userBalancesMapper.add(userBalances.getId(), BigDecimal.ZERO, transaction.getAmount(), transaction.getAmount());

        sqlSession.getMapper(TransactionMapper.class).markProcessed(transaction.getId());
    }

    public void processEWalletDebit(SqlSession sqlSession, Transaction transaction) {
        UserBalancesMapper userBalancesMapper = sqlSession.getMapper(UserBalancesMapper.class);
        EntryMapper entryMapper = sqlSession.getMapper(EntryMapper.class);

        UserBalances userBalances = getUserBalances(userBalancesMapper, transaction);
        BigDecimal amount = transaction.getAmount().negate(); // debit is a negative amount

        entryMapper.insert(new Entry(userBalances.getId(), amount, transaction.getId(),
                null, null, PaymentType.TRANSFER.slug, true));

        userBalancesMapper.add(userBalances.getId(), BigDecimal.ZERO, amount, amount);

        sqlSession.getMapper(TransactionMapper.class).markProcessed(transaction.getId());
    }

    public void processEWalletWithdraw(SqlSession sqlSession, String clientId, Transaction transaction, UserBalances userBalances) {
        if (!isGood(transaction)) {
            return;
        }

        EntryMapper entryMapper = sqlSession.getMapper(EntryMapper.class);
        UserBalancesMapper userBalancesMapper = sqlSession.getMapper(UserBalancesMapper.class);

        // Balance already deducted for ewallet, transaction balance will negate when processed
        entryMapper.insert(new Entry(userBalances.getId(), transaction.getAmount().negate(), transaction.getId(), null,
                null, PaymentType.WITHDRAW.slug, false));

        cutFees(sqlSession, clientId, transaction, userBalancesMapper, userBalances);

        sqlSession.getMapper(TransactionMapper.class).markProcessed(transaction.getId());
    }

    public void processEWalletDepositACH(SqlSession sqlSession, String clientId, Transaction transaction) {
//        TransactionPayoutMapper transactionPayoutMapper = sqlSession.getMapper(TransactionPayoutMapper.class);
//        TransactionDebitMapper debitMapper = sqlSession.getMapper(TransactionDebitMapper.class);
//        // Insert debit record
//        TransactionDebit transactionDebit = new TransactionDebit(
//                transaction.getPayerUserId(), transaction.getId(), transaction.getAmount());
//        debitMapper.insert(transactionDebit);
//        debitMapper.generateId();
//
//        // Cut fees and create an ewallet payout
//        BigDecimal balance = transaction.getAmount();
//        balance = cutFees(sqlSession, clientId, transaction, balance);
//
//        TransactionPayout ewalletPayout = new TransactionPayout(transaction.getPayeeUserId(), transaction.getId(),
//                balance, null, TransactionPayoutType.E_WALLET.slug);
//        transactionPayoutMapper.insert(ewalletPayout);
//        sqlSession.getMapper(TransactionMapper.class).markProcessed(transaction.getId());
    }

    public void processEWalletTaxPayment(SqlSession sqlSession, String clientId, Transaction transaction, UserBalances userBalances) {
        if (!isGood(transaction)) {
            return;
        }
        EntryMapper entryMapper = sqlSession.getMapper(EntryMapper.class);
        UserBalancesMapper userBalancesMapper = sqlSession.getMapper(UserBalancesMapper.class);

        entryMapper.insert(new Entry(userBalances.getId(), transaction.getAmount().negate(), transaction.getId(), null,
                null, PaymentType.SALES_TAX.slug, true));

        // Add to sales tax balance
        // Balance already deducted for ewallet, so just update transaction balance
        userBalancesMapper.add(userBalances.getId(), transaction.getAmount(), BigDecimal.ZERO, transaction.getAmount().negate());

        cutFees(sqlSession, clientId, transaction, userBalancesMapper, userBalances);

        sqlSession.getMapper(TransactionMapper.class).markProcessed(transaction.getId());
    }

    public void processACHTaxPayment(SqlSession session, String clientId, Transaction transaction) {
//        TransactionDebitMapper debitMapper = session.getMapper(TransactionDebitMapper.class);
//        // Insert debit record
//        TransactionDebit transactionDebit = new TransactionDebit(
//                transaction.getPayerUserId(), transaction.getId(), transaction.getAmount());
//        debitMapper.insert(transactionDebit);
//        debitMapper.generateId();
//
//        TransactionPayoutMapper transactionPayoutMapper = session.getMapper(TransactionPayoutMapper.class);
//        // Cut fees and create an ewallet payout
//        BigDecimal balance = transaction.getAmount();
//        balance = cutFees(session, clientId, transaction, balance);
//
//        TransactionPayout taxBalancePayout = new TransactionPayout(transaction.getPayeeUserId(), transaction.getId(),
//                balance, null, TransactionPayoutType.TAX_BALANCE.slug);
//        transactionPayoutMapper.insert(taxBalancePayout);
//        // We don't adjust balance until the ach record is actually submitted
//
//        session.getMapper(TransactionMapper.class).markProcessed(transaction.getId());
    }

    private void cutTax(EntryMapper entryMapper, Transaction transaction, UserBalancesMapper userBalancesMapper, UserBalances payeeBalances) {
        // TODO check if collecting tax for user or team
        if (transaction.getSalesTax() == null || transaction.getSalesTax().compareTo(BigDecimal.ZERO) == 0) {
            return;
        }
        entryMapper.insert(new Entry(payeeBalances.getId(), transaction.getSalesTax().negate(), transaction.getId(),
                null, null, PaymentType.SALES_TAX.slug, false));
        userBalancesMapper.addEWallet(payeeBalances.getId(), transaction.getSalesTax().negate());
    }

    private void cutFees(SqlSession sqlSession, String clientId, Transaction transaction, UserBalancesMapper userBalancesMapper, UserBalances payeeBalances) {
        List<Fee> fees = feeSetUtil.getFeesForSet(clientId, transaction.getTeamId(), transaction.getTransactionType());

        if (fees.isEmpty()) {
            return;
        }

        BigDecimal feeAmount;
        for (Fee fee : fees) {
            feeAmount = fee.calculateChargeAmount(transaction).negate();
            sqlSession.getMapper(EntryMapper.class).insert(new Entry(payeeBalances.getId(), feeAmount, transaction.getId(),
                    fee.getId(), null, PaymentType.FEE.slug, (fee.getAccountId() == null)));
            userBalancesMapper.add(payeeBalances.getId(), BigDecimal.ZERO, feeAmount, (fee.getAccountId() != null ? feeAmount : BigDecimal.ZERO));
        }
    }

    private UserBalances getUserBalances(UserBalancesMapper userBalancesMapper, String userId, String teamId) {
        UserBalances userBalances = userBalancesMapper.find(userId, teamId);
        if (userBalances == null) {
            userBalances = new UserBalances(userId, teamId, 0D, 0D, 0D);
            userBalancesMapper.insert(userBalances);
        }
        return userBalances;
    }

    private UserBalances getUserBalances(UserBalancesMapper userBalancesMapper, Transaction transaction) {
        return getUserBalances(userBalancesMapper, transaction.getPayeeUserId(), transaction.getTeamId());
    }

    private boolean isGood(Transaction transaction) {
        return transaction.getResultCode() == TransactionResult.Success.getResultCode()
                && StringUtils.equals(transaction.getStatusCode(), "S");
    }
}