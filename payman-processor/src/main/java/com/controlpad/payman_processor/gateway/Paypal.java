package com.controlpad.payman_processor.gateway;

import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.paypal.api.payments.Payment;
import com.paypal.base.rest.APIContext;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import static com.controlpad.payman_processor.transaction_processing.TransactionUpdateResult.*;


public class Paypal implements Gateway {

    private static final Logger logger = LoggerFactory.getLogger(Paypal.class);


    @Override
    public int updateTransactionStatus(SqlSession session, GatewayConnection gatewayConnection,
                                       Transaction currentTransaction, String clientId) {
        // We are the ones batching the transactions, if they haven't been batched we don't worry about them
        TransactionMapper transactionMapper = session.getMapper(TransactionMapper.class);
        if (currentTransaction.getBatchId() == null) {
            return STOP;
        }
        try {
            Payment payment = Payment.get(createApiContextFromGatewayConnection(gatewayConnection), currentTransaction.getGatewayReferenceId());
            if (!StringUtils.equalsIgnoreCase(payment.getState(), "approved")) {
                logger.error(String.format(Locale.US, "Paypal.updateTransactionStatus found a non approved transaction: %s", currentTransaction.getId()));
                currentTransaction.setStatusCode("E");
                transactionMapper.updateTransactionStatus(currentTransaction);
                return SKIP;
            }
            // TODO find a way to look at the sale to see if it's been refunded fully, and it's state, or use the refunds table
            currentTransaction.setStatusCode("S");
            transactionMapper.updateTransactionStatus(currentTransaction);
            return UPDATED;
        } catch (Exception e) {
            logger.error(e.getMessage(), e);
        }
        return STOP;
    }

    @Override
    public String createWithdraw(GatewayConnection gatewayConnection, com.controlpad.payman_common.payment.Payment payment) {
        logger.error(String.format(Locale.US, "createWithdraw called on PayPal: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public String createTaxFee(GatewayConnection gatewayConnection, com.controlpad.payman_common.payment.Payment payment) {
        logger.error(String.format(Locale.US, "createTaxFee called on PayPal: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public String createConsignmentFee(GatewayConnection gatewayConnection, com.controlpad.payman_common.payment.Payment payment) {
        logger.error(String.format(Locale.US, "createConsignmentFee called on PayPal: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public BigDecimal getSubAccountBalance(GatewayConnection gatewayConnection) {
        logger.error(String.format(Locale.US, "getSubAccountBalance called on PayPal: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public boolean checkTransactionBatch(SqlSession clientSession, GatewayConnection gatewayConnection, TransactionBatch transactionBatch) {
        return true;
    }

    @Override
    public List<FeeEntry> getInternalFees(GatewayConnection gatewayConnection, Transaction currentTransaction, Long userBalanceId) {
        return new ArrayList<>();
    }

    private APIContext createApiContextFromGatewayConnection(GatewayConnection gatewayConnection) {
        return new APIContext(gatewayConnection.getUsername(), gatewayConnection.getPrivateKey(),
                (gatewayConnection.getIsSandbox() ? "sandbox" : "live"));
    }
}
