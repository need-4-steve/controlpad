/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.refund;

import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.gateway.GatewayUtil;
import com.controlpad.pay_fac.transaction.TransactionUtil;
import com.controlpad.pay_fac.transaction_processing.TransactionProcessUtil;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.affiliate_charge.AffiliateChargeMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction.TransactionResult;
import com.controlpad.payman_common.transaction.TransactionType;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Component;

import java.math.BigDecimal;
import java.util.Map;


@Component
public class RefundUtil {

    private static final Logger logger = LoggerFactory.getLogger(RefundUtil.class);

    @Autowired
    GatewayUtil gatewayUtil;
    @Autowired
    TransactionProcessUtil transactionProcessUtil;
    @Autowired
    IDUtil idUtil;

    public Transaction refundTransaction(SqlSession sqlSession, Transaction refund,
                                                 String remoteAddress, String clientId) {
        TransactionMapper transactionMapper = sqlSession.getMapper(TransactionMapper.class);
        AffiliateChargeMapper affiliateChargeMapper = sqlSession.getMapper(AffiliateChargeMapper.class);

        Transaction originalTransaction = transactionMapper.findById(refund.getForTxnId());
        if (originalTransaction == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Transaction id invalid");
        }
        refund.setPayeeUserId(originalTransaction.getPayeeUserId());
        refund.setPayerUserId(originalTransaction.getPayerUserId());
        refund.setTeamId(originalTransaction.getTeamId());

        Transaction refundTotals = transactionMapper.getRefundTotalsForTransactionId(refund.getForTxnId());
        if (refundTotals == null) {
            refundTotals = new Transaction(BigDecimal.ZERO, BigDecimal.ZERO);
        }

        BigDecimal balance = originalTransaction.getAmount().subtract(refundTotals.getAmount());
        if (refund.getAmount().compareTo(balance) > 0) {
            refund.updateResultAndCode(TransactionResult.Maximum_Limit.getResultCode());
            refund.setStatusCode("D");
            return refund;
        }

        if (refund.getSalesTax() != null && refund.getSalesTax().compareTo(originalTransaction.getSalesTax().subtract(refundTotals.getSalesTax())) > 0) {
            refund.updateResultAndCode(TransactionResult.Maximum_Tax.getResultCode());
            refund.setStatusCode("D");
            return refund;
        }

        if (refund.getAffiliatePayouts() != null) {
            Map<String, AffiliateCharge> affiliateChargeBalanceMap = affiliateChargeMapper.mapAffiliateChargeTotalsForTransaction(refund.getForTxnId());
            for (AffiliateCharge affiliateCharge : refund.getAffiliatePayouts()) {
                if (!affiliateChargeBalanceMap.containsKey(affiliateCharge.getPayeeUserId()) ||
                        affiliateChargeBalanceMap.get(affiliateCharge.getPayeeUserId()).getAmount().compareTo(affiliateCharge.getAmount()) < 0) {
                    throw new ResponseException(HttpStatus.BAD_REQUEST,
                            String.format("Affiliate charge refund too high for user: %s | Original transaction plus partial refunds affects this balance check", affiliateCharge.getPayeeUserId()));
                }
            }
        }

        TransactionType transactionType = TransactionType.findBySlug(originalTransaction.getTransactionType());
        if (StringUtils.equalsIgnoreCase(refund.getTransactionType(), TransactionType.REFUND_CASH.slug)
                || transactionType == TransactionType.CASH_SALE) {

            refund.setStatusCode("S");
            refund.updateResultAndCode(TransactionResult.Success.getResultCode());

            TransactionUtil.insertTransaction(sqlSession, refund, idUtil, refund.getAffiliatePayouts());
            transactionProcessUtil.processCashRefund(sqlSession, refund, originalTransaction);
            return refund;
        } else {
            switch (transactionType) {
                // TODO cash sale instant?
                case CHECK_SALE:
                case CHECK_SUB:
                case CREDIT_CARD_SALE:
                case CREDIT_CARD_SUB:
                case DEBIT_CARD_SALE:
                case DEBIT_CARD_SUB:
                case PAYPAL_SALE:
                    refund = refundGatewayTransaction(sqlSession, originalTransaction, refund, remoteAddress, clientId);
                    if(refund.getResultCode() == TransactionResult.Success.getResultCode()){
                        if (refund.getTransactionType().equalsIgnoreCase(TransactionType.VOID.slug)) {
                            // A void happened
                            originalTransaction.setStatusCode("V");
                            transactionMapper.updateTransactionStatus(originalTransaction);
                            transactionMapper.markProcessed(originalTransaction.getId());
                            transactionMapper.markProcessed(refund.getId());
                            // No affiliate retraction for voids
                            TransactionUtil.insertTransaction(sqlSession, refund, idUtil, null);
                        } else {
                            TransactionUtil.insertTransaction(sqlSession, refund, idUtil, refund.getAffiliatePayouts());
                        }
                    }
                    return refund;
                case E_WALLET_SALE:
                case E_WALLET_SUB:
                    refund.updateResultAndCode(TransactionResult.Success.getResultCode());
                    refund.setStatusCode("S");

                    TransactionUtil.insertTransaction(sqlSession, refund, idUtil, refund.getAffiliatePayouts());
                    transactionProcessUtil.processEWalletRefund(sqlSession, refund, originalTransaction);
                    return refund;
                default:
                    logger.error("Refund bad transaction type: " + transactionType.slug);
                    throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
            }
        }
    }

    private Transaction refundGatewayTransaction(SqlSession sqlSession, Transaction originalTransaction,
                                                         Transaction refund, String remoteAddress, String clientId) {
        GatewayConnection gatewayConnection = sqlSession.getMapper(GatewayConnectionMapper.class)
                .findById(originalTransaction.getGatewayConnectionId());
        if (gatewayConnection == null) {
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
        return gatewayUtil.getGatewayApi(gatewayConnection).refundTransaction(gatewayConnection, originalTransaction,
                refund, remoteAddress, clientId);
    }

}