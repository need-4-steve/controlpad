package com.controlpad.payman_processor.gateway;

import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.stripe.model.Charge;
import com.stripe.net.RequestOptions;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import static com.controlpad.payman_processor.transaction_processing.TransactionUpdateResult.*;


public class Stripe implements Gateway {

    private static final Logger logger = LoggerFactory.getLogger(Stripe.class);



    @Override
    public int updateTransactionStatus(SqlSession session, GatewayConnection gatewayConnection,
                                       Transaction currentTransaction, String clientId) {

        RequestOptions requestOptions = RequestOptions.builder().setApiKey(gatewayConnection.getPrivateKey()).build();
        int result = STOP;

        try {
            Charge charge = Charge.retrieve(currentTransaction.getGatewayReferenceId(), requestOptions);
            if (charge.getTransfer() != null) {
                TransactionBatchMapper transactionBatchMapper = session.getMapper(TransactionBatchMapper.class);

                Long batchId = transactionBatchMapper.findTransactionBatchIdForExternalId(currentTransaction.getGatewayConnectionId(), charge.getTransfer());
                if (batchId == null) {
                    TransactionBatch transactionBatch = new TransactionBatch(gatewayConnection.getId(), charge.getTransfer(), null, 0, null, null);
                    transactionBatchMapper.insert(transactionBatch);
                    batchId = transactionBatch.getId();
                    result ^= BATCH_CREATED;
                }
                // TODO consider checking refunds to see if the transaction has been refunded fully on the same day and set to void?
                currentTransaction.setStatusCode("S");
                currentTransaction.setBatchId(batchId);
                session.getMapper(TransactionMapper.class).updateTransactionStatusAndBatch(currentTransaction);
                result ^= UPDATED;
            } else {
                // Made it to an 'open batch'
                result = STOP;
            }
        } catch (Exception e) {
            logger.error(e.getMessage(), e);
            result = ERROR;
        }
        return result;
    }

    @Override
    public String createWithdraw(GatewayConnection gatewayConnection, Payment payment) {
        logger.error(String.format(Locale.US, "createWithdraw called on Stripe: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public String createTaxFee(GatewayConnection gatewayConnection, Payment payment) {
        logger.error(String.format(Locale.US, "createTaxFee called on Stripe: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public String createConsignmentFee(GatewayConnection gatewayConnection, Payment payment) {
        logger.error(String.format(Locale.US, "createConsignmentFee called on Stripe: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public BigDecimal getSubAccountBalance(GatewayConnection gatewayConnection) {
        logger.error(String.format(Locale.US, "getSubAccountBalance called on Stripe: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public boolean checkTransactionBatch(SqlSession clientSession, GatewayConnection gatewayConnection, TransactionBatch transactionBatch) {
        return false;
    }

    @Override
    public List<FeeEntry> getInternalFees(GatewayConnection gatewayConnection, Transaction currentTransaction, Long userBalanceId) {
        return new ArrayList<>();
    }
}
