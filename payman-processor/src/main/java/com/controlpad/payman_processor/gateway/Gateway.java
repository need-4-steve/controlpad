package com.controlpad.payman_processor.gateway;


import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;

import java.math.BigDecimal;
import java.math.BigInteger;
import java.util.List;

public interface Gateway {

    int updateTransactionStatus(SqlSession session, GatewayConnection gatewayConnection,
                                Transaction currentTransaction, String clientId);

    String createWithdraw(GatewayConnection gatewayConnection, Payment payment);

    String createTaxFee(GatewayConnection gatewayConnection, Payment payment);

    String createConsignmentFee(GatewayConnection gatewayConnection, Payment payment);

    boolean checkTransactionBatch(SqlSession clientSession, GatewayConnection gatewayConnection, TransactionBatch transactionBatch);

    List<FeeEntry> getInternalFees(GatewayConnection gatewayConnection, Transaction currentTransaction, Long userBalanceId);

    BigDecimal getSubAccountBalance(GatewayConnection gatewayConnection);

}
