package com.controlpad.payman_processor.gateway;

import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import org.apache.ibatis.session.SqlSession;
import org.springframework.stereotype.Component;

import java.math.BigDecimal;
import java.math.BigInteger;
import java.util.ArrayList;
import java.util.List;

import static com.controlpad.payman_processor.transaction_processing.TransactionUpdateResult.STOP;
import static com.controlpad.payman_processor.transaction_processing.TransactionUpdateResult.UPDATED;

@Component
public class MockGateway implements Gateway {

    @Override
    public int updateTransactionStatus(SqlSession session, GatewayConnection gatewayConnection,
                                       Transaction currentTransaction, String clientId) {
        if (currentTransaction.getBatchId() == null) {
            return STOP;
        }
        currentTransaction.setStatusCode("S");
        session.getMapper(TransactionMapper.class).updateTransactionStatusAndBatch(currentTransaction);
        return UPDATED;
    }

    @Override
    public String createWithdraw(GatewayConnection gatewayConnection, Payment payment) {
        return "Fake id";
    }

    @Override
    public String createTaxFee(GatewayConnection gatewayConnection, Payment payment) {
        return "Fake id";
    }

    @Override
    public String createConsignmentFee(GatewayConnection gatewayConnection, Payment payment) {
        return "Fake id";
    }

    @Override
    public BigDecimal getSubAccountBalance(GatewayConnection gatewayConnection) {
        return BigDecimal.valueOf(1000D);
    }

    @Override
    public boolean checkTransactionBatch(SqlSession clientSession, GatewayConnection gatewayConnection, TransactionBatch transactionBatch) {
        TransactionBatch fakeStats = clientSession.getMapper(TransactionBatchMapper.class).calculateTransactionStats(transactionBatch.getId());
        transactionBatch.setGatewayTransactionCount(fakeStats.getTransactionCount());
        // Net amount = sales + sub + shipping - refunds - voids

        if (fakeStats.getTransactionCount() == null || fakeStats.getTransactionCount().compareTo(BigInteger.ZERO) == 0) {
            transactionBatch.setGatewayNetAmount(BigDecimal.ZERO);
        } else {
            transactionBatch.setGatewayNetAmount(
                    fakeStats.getSales()
                            .add(fakeStats.getSubscriptions())
                            .add(fakeStats.getShipping())
                            .subtract(fakeStats.getRefunds()
                                    .subtract(fakeStats.getVoids())));
        }
        return true;
    }

    @Override
    public List<FeeEntry> getInternalFees(GatewayConnection gatewayConnection, Transaction currentTransaction, Long userBalanceId) {
        return new ArrayList<>();
    }

}
