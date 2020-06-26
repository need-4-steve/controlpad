package com.controlpad.pay_fac.gateway;

import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.pay_fac.gatewayconnection.SubAccountUser;
import com.controlpad.pay_fac.payment_info.TokenRequest;
import com.controlpad.pay_fac.report.gateway.GatewayTransaction;
import com.controlpad.pay_fac.tokenization.TokenizeCardResponse;
import com.controlpad.pay_fac.transaction.TransactionUtil;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionType;
import com.controlpad.payman_common.transaction.*;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;

import java.math.BigInteger;
import java.util.List;

public class MockGateway implements Gateway {

    private final static String dbFormat = "yyyy-MM-dd HH:mm:ss";

    private IDUtil idUtil;
    private DateTimeFormatter dbFormatter;

    MockGateway(IDUtil idUtil) {
        this.idUtil = idUtil;
        this.dbFormatter = DateTimeFormat.forPattern(dbFormat);
    }

    @Override
    public Transaction saleCard(SqlSession session, GatewayConnection gatewayConnection, String remoteAddress,
                                        Transaction transaction, TransactionType transactionType) {

        if (transaction.getCard() != null && StringUtils.equals("9999999999999995", transaction.getCard().getNumber())) {
            transaction.setStatusCode("D");
            transaction.updateResultAndCode(TransactionResult.Invalid_Card_Number.getResultCode());
            return transaction;
        }
        Card card = transaction.getCard();

        if (StringUtils.equalsIgnoreCase(transaction.getStatusCode(), "a")) {
            transaction.setStatusCode("A");
        } else {
            transaction.setStatusCode("P");
        }

        transaction.updateResultAndCode(TransactionResult.Success.getResultCode());
        transaction.setGatewayConnectionId(gatewayConnection.getId());
        if (card != null) {
            transaction.setSwiped(card.getEncMagstripe() != null || card.getMagstripe() != null);
        }
        TransactionUtil.insertTransaction(session, transaction, idUtil, transaction.getAffiliatePayouts());

        return transaction;
    }

    @Override
    public Transaction saleCheck(SqlSession session, GatewayConnection gatewayConnection, Transaction transaction,
                                         String remoteAddress, TransactionType transactionType) {

        transaction.setStatusCode("P");
        transaction.updateResultAndCode(TransactionResult.Success.getResultCode());
        transaction.setGatewayConnectionId(gatewayConnection.getId());
        transaction.setGatewayReferenceId("1111");

        TransactionUtil.insertTransaction(session, transaction, idUtil, transaction.getAffiliatePayouts());
        return transaction;
    }

    @Override
    public Transaction updateTransactionStatus(SqlSession session, Transaction currentTransaction, GatewayConnection gatewayConnection, String remoteAddress) {
        currentTransaction.setStatusCode("S");
        session.getMapper(TransactionMapper.class).updateTransactionStatus(currentTransaction);
        return currentTransaction;
    }

    @Override
    public Transaction refundTransaction(GatewayConnection gatewayConnection, Transaction transaction,
                                                 Transaction refund, String remoteAddress, String clientId) {

        if (!StringUtils.equals(transaction.getStatusCode(), "S") && (refund.getAmount().compareTo(transaction.getAmount()) == 0)) {
            refund.setGatewayConnectionId(gatewayConnection.getId());
            refund.setStatusCode("S");
            refund.updateResultAndCode(TransactionResult.Success.getResultCode());
            refund.setTransactionType(TransactionType.VOID.slug);
        } else {
            refund.setGatewayConnectionId(gatewayConnection.getId());
            refund.setStatusCode("P");
            refund.updateResultAndCode(TransactionResult.Success.getResultCode());
        }
        return refund;
    }

    @Override
    public TokenizeCardResponse tokenizeCard(TokenRequest creditTokenRequestData, GatewayConnection gatewayConnection, String remoteAddress) {
        return new TokenizeCardResponse(new TokenizeCardResponse.CardToken("a38ahf8i4hahgr3dd", "l389dan3joh", creditTokenRequestData.getExpireDate(), "1111", "V", "9sjak3cs9z0vlwe"));
    }

    @Override
    public Boolean isAccountSame(GatewayConnection currentConnection, GatewayConnection newConnection, SqlSession session) {
        return true;
    }

    @Override
    public boolean checkCredentials(GatewayConnection gatewayConnection) {
        if (StringUtils.equals(gatewayConnection.getUsername(), "invalid")) {
            return false;
        }
        return true;
    }

    @Override
    public CommonResponse createSubAccount(String clientName, SqlSession clientSession, GatewayConnection masterConnection, SubAccountUser subAccountUser) {
        // Save user gateway connection
        GatewayConnection subAccountGC = new GatewayConnection(subAccountUser.getTeamId(), subAccountUser.getUserId(),
                subAccountUser.getBusiness().getName(), null, null, null, masterConnection.getPrivateKey(), GatewayConnectionType.MOCK.slug,
                masterConnection.getIsSandbox(), false, true, false, false,
                masterConnection.getId(), false, true);

        clientSession.getMapper(GatewayConnectionMapper.class).insert(subAccountGC);

        return new CommonResponse<>(true, 1, "Success");
    }

    @Override
    public boolean updateSubAccount(GatewayConnection gatewayConnection, SubAccountUser subAccountUser) {
        return true;
    }

    @Override
    public Transaction importTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction) {
        transaction.setStatusCode("A");
        transaction.updateResultAndCode(TransactionResult.Success.getResultCode());
        transaction.setGatewayConnectionId(gatewayConnection.getId());
        TransactionUtil.insertTransaction(sqlSession, transaction, idUtil, null);
        return transaction;
    }

    @Override
    public Transaction captureTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction, String clientId) {
        transaction.setStatusCode("P");
        transaction.updateResultAndCode(TransactionResult.Success.getResultCode());
        sqlSession.getMapper(TransactionMapper.class).updateTransactionStatusOrderId(transaction);
        return transaction;
    }

    @Override
    public Object getTransaction(GatewayConnection gatewayConnection, Transaction transaction) {
        return null;
    }

    @Override
    public List<TransactionBatch> searchGatewayBatches(SqlSession clientSession, GatewayConnection gatewayConnection, DateTime startDate,
                                                       DateTime endDate, BigInteger page, BigInteger limit) {
        String startDateString = null;
        String endDateString = null;
        if (startDate != null) {
            startDateString = startDate.toString(dbFormatter);
        }
        if (endDate != null) {
            endDateString = endDate.toString(dbFormatter);
        }
        return clientSession.getMapper(TransactionBatchMapper.class).search(BigInteger.valueOf(gatewayConnection.getId()),
                null, startDateString, endDateString, page.subtract(BigInteger.ONE).multiply(limit), limit);
    }

    @Override
    public List<GatewayTransaction> searchTransactions(SqlSession clientSession, GatewayConnection gatewayConnection, String externalBatchId, BigInteger page, BigInteger limit) {

        return null;
    }
}