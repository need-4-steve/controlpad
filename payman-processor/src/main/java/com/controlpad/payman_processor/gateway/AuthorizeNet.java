package com.controlpad.payman_processor.gateway;

import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.controlpad.payman_common.util.GsonUtil;
import net.authorize.Environment;
import net.authorize.api.contract.v1.*;
import net.authorize.api.controller.GetBatchStatisticsController;
import net.authorize.api.controller.GetTransactionDetailsController;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
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
import static net.authorize.api.contract.v1.TransactionStatusEnum.fromValue;


@Component
public class AuthorizeNet implements Gateway {

    private static final Logger logger = LoggerFactory.getLogger(AuthorizeNet.class);

    @Override
    public int updateTransactionStatus(SqlSession session, GatewayConnection gatewayConnection,
                                       Transaction currentTransaction, String clientId) {
        int result = 0;
        GetTransactionDetailsRequest getRequest = new GetTransactionDetailsRequest();
        getRequest.setMerchantAuthentication(getMerchantAuth(gatewayConnection));
        getRequest.setTransId(currentTransaction.getGatewayReferenceId());

        GetTransactionDetailsController controller = new GetTransactionDetailsController(getRequest);
        controller.execute((gatewayConnection.getIsSandbox() ? Environment.SANDBOX : Environment.PRODUCTION));

        GetTransactionDetailsResponse response = controller.getApiResponse();

        if (response != null) {
            if (response.getMessages().getResultCode() == MessageTypeEnum.OK) {
                TransactionType transactionType = TransactionType.findBySlug(currentTransaction.getTransactionType());
                TransactionStatusEnum status = fromValue(response.getTransaction().getTransactionStatus());
                switch (status) {
                    case REFUND_SETTLED_SUCCESSFULLY:
                        if (!currentTransaction.getTransactionType().equals("refund")) {
                            logger.error("Transaction status REFUND_SETTLED_SUCCESSFULLY but type not refund\nClient: {}\nTransaction: {} ",
                                    clientId, currentTransaction.getId());
                            currentTransaction.setStatusCode("E");
                            return SKIP;
                        }
                    case SETTLED_SUCCESSFULLY:
                        currentTransaction.setStatusCode("S");
                        TransactionBatchMapper transactionBatchMapper = session.getMapper(TransactionBatchMapper.class);
                        String external_batch_id = response.getTransaction().getBatch().getBatchId();
                        Long batchId = transactionBatchMapper.findTransactionBatchIdForExternalId(currentTransaction.getGatewayConnectionId(), external_batch_id);
                        if (batchId == null) {

                            if (!StringUtils.equalsIgnoreCase(TransactionStatusEnum.SETTLED_SUCCESSFULLY.value(), response.getTransaction().getBatch().getSettlementState())) {
                                // If not finished settling just quit
                                return STOP;
                            }
                            TransactionBatch transactionBatch = new TransactionBatch(currentTransaction.getGatewayConnectionId(), external_batch_id, null);
                            transactionBatchMapper.insert(transactionBatch);
                            currentTransaction.setBatchId(transactionBatch.getId());
                            result ^= BATCH_CREATED;
                        } else {
                            currentTransaction.setBatchId(batchId);
                        }
                        break;
                    case VOIDED:
                        currentTransaction.setStatusCode("V");
                        result ^= SKIP;
                        break;
                    case CAPTURED_PENDING_SETTLEMENT:
                        if (transactionType == TransactionType.CHECK_SALE || transactionType == TransactionType.CHECK_SUB) {
                            // We would ignore this because these can get stuck floating
                            return SKIP;
                        } else {
                            // For other electronic transactions pending should mean that we have reached an open batch
                            return STOP;
                        }
                    case AUTHORIZED_PENDING_CAPTURE:
                        // Ignore this it is pending because it can't settle until captured
                        return SKIP;
                    case RETURNED_ITEM:
                    case CHARGEBACK:
                        // TODO at some point we should have a way to notify of this, remove error log
                        logger.error("Transaction update status was Chargeback\nClient: {}\nTransaction: {}",
                                clientId, currentTransaction.getId());
                        currentTransaction.setStatusCode("R");
                        result ^= SKIP;
                        break;
                    case CHARGEBACK_REVERSAL:
                        // TODO how the hell do we handle this case? Notify and remove error log
                        logger.error("Transaction update status was Chargeback Reversal\nClient: {}\nTransaction: {}",
                                clientId, currentTransaction.getId());
                        currentTransaction.setStatusCode("B");
                        result ^= SKIP;
                        break;
                    case REFUND_PENDING_SETTLEMENT:
                        return SKIP;
                    case GENERAL_ERROR:
                        logger.error("Update transaction status unexpected\nClient: {}\nResponse: {}",
                                clientId, GsonUtil.getGson().toJson(response));
                        return ERROR;
                    default:
//                    case FDS_AUTHORIZED_PENDING_REVIEW:
//                    case FDS_PENDING_REVIEW:
//                    case SETTLEMENT_ERROR:
//                    case COMMUNICATION_ERROR:
//                    case COULD_NOT_VOID:
//                    case APPROVED_REVIEW:
//                    case AUTHORIZED_PENDING_RELEASE:
//                    case UPDATING_SETTLEMENT:
//                    case PENDING_FINAL_SETTLEMENT:
//                    case PENDING_SETTLEMENT:
//                    case DECLINED:
//                    case EXPIRED:
//                    case FAILED_REVIEW:
//                    case UNDER_REVIEW:
                        // Unknown cases, I suspect some of them are for declined transactions
                      logger.error("Update transaction status was " + status.value());
                      currentTransaction.setStatusCode("E");
                      result ^= SKIP;

                }
                session.getMapper(TransactionMapper.class).updateTransactionStatusAndBatch(currentTransaction);
                result ^= UPDATED;
            } else {
                MDC.put("transaction", currentTransaction.getId());
                MDC.put("response", GsonUtil.getGson().toJson(response));
                logger.error("AuthorizeNet: Failed to get transaction status");
                MDC.remove("transaction");
                MDC.remove("response");
                return ERROR;
            }
        } else {
            ANetApiResponse errorResponse = controller.getErrorResponse();
            MDC.put("transaction", currentTransaction.getId());
            MDC.put("response", GsonUtil.getGson().toJson(errorResponse));
            logger.error("AuthorizeNet: Failed to get transaction status");
            MDC.remove("transaction");
            MDC.remove("response");
            return ERROR;
        }
        return result;
    }

    @Override
    public String createWithdraw(GatewayConnection gatewayConnection, Payment payment) {
        logger.error(String.format(Locale.US, "createWithdraw called on AuthorizeNet: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public String createTaxFee(GatewayConnection gatewayConnection, Payment payment) {
        logger.error(String.format(Locale.US, "createTaxFee called on AuthorizeNet: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public String createConsignmentFee(GatewayConnection gatewayConnection, Payment payment) {
        logger.error(String.format(Locale.US, "createConsignmentFee called on AuthorizeNet: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public BigDecimal getSubAccountBalance(GatewayConnection gatewayConnection) {
        logger.error(String.format(Locale.US, "getSubAccountBalance called on AuthorizeNet: %s", gatewayConnection.getId()));
        return null;
    }

    @Override
    public boolean checkTransactionBatch(SqlSession clientSession, GatewayConnection gatewayConnection, TransactionBatch transactionBatch) {
        GetBatchStatisticsRequest getRequest = new GetBatchStatisticsRequest();
        getRequest.setBatchId(transactionBatch.getExternalId());
        getRequest.setMerchantAuthentication(getMerchantAuth(gatewayConnection));

        GetBatchStatisticsController controller = new GetBatchStatisticsController(getRequest);
        controller.execute((gatewayConnection.getIsSandbox() ? Environment.SANDBOX : Environment.PRODUCTION));

        GetBatchStatisticsResponse response = controller.getApiResponse();
        if (response != null) {
            if (response.getMessages().getResultCode() == MessageTypeEnum.OK) {
                if (response.getBatch().getSettlementState().equals("settledSuccessfully")) {
                    transactionBatch.setSettledAt(new DateTime(response.getBatch().getSettlementTimeUTC().toGregorianCalendar().getTime()));
                    int count = 0;
                    BigDecimal total = BigDecimal.ZERO;
                    for (BatchStatisticType batchStatisticType : response.getBatch().getStatistics().getStatistic()) {
                        count += batchStatisticType.getChargeCount();
                        count += batchStatisticType.getRefundCount();
                        total = total.add(batchStatisticType.getChargeAmount()).subtract(batchStatisticType.getRefundAmount());
                    }
                    transactionBatch.setGatewayNetAmount(total);
                    transactionBatch.setGatewayTransactionCount(BigInteger.valueOf(count));
                    return true;
                } else {
                    return false;
                }
            } else {
                logger.error("Failed to get batch status: " + GsonUtil.getGson().toJson(response));
                return false;
            }
        } else {
            MDC.put("batchId", String.valueOf(transactionBatch.getId()));
            MDC.put("gatewayConnectionId", String.valueOf(gatewayConnection.getId()));
            MDC.put("errorResponse", GsonUtil.getGson().toJson(controller.getErrorResponse()));
            logger.error("AuthorizeNet: Failed to get batch");
            MDC.remove("batchId");
            MDC.remove("gatewayConnectionId");
            MDC.remove("errorResponse");
            return false;
        }
    }

    @Override
    public List<FeeEntry> getInternalFees(GatewayConnection gatewayConnection, Transaction currentTransaction, Long userBalanceId) {
        return new ArrayList<>();
    }

    private MerchantAuthenticationType getMerchantAuth(GatewayConnection gatewayConnection) {
        MerchantAuthenticationType merchantAuthenticationType = new MerchantAuthenticationType();
        merchantAuthenticationType.setName(gatewayConnection.getUsername());
        merchantAuthenticationType.setTransactionKey(gatewayConnection.getPrivateKey());
        return merchantAuthenticationType;
    }
}
