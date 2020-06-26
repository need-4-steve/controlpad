package com.controlpad.payman_processor.gateway;

import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import org.apache.ibatis.session.SqlSession;

import java.math.BigDecimal;
import java.util.List;

public class NexioPay implements Gateway {
    @Override
    public int updateTransactionStatus(SqlSession session, GatewayConnection gatewayConnection, Transaction currentTransaction, String clientId) {
        return 0;
    }

    @Override
    public String createWithdraw(GatewayConnection gatewayConnection, Payment payment) {
        return null;
    }

    @Override
    public String createTaxFee(GatewayConnection gatewayConnection, Payment payment) {
        return null;
    }

    @Override
    public String createConsignmentFee(GatewayConnection gatewayConnection, Payment payment) {
        return null;
    }

    @Override
    public boolean checkTransactionBatch(SqlSession clientSession, GatewayConnection gatewayConnection, TransactionBatch transactionBatch) {
        return false;
    }

    @Override
    public List<FeeEntry> getInternalFees(GatewayConnection gatewayConnection, Transaction currentTransaction, Long userBalanceId) {
        return null;
    }

    @Override
    public BigDecimal getSubAccountBalance(GatewayConnection gatewayConnection) {
        return null;
    }
}
