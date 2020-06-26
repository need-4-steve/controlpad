package com.controlpad.pay_fac.gateway;


import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.pay_fac.gatewayconnection.SubAccountUser;
import com.controlpad.pay_fac.payment_info.TokenRequest;
import com.controlpad.pay_fac.report.gateway.GatewayTransaction;
import com.controlpad.pay_fac.tokenization.TokenizeCardResponse;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;

import java.math.BigInteger;
import java.util.List;

public interface Gateway {

    Transaction saleCard(SqlSession session, GatewayConnection gatewayConnection, String remoteAddress,
                                 Transaction transaction, TransactionType transactionType);

    Transaction saleCheck(SqlSession session, GatewayConnection gatewayConnection, Transaction transaction,
                                  String remoteAddress, TransactionType transactionType);

    Transaction updateTransactionStatus(SqlSession session, Transaction currentTransaction, GatewayConnection gatewayConnection,
                                        String remoteAddress);

    Transaction refundTransaction(GatewayConnection gatewayConnection, Transaction originalTransaction,
                                          Transaction refund, String remoteAddress, String clientId);

    TokenizeCardResponse tokenizeCard(TokenRequest creditTokenRequestData, GatewayConnection gatewayConnection, String remoteAddress);

    Boolean isAccountSame(GatewayConnection currentConnection, GatewayConnection newConnection, SqlSession session);

    boolean checkCredentials(GatewayConnection gatewayConnection);

    CommonResponse createSubAccount(String clientName, SqlSession clientSession, GatewayConnection masterConnection, SubAccountUser subAccountUser);

    boolean updateSubAccount(GatewayConnection gatewayConnection, SubAccountUser subAccountUser);

    Transaction importTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction);

    Transaction captureTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction, String clientId);

    Object getTransaction(GatewayConnection gatewayConnection, Transaction transaction);

    List<TransactionBatch> searchGatewayBatches(SqlSession clientSession, GatewayConnection gatewayConnection,
                                                DateTime startDate, DateTime endDate, BigInteger page, BigInteger limit);

    List<GatewayTransaction> searchTransactions(SqlSession clientSession, GatewayConnection gatewayConnection, String externalBatchId,
                                                BigInteger page, BigInteger limit);
}
