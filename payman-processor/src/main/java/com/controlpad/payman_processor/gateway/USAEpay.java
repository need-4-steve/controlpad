/*===============================================================================
* Copyright 2014(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_processor.gateway;

import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.controlpad.payman_common.util.GsonUtil;
import com.usaepay.api.jaxws.*;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;
import org.springframework.stereotype.Component;

import java.math.BigDecimal;
import java.math.BigInteger;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import static com.controlpad.payman_processor.transaction_processing.TransactionUpdateResult.*;

@Component
public class USAEpay implements Gateway {

    private static final Logger logger = LoggerFactory.getLogger(USAEpay.class);

    private static final String PRODUCTION_URL = "www.usaepay.com";
    private static final String SANDBOX_URL = "sandbox.usaepay.com";

    private DateTimeFormatter dateTimeFormatter = DateTimeFormat.forPattern("MM/dd/yyyy'T'HH:mm:ss");

    @Override
    public int updateTransactionStatus(SqlSession session, GatewayConnection gatewayConnection,
                                       Transaction currentTransaction, String clientId) {
        UeSecurityToken ueSecurityToken = USAEpay.getToken(gatewayConnection.getPrivateKey(), gatewayConnection.getPin(), "payman-processor");
        UeSoapServerPortType usaepayClient = USAEpay.getClient(USAEpay.getUrl(gatewayConnection));
        if (usaepayClient == null) { // If not found
            logger.error("usaepay client not found for connection\nClient: {}\nGatewayConnection: {}",
                    clientId, gatewayConnection.getId());
            return ERROR;
        }
        int result = 0;
        try {
            TransactionBatchMapper transactionBatchMapper = session.getMapper(TransactionBatchMapper.class);

            TransactionObject gatewayTransaction = usaepayClient.getTransaction(ueSecurityToken, new BigInteger(currentTransaction.getGatewayReferenceId()));
            switch (gatewayTransaction.getStatus()) {
                case "Voided":
                case "Voided (Funds Released)":
                    currentTransaction.setStatusCode("V");
                    break;
                case "Settled":
                    currentTransaction.setStatusCode("S");
                    break;
                case "Authorized (Will not be captured)":
                case "Pending Settlement":
                case "Authorized (Pending Settlement)":
                    return SKIP;
                case "Error":
                default:
                    currentTransaction.setStatusCode("E");
                    session.getMapper(TransactionMapper.class).updateTransactionStatus(currentTransaction);
                    session.commit();
                    MDC.put("clientId", clientId);
                    MDC.put("transactionId", currentTransaction.getId());
                    MDC.put("gatewayTransaction", GsonUtil.getGson().toJson(gatewayTransaction));
                    MDC.put("gatewayConnectionId", String.valueOf(gatewayConnection.getId()));
                    logger.error("Transaction status unexpected");
                    MDC.clear();
                    return SKIP;
            }

            String external_batch_id = gatewayTransaction.getResponse().getBatchRefNum().toString();
            Long batchId = transactionBatchMapper.findTransactionBatchIdForExternalId(currentTransaction.getGatewayConnectionId(), external_batch_id);

            if (StringUtils.equals(external_batch_id, "-2")) { // Part of an open batch, no reason to update
                return STOP;
            } else if (StringUtils.equals(external_batch_id, "-1")) { // Non settled e-checks, TODO probably need a new state
                return SKIP;
            } else if (batchId == null) { // Create a gateway batch record
                BatchStatus batchStatus = usaepayClient.getBatchStatus(ueSecurityToken, new BigInteger(external_batch_id));
                if (StringUtils.equalsIgnoreCase("Open", batchStatus.getStatus())) {
                    return STOP;
                }
                TransactionBatch transactionBatch = new TransactionBatch(currentTransaction.getGatewayConnectionId(), external_batch_id, null);
                transactionBatchMapper.insert(transactionBatch);
                currentTransaction.setBatchId(transactionBatch.getId());
                result ^= BATCH_CREATED;
            } else {  // Set batch id
                currentTransaction.setBatchId(batchId);
            }

            session.getMapper(TransactionMapper.class).updateTransactionStatusAndBatch(currentTransaction);
            result ^= UPDATED;
        } catch (Exception e) {
            logger.error(String.format("Failed to update transaction status | Client: %s | Transaction: %s",
                    clientId, currentTransaction.getId()),
                    e);
            return ERROR;
        }
        return result;
    }

    @Override
    public String createWithdraw(GatewayConnection gatewayConnection, Payment payment) {
        logger.error(String.format(Locale.US, "createWithdraw called on USAePay: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public String createTaxFee(GatewayConnection gatewayConnection, Payment payment) {
        logger.error(String.format(Locale.US, "createTaxFee called on USAePay: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public String createConsignmentFee(GatewayConnection gatewayConnection, Payment payment) {
        logger.error(String.format(Locale.US, "createConsignmentFee called on USAePay: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public BigDecimal getSubAccountBalance(GatewayConnection gatewayConnection) {
        logger.error(String.format(Locale.US, "getSubAccountBalance called on USAePay: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public boolean checkTransactionBatch(SqlSession clientSession, GatewayConnection gatewayConnection, TransactionBatch transactionBatch) {
        UeSecurityToken ueSecurityToken = USAEpay.getToken(gatewayConnection.getPrivateKey(), gatewayConnection.getPin(), "payman-processor");
        UeSoapServerPortType usaepayClient = USAEpay.getClient(USAEpay.getUrl(gatewayConnection));
        if (usaepayClient == null) { // If not found
            logger.error("usaepay client not found for connection: {}", gatewayConnection.getId());
            return false;
        }
        try {
            BatchStatus batchStatus = usaepayClient.getBatchStatus(ueSecurityToken, new BigInteger(transactionBatch.getExternalId()));
            if (StringUtils.equalsIgnoreCase(batchStatus.getStatus(), "closed")) {
                DateTime settlementTime = DateTime.parse(batchStatus.getClosed(), dateTimeFormatter).plusHours(7); // Not sure if mountain time is for usaepay or the individual account
                transactionBatch.setSettledAt(settlementTime);
                transactionBatch.setGatewayNetAmount(BigDecimal.valueOf(batchStatus.getNetAmount()));
                transactionBatch.setGatewayTransactionCount(batchStatus.getTransactionCount());
                return true;
            }
            return false;
        } catch (Exception e) {
            logger.error("usaepay error for checkTransactionBatch", e);
            return false;
        }
    }

    @Override
    public List<FeeEntry> getInternalFees(GatewayConnection gatewayConnection, Transaction currentTransaction, Long userBalanceId) {
        return new ArrayList<>();
    }

    public static UeSecurityToken getToken(String sourceKey, String pin, String clientIP) {
        try {
            return usaepay.getToken(sourceKey, (pin == null ? "" : pin), clientIP);
        } catch (Exception e) {
            logger.error("Failed to get token.", e);
            // TODO throw exception that will help log meta data
            throw new RuntimeException(e);
        }
    }

    public static UeSoapServerPortType getClient(String url) {
        try {
            return usaepay.getClient(url);
        } catch (Exception e) {
            logger.error("Failed to get client.", e);
            return null;
        }
    }

    public static String getUrl(GatewayConnection gatewayConnection) {
        return gatewayConnection.getIsSandbox() ? SANDBOX_URL : PRODUCTION_URL;
    }

}