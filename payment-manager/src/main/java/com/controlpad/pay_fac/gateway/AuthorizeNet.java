package com.controlpad.pay_fac.gateway;


import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.gatewayconnection.SubAccountUser;
import com.controlpad.pay_fac.payment_info.TokenRequest;
import com.controlpad.pay_fac.report.gateway.GatewayTransaction;
import com.controlpad.pay_fac.tokenization.TokenizeCardResponse;
import com.controlpad.pay_fac.transaction.TransactionUtil;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.payman_common.address.Address;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.transaction.*;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.controlpad.payman_common.util.GsonUtil;
import net.authorize.Environment;
import net.authorize.ResponseCode;
import net.authorize.api.contract.v1.*;
import net.authorize.api.controller.*;
import org.apache.commons.lang3.StringUtils;
import org.apache.commons.lang3.math.NumberUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;
import org.springframework.http.HttpStatus;

import javax.xml.datatype.DatatypeFactory;
import java.math.BigDecimal;
import java.math.BigInteger;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.GregorianCalendar;
import java.util.List;

import static net.authorize.api.contract.v1.TransactionStatusEnum.fromValue;

public class AuthorizeNet implements Gateway {

    private final Logger logger = LoggerFactory.getLogger(AuthorizeNet.class);
    private final static String dbFormat = "yyyy-MM-dd HH:mm:ss";
    private IDUtil idUtil;

    private DateFormat dbFormatter;
    private DatatypeFactory dataTypeFactory;

    AuthorizeNet(IDUtil idUtil) {
        this.idUtil = idUtil;
        this.dbFormatter = new SimpleDateFormat(dbFormat);
        try {
            this.dataTypeFactory = DatatypeFactory.newInstance();
        } catch (Exception e) {
            logger.error("Failed to get dataTypeFactory for AuthorizeNet", e);
        }
    }

    @Override
    public Transaction saleCard(SqlSession session, GatewayConnection gatewayConnection, String remoteAddress,
                                        Transaction transaction, TransactionType transactionType) {
        // Create request
        CreateTransactionRequest apiRequest = new CreateTransactionRequest();
        apiRequest.setMerchantAuthentication(getMerchantAuth(gatewayConnection));
        apiRequest.setTransactionRequest(buildTransactionRequestForCardPayment(transaction));
        // Create controller, execute request
        CreateTransactionController controller = new CreateTransactionController(apiRequest);
        controller.execute((gatewayConnection.getIsSandbox() ? Environment.SANDBOX : Environment.PRODUCTION));
        // Handle response
        CreateTransactionResponse response = controller.getApiResponse();
        if (response != null) {
            transaction.setGatewayReferenceId(response.getTransactionResponse().getTransId());
            transaction.setGatewayConnectionId(gatewayConnection.getId());
            transaction.setSwiped(transaction.getCard().getEncMagstripe() != null || transaction.getCard().getMagstripe() != null);
            transaction.setCard(null);

            setStatusAndResult(response, transaction);

            TransactionUtil.insertTransaction(session, transaction, idUtil, transaction.getAffiliatePayouts());

            return transaction;
        } else {
            if (transaction.getCard() != null) {
                if (transaction.getCard().getNumber() != null) {
                    transaction.getCard().setNumber("****");
                }
                if (transaction.getCard().getCode() != null) {
                    transaction.getCard().setCode(null);
                }
            }
            if (controller.getErrorResponse() != null) {
                MDC.put("response", GsonUtil.getGson().toJson(controller.getErrorResponse()));
            }
            MDC.put("transaction", GsonUtil.getGson().toJson(transaction));
            MDC.put("gatewayResult", GsonUtil.getGson().toJson(controller.getResults()));
            logger.error("AuthorizeNet failed to charge card");
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
        }
    }

    @Override
    public Transaction saleCheck(SqlSession session, GatewayConnection gatewayConnection, Transaction transaction,
                                         String remoteAddress, TransactionType transactionType) {
        // Create request
        CreateTransactionRequest apiRequest = new CreateTransactionRequest();
        apiRequest.setMerchantAuthentication(getMerchantAuth(gatewayConnection));
        apiRequest.setTransactionRequest(buildTransactionRequestForCheckPayment(transaction));
        // Create controller, execute request
        CreateTransactionController controller = new CreateTransactionController(apiRequest);
        controller.execute((gatewayConnection.getIsSandbox() ? Environment.SANDBOX : Environment.PRODUCTION));
        // Handle response
        CreateTransactionResponse response = controller.getApiResponse();
        if (response != null && response.getMessages().getResultCode() == MessageTypeEnum.OK) {
            transaction.setGatewayReferenceId(response.getTransactionResponse().getTransId());
            transaction.setGatewayConnectionId(gatewayConnection.getId());
            transaction.setBankAccount(null);

            setStatusAndResult(response, transaction);

            TransactionUtil.insertTransaction(session, transaction, idUtil, transaction.getAffiliatePayouts());

            return transaction;
        } else {
            if (controller.getErrorResponse() != null) {
                MDC.put("gatewayResponse", GsonUtil.getGson().toJson(controller.getErrorResponse()));
            }
            MDC.put("transaction", GsonUtil.getGson().toJson(transaction));
            MDC.put("gatewayResult", GsonUtil.getGson().toJson(controller.getResults()));
            logger.error("AuthorizeNet failed to process e-check");

            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
        }
    }

    @Override
    public Transaction refundTransaction(GatewayConnection gatewayConnection, Transaction originalTransaction,
                                                 Transaction refund, String remoteAddress, String clientId) {
        // Create request
        CreateTransactionRequest apiRequest = new CreateTransactionRequest();
        apiRequest.setMerchantAuthentication(getMerchantAuth(gatewayConnection));

        GetTransactionDetailsResponse detailsResponse = getTransactionDetails(gatewayConnection, originalTransaction);

        CreditCardType creditCardType = new CreditCardType();
        creditCardType.setCardNumber(detailsResponse.getTransaction().getPayment().getCreditCard().getCardNumber());
        creditCardType.setExpirationDate(detailsResponse.getTransaction().getPayment().getCreditCard().getExpirationDate());
        PaymentType paymentType = new PaymentType();
        paymentType.setCreditCard(creditCardType);

        if ((StringUtils.equals(originalTransaction.getStatusCode(), "A") || StringUtils.equals(originalTransaction.getStatusCode(), "P")) &&
                (refund.getAmount().compareTo(originalTransaction.getAmount()) == 0)) {
            // Attempt a void because transaction is listed as still pending
            if (voidTransaction(gatewayConnection, originalTransaction, paymentType, remoteAddress)) {
                refund.setGatewayConnectionId(gatewayConnection.getId());
                refund.updateResultAndCode(TransactionResult.Success.getResultCode());
                refund.setStatusCode("S");
                refund.setTransactionType(TransactionType.VOID.slug);
                return refund;
            }
        }

        TransactionRequestType txnRequest = new TransactionRequestType();

        TransactionType transactionType = TransactionType.findBySlug(originalTransaction.getTransactionType());
        if (transactionType == TransactionType.CREDIT_CARD_SALE || transactionType == TransactionType.CREDIT_CARD_SUB ||
                transactionType == TransactionType.DEBIT_CARD_SALE || transactionType == TransactionType.DEBIT_CARD_SUB) {

            txnRequest.setPayment(paymentType);
        }

        txnRequest.setTransactionType(TransactionTypeEnum.REFUND_TRANSACTION.value());
        txnRequest.setRefTransId(originalTransaction.getGatewayReferenceId());
        txnRequest.setAmount(refund.getAmount());


        apiRequest.setTransactionRequest(txnRequest);

        CreateTransactionController controller = new CreateTransactionController(apiRequest);
        controller.execute((gatewayConnection.getIsSandbox() ? Environment.SANDBOX : Environment.PRODUCTION));

        CreateTransactionResponse response = controller.getApiResponse();
        if (response != null) {
            refund.setGatewayReferenceId(response.getTransactionResponse().getTransId());
            refund.setGatewayConnectionId(gatewayConnection.getId());

            if (response.getMessages().getResultCode() == MessageTypeEnum.OK) {
                if (response.getTransactionResponse().getResponseCode().equals("1")) {
                    refund.setStatusCode("P");
                    refund.updateResultAndCode(TransactionResult.Success.getResultCode());
                } else {
                    // TODO parse errors?
                    refund.setStatusCode("D");
                    refund.updateResultAndCode(TransactionResult.Declined.getResultCode());
                }
                return refund;
            } else if(response.getTransactionResponse().getErrors() != null &&
                    response.getTransactionResponse().getErrors().getError() != null &&
                    response.getTransactionResponse().getErrors().getError().size() > 0){

                    TransactionResponse transactionResponse = response.getTransactionResponse();
                    List<net.authorize.api.contract.v1.TransactionResponse.Errors.Error> errors = transactionResponse.getErrors().getError();
                    if("54".equals(errors.get(0).getErrorCode())){
                        refund.updateResultAndCode(TransactionResult.Transaction_Not_Settled.getResultCode());
                        refund.setStatusCode("D");
                    }else{
                        MDC.put("gatewayResponse", GsonUtil.getGson().toJson(transactionResponse));
                        MDC.put("transactionId", originalTransaction.getId());
                        logger.error("Authorize net failed to refund");
                        throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
                    }
            }else{
                MDC.put("gatewayMessages", GsonUtil.getGson().toJson(response.getMessages().getMessage()));
                MDC.put("transactionId", originalTransaction.getId());
                logger.error("AuthorizeNet failed to refund");
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
            }
            return refund;
        } else {
            if (controller.getErrorResponse() != null) {
                MDC.put("gatewayMessages", GsonUtil.getGson().toJson(controller.getErrorResponse().getMessages()));
            }
            MDC.put("transactionId", originalTransaction.getId());
            MDC.put("", GsonUtil.getGson().toJson(controller.getResults()));
            logger.error("AuthorizeNet failed to refund");

            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
        }
    }

    @Override
    public TokenizeCardResponse tokenizeCard(TokenRequest tokenRequest, GatewayConnection gatewayConnection, String remoteAddress) {
        if (tokenRequest.getAddress() == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "address required");
        }
        if (tokenRequest.getAddress().getLine1() == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "address.line1 required");
        }
        if (tokenRequest.getAddress().getPostalCode() == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "address.postalCode required");
        }
        if (tokenRequest.getPayerId() == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "payerId required");
        }
        CardType cardType = tokenRequest.getType();
        if (cardType == CardType.UNKNOWN) {
            return new TokenizeCardResponse(3, "Error: Invalid TokenRequest Number");
        }

        if(tokenRequest.getGatewayCustomerId() == null){
            CustomerPaymentProfileType paymentProfile = setupPaymentProfile(tokenRequest);

            CustomerProfileType customerProfile = new CustomerProfileType();
            if (tokenRequest.getAddress().getEmail() != null) {
                customerProfile.setEmail(tokenRequest.getAddress().getEmail());
            }
            customerProfile.setMerchantCustomerId(tokenRequest.getPayerId());
            customerProfile.getPaymentProfiles().add(paymentProfile);

            CreateCustomerProfileRequest apiRequest = new CreateCustomerProfileRequest();
            apiRequest.setMerchantAuthentication(getMerchantAuth(gatewayConnection));
            apiRequest.setProfile(customerProfile);
            apiRequest.setValidationMode(ValidationModeEnum.TEST_MODE);

            CreateCustomerProfileController controller = new CreateCustomerProfileController(apiRequest);
            CreateCustomerProfileResponse response = controller.executeWithApiResponse(
                    gatewayConnection.getIsSandbox() ? Environment.SANDBOX : Environment.PRODUCTION);

            if(response != null) {
                switch (response.getMessages().getResultCode()) {
                    case OK:
                        return new TokenizeCardResponse(
                                new TokenizeCardResponse.CardToken(response.getCustomerPaymentProfileIdList().getNumericString().get(0),
                                        null, tokenRequest.getExpireDate(), tokenRequest.getCard().getNumber(),
                                        cardType.getSlug(), response.getCustomerProfileId())
                        );
                    case ERROR:
                        if ("E00039".equals(response.getMessages().getMessage().get(0).getCode())) {
                            // Duplicate creation attemp, set gatewayCustomerId and move on
                            tokenRequest.setGatewayCustomerId(response.getMessages().getMessage().get(0).getText().replaceAll("([A-Za-z]*)\\s*\\.*", ""));
                            break;
                        }
                        return new TokenizeCardResponse(10, "Error: create new customer failed: " + GsonUtil.getGson().toJson(response));
                    default:
                        MDC.put("gatewayResponse", GsonUtil.getGson().toJson(response));
                        logger.error("AuthorizeNet unexpected result code");
                        throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
                }
            }else{
                if (controller.getErrorResponse() != null) {
                    MDC.put("gatewayMessages", GsonUtil.getGson().toJson(controller.getErrorResponse().getMessages()));
                }
                MDC.put("gatewayResults", GsonUtil.getGson().toJson(controller.getResults()));
                logger.error("AuthorizeNet failed to save card");

                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Unexpected error");
            }
        }
        if(tokenRequest.getCurrentToken() == null) {

            CustomerPaymentProfileType paymentProfile = setupPaymentProfile(tokenRequest);

            CreateCustomerPaymentProfileRequest apiRequest = new CreateCustomerPaymentProfileRequest();
            apiRequest.setMerchantAuthentication(getMerchantAuth(gatewayConnection));
            apiRequest.setCustomerProfileId(tokenRequest.getGatewayCustomerId());
            apiRequest.setPaymentProfile(paymentProfile);
            apiRequest.setValidationMode(ValidationModeEnum.TEST_MODE);

            CreateCustomerPaymentProfileController controller = new CreateCustomerPaymentProfileController(apiRequest);
            controller.execute(gatewayConnection.getIsSandbox() ? Environment.SANDBOX : Environment.PRODUCTION);
            CreateCustomerPaymentProfileResponse response = controller.getApiResponse();
            if(response != null) {
                switch (response.getMessages().getResultCode()) {
                    case OK:
                        return new TokenizeCardResponse(
                                new TokenizeCardResponse.CardToken(response.getCustomerPaymentProfileId(), null, tokenRequest.getExpireDate(), tokenRequest.getCard().getNumber(), cardType.getSlug(), response.getCustomerProfileId())
                        );
                    case ERROR:
                        if ("E00039".equals(response.getMessages().getMessage().get(0).getCode())) {
                            // Duplicate creation attemp, set token and move on
                            tokenRequest.setCurrentToken(response.getCustomerPaymentProfileId());
                            break;
                        }
                        return new TokenizeCardResponse(10, "Error: create new payment profile failed: " + GsonUtil.getGson().toJson(response));
                    default:
                        MDC.put("gatewayResponse", GsonUtil.getGson().toJson(response));
                        logger.error("AuthorizeNet save card unexpected result");
                        throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
                }
            }else{
                if (controller.getErrorResponse() != null) {
                    MDC.put("gatewayMessages", GsonUtil.getGson().toJson(controller.getErrorResponse().getMessages()));
                }
                MDC.put("gatewayResults", GsonUtil.getGson().toJson(controller.getResults()));
                logger.error("AuthorizeNet failed to save card");
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Unexpected error");
            }
        }
        // If we are updating known data
        CustomerPaymentProfileExType paymentProfile = setupPaymentProfile(tokenRequest);
        paymentProfile.setCustomerPaymentProfileId(tokenRequest.getCurrentToken());

        UpdateCustomerPaymentProfileRequest apiRequest = new UpdateCustomerPaymentProfileRequest();
        apiRequest.setMerchantAuthentication(getMerchantAuth(gatewayConnection));
        apiRequest.setCustomerProfileId(tokenRequest.getGatewayCustomerId());
        apiRequest.setPaymentProfile(paymentProfile);
        apiRequest.setValidationMode(ValidationModeEnum.TEST_MODE);

        UpdateCustomerPaymentProfileController controller = new UpdateCustomerPaymentProfileController(apiRequest);
        controller.execute(gatewayConnection.getIsSandbox() ? Environment.SANDBOX : Environment.PRODUCTION);
        UpdateCustomerPaymentProfileResponse response = controller.getApiResponse();

        if(response != null) {
            switch (response.getMessages().getResultCode()) {
                case OK:
                    return new TokenizeCardResponse(
                            new TokenizeCardResponse.CardToken(tokenRequest.getCurrentToken(), null, tokenRequest.getExpireDate(), tokenRequest.getCard().getNumber(), cardType.getSlug(), tokenRequest.getGatewayCustomerId())
                    );
                case ERROR:
                    return new TokenizeCardResponse(10, "Error: create new payment profile failed: " + GsonUtil.getGson().toJson(response));
                default:
                    logger.error("Unexpected response result code: " + GsonUtil.getGson().toJson(response));
                    throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Unexpected response: " + GsonUtil.getGson().toJson(response));
            }
        }else{
            if (controller.getErrorResponse() != null) {
                MDC.put("gatewayMessages", GsonUtil.getGson().toJson(controller.getErrorResponse().getMessages()));
            }
            MDC.put("gatewayResults", GsonUtil.getGson().toJson(controller.getResults()));
            logger.error("AuthorizeNet failed to save card");
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Unexpected error");
        }
    }

    @Override
    public Boolean isAccountSame(GatewayConnection currentConnection, GatewayConnection newConnection, SqlSession session) {
        if(currentConnection == null){
            return false;
        }
        return StringUtils.equals(currentConnection.getUsername(), newConnection.getUsername());
    }

    @Override
    public boolean checkCredentials(GatewayConnection gatewayConnection) {
        // the goal here is to verify that the transaction api is enabled, this call will reject otherwise
        // Trying to not really pull records but have an authorized call
        try {
            GetSettledBatchListRequest batchRequest = new GetSettledBatchListRequest();
            batchRequest.setFirstSettlementDate(
                    dataTypeFactory.newXMLGregorianCalendar(
                            new GregorianCalendar(3100, 1, 1)));
            batchRequest.setLastSettlementDate(
                    dataTypeFactory.newXMLGregorianCalendar(
                            new GregorianCalendar(3100, 1, 1)));
            batchRequest.setMerchantAuthentication(getMerchantAuth(gatewayConnection));
            GetSettledBatchListController batchController = new GetSettledBatchListController(batchRequest);
            batchController.execute((gatewayConnection.getIsSandbox() ? Environment.SANDBOX : Environment.PRODUCTION));
            GetSettledBatchListResponse batchResponse = batchController.getApiResponse();
            if (batchResponse.getMessages().getResultCode() == MessageTypeEnum.OK) {
                return true;
            }
            // TODO should we return a specific message for this case?
//            "message": [
//              {
//                "code": "E00011",
//                    "text": "Access denied. You do not have permissions to call the Transaction Details API."
//              }
//            ]
        } catch (Exception e) {
            logger.error("checkCredentials exception", e);
        }
        return false;
    }

    @Override
    public CommonResponse createSubAccount(String clientName, SqlSession clientSession, GatewayConnection masterConnection, SubAccountUser subAccountUser) {
        throw new ResponseException(HttpStatus.BAD_REQUEST, "Create sub account not supported for AuthorizeNet");
    }

    @Override
    public boolean updateSubAccount(GatewayConnection gatewayConnection, SubAccountUser subAccountUser) {
        return false;
    }

    @Override
    public Transaction importTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction) {
        GetTransactionDetailsResponse response = getTransactionDetails(gatewayConnection, transaction);

        TransactionDetailsType transactionDetailsType = response.getTransaction();
        String statusCode;
        Integer resultCode;
        switch (transactionDetailsType.getTransactionType()) {
            case "authOnlyTransaction":
                if (transactionDetailsType.getTransactionStatus().equalsIgnoreCase("authorizedPendingCapture")) {
                    statusCode = "A"; // Accepted
                } else {
                    statusCode = "P"; // Captured
                }
                break;
            case "authCaptureTransaction":
            case "captureOnlyTransaction":
                if (transaction.getTransactionType().equalsIgnoreCase(TransactionType.REFUND.slug)){
                    throw new ResponseException(HttpStatus.BAD_REQUEST, "Cannot import a refund as a sale transaction");
                }
                statusCode = "P"; // Pending 'Sale' or 'Refund'
                break;
            case "refundTransaction":
                if (!transaction.getTransactionType().equalsIgnoreCase(TransactionType.REFUND.slug)){
                    throw new ResponseException(HttpStatus.BAD_REQUEST, "Cannot import a sale as a refund transaction");
                }
                statusCode = "P"; // Pending 'Sale' or 'Refund'
                break;
            default:
                MDC.put("gatewayTransactionType", transactionDetailsType.getTransactionType());
                MDC.put("gatewayTransactionId", transaction.getGatewayReferenceId());
                MDC.put("message", "Transaction type unexpected");
                logger.error("AuthorizeNet import failed");
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Unexpected transaction type");
        }

        switch (transactionDetailsType.getResponseCode()) {
            case 1: // Approved
                resultCode = TransactionResult.Success.getResultCode();
                break;
            case 2: // Declined
            case 4: // Held for Review
                resultCode = TransactionResult.Declined.getResultCode();
                throw new ResponseException(HttpStatus.BAD_REQUEST, "Not accepted");
            default:
                MDC.put("gatewayTransactionResponseCode", String.valueOf(transactionDetailsType.getResponseCode()));
                MDC.put("message", "unexpected code during import");
                logger.error("AuthorizeNet import failed");
            case 3: // Error
                throw new ResponseException(HttpStatus.BAD_REQUEST, "Transaction had an error");
        }

        String description = (transactionDetailsType.getOrder() != null ? transactionDetailsType.getOrder().getDescription() : null);
        BigDecimal taxAmount = (transactionDetailsType.getTax() != null ? transactionDetailsType.getTax().getAmount() : BigDecimal.ZERO);
        BigDecimal shippingAmount = (transactionDetailsType.getShipping() != null ? transactionDetailsType.getShipping().getAmount() : BigDecimal.ZERO);
        String accountHolder = (transactionDetailsType.getBillTo() != null ?
                String.format("%s %s", transactionDetailsType.getBillTo().getFirstName(), transactionDetailsType.getBillTo().getLastName()) :
                null
        );
        Transaction saveTransaction = new Transaction(null,
            transaction.getPayeeUserId(), transaction.getPayerUserId(), transaction.getTeamId(), transaction.getGatewayReferenceId(),
            transaction.getTransactionType(), transactionDetailsType.getAuthAmount(), taxAmount, shippingAmount,
                statusCode, resultCode,gatewayConnection.getId(), description, accountHolder
        );

        if (transactionDetailsType.getProduct() != null) {
            saveTransaction.setSwiped(transactionDetailsType.getProduct().equalsIgnoreCase("Card Present"));
        } else {
            saveTransaction.setSwiped(false);
        }

        TransactionUtil.insertTransaction(sqlSession, saveTransaction, idUtil, null);

        return saveTransaction;
    }

    @Override
    public Transaction captureTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction originalTransaction, String clientId) {
        // Create request
        CreateTransactionRequest apiRequest = new CreateTransactionRequest();
        apiRequest.setMerchantAuthentication(getMerchantAuth(gatewayConnection));

        TransactionRequestType txnRequest = new TransactionRequestType();
        txnRequest.setTransactionType(TransactionTypeEnum.PRIOR_AUTH_CAPTURE_TRANSACTION.value());
        txnRequest.setRefTransId(originalTransaction.getGatewayReferenceId());

        apiRequest.setTransactionRequest(txnRequest);

        CreateTransactionController controller = new CreateTransactionController(apiRequest);
        controller.execute((gatewayConnection.getIsSandbox() ? Environment.SANDBOX : Environment.PRODUCTION));

        if (controller.getResultCode() == MessageTypeEnum.OK) {
            CreateTransactionResponse response = controller.getApiResponse();

            if (response.getTransactionResponse().getResponseCode().equals("1")) {
                originalTransaction.setStatusCode("P");
                sqlSession.getMapper(TransactionMapper.class).updateTransactionStatusOrderId(originalTransaction);
                return originalTransaction;
            } else {
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Unexpected error");
            }
        } else {
            if (controller.getErrorResponse() != null) {
                MDC.put("gatewayResponse", GsonUtil.getGson().toJson(controller.getErrorResponse()));
            }
            MDC.put("gatewayResults", GsonUtil.getGson().toJson(controller.getResults()));
            MDC.put("transactionId", originalTransaction.getId());
            logger.error("AuthorizeNet failed to capture transaction");
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
        }
    }

    @Override
    public Object getTransaction(GatewayConnection gatewayConnection, Transaction transaction) {
        CreateTransactionRequest apiRequest = new CreateTransactionRequest();
        apiRequest.setMerchantAuthentication(getMerchantAuth(gatewayConnection));

        return getTransactionDetails(gatewayConnection, transaction).getTransaction();
    }

    @Override
    public List<TransactionBatch> searchGatewayBatches(SqlSession clientSession, GatewayConnection gatewayConnection,
                                                       DateTime startDate, DateTime endDate, BigInteger page, BigInteger limit) {
        GetSettledBatchListRequest request = new GetSettledBatchListRequest();
        request.setMerchantAuthentication(getMerchantAuth(gatewayConnection));
        request.setIncludeStatistics(true);
        if (startDate != null) {
            request.setFirstSettlementDate(dataTypeFactory.newXMLGregorianCalendar(startDate.toGregorianCalendar()));
        }
        if (endDate != null) {
            request.setLastSettlementDate(dataTypeFactory.newXMLGregorianCalendar(endDate.toGregorianCalendar()));
        }
        if (startDate != null && endDate != null && startDate.plusMonths(1).isBefore(endDate.getMillis())) {
            // Can't pull authorize net farther than 1 month
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Authorize net won't return more than 1 month of records");
        }

        GetSettledBatchListController controller = new GetSettledBatchListController(request);
        controller.execute(gatewayConnection.getIsSandbox() ? Environment.SANDBOX : Environment.PRODUCTION);

        if (controller.getResultCode() == MessageTypeEnum.OK) {
            if (controller.getApiResponse().getBatchList() == null) {
                return new ArrayList<>(0);
            }
            List<BatchDetailsType> resultBatches = controller.getApiResponse().getBatchList().getBatch();
            List<TransactionBatch> gatewayBatches = new ArrayList<>(limit.intValue());
            // Fake paging because the api doesn't support it
            BigInteger offset = page.subtract(BigInteger.ONE).multiply(limit);
            if (offset.intValue() >= resultBatches.size()) {
                return new ArrayList<>(0);
            }
            int endPosition = offset.add(limit).intValue();
            if (endPosition > resultBatches.size()) {
                endPosition = resultBatches.size();
            }
            for (BatchDetailsType resultBatch : resultBatches.subList(offset.intValue(), endPosition)) {
                int count = 0;
                BigDecimal total = BigDecimal.ZERO;
                for (BatchStatisticType batchStatisticType : resultBatch.getStatistics().getStatistic()) {
                    count += batchStatisticType.getChargeCount();
                    count += batchStatisticType.getRefundCount();
                    total = total.add(batchStatisticType.getChargeAmount()).subtract(batchStatisticType.getRefundAmount());
                }
                gatewayBatches.add(new TransactionBatch(resultBatch.getBatchId(), total,
                        new DateTime(resultBatch.getSettlementTimeLocal().toGregorianCalendar().getTime()), BigInteger.valueOf(count)));
            }
            return gatewayBatches;
        } else {
            if (controller.getErrorResponse() != null) {
                MDC.put("gatewayResponse", GsonUtil.getGson().toJson(controller.getErrorResponse()));
            }
            MDC.put("gatewayRequest", GsonUtil.getGson().toJson(request));
            MDC.put("gatewayResults", GsonUtil.getGson().toJson(controller.getResults()));
            logger.error("AuthorizeNet failed to get gateway batches");
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Unexpected error");
        }
    }

    @Override
    public List<GatewayTransaction> searchTransactions(SqlSession clientSession, GatewayConnection gatewayConnection,
                                                       String externalBatchId, BigInteger page, BigInteger limit) {
        Paging paging = new Paging();
        paging.setLimit(limit.intValue());
        paging.setOffset(page.intValue());
        GetTransactionListRequest getTransactionListRequest = new GetTransactionListRequest();
        getTransactionListRequest.setMerchantAuthentication(getMerchantAuth(gatewayConnection));
        getTransactionListRequest.setBatchId(externalBatchId);
        getTransactionListRequest.setPaging(paging);

        GetTransactionListController controller = new GetTransactionListController(getTransactionListRequest);
        controller.execute(gatewayConnection.getIsSandbox() ? Environment.SANDBOX : Environment.PRODUCTION);
        if (controller.getResultCode() == MessageTypeEnum.OK) {
            if (controller.getApiResponse().getTransactions() == null) {
                return new ArrayList<>(0);
            }
            List<GatewayTransaction> gatewayTransactions = new ArrayList<>(controller.getApiResponse().getTotalNumInResultSet());
            for (TransactionSummaryType transactionSummaryType : controller.getApiResponse().getTransactions().getTransaction()) {
                gatewayTransactions.add(parseGatewayTransaction(transactionSummaryType));
            }
            return gatewayTransactions;
        } else {
            if (controller.getErrorResponse() != null) {
                MDC.put("gatewayResponse", GsonUtil.getGson().toJson(controller.getErrorResponse()));
            }
            MDC.put("gatewayRequest", GsonUtil.getGson().toJson(getTransactionListRequest));
            MDC.put("gatewayResults", GsonUtil.getGson().toJson(controller.getResults()));
            logger.error("AuthorizeNet failed to search transactions");
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Unexpected error");
        }
    }

    private GatewayTransaction parseGatewayTransaction(TransactionSummaryType transactionSummaryType) {
        String type;
        String status;
        switch (transactionSummaryType.getTransactionStatus()) {
            case "settledSuccessfully":
                type = "Sale";
                status = "Settled";
                break;
            case "authorizedPendingCapture":
            case "capturedPendingSettlement":
            case "approvedReview":
                type = "Sale";
                status = "Pending";
                break;
            case "declined":
                type = "Sale";
                status = "Declined";
                break;
            case "voided":
                type = "Sale";
                status = "Voided";
                break;
            case "refundSettledSuccessfully":
                type = "Refund";
                status = "Settled";
                break;
            case "refundPendingSettlement":
                type = "Refund";
                status = "Pending";
            default:
                type = "Unknown";
                status = transactionSummaryType.getTransactionStatus();

        }
        return new GatewayTransaction(transactionSummaryType.getTransId(),
                dbFormatter.format(transactionSummaryType.getSubmitTimeLocal().toGregorianCalendar().getTime()),
                transactionSummaryType.getSettleAmount(), null, type,
                String.format("%s %s", transactionSummaryType.getFirstName(), transactionSummaryType.getLastName()),
                null, status, null);
    }

    private boolean voidTransaction(GatewayConnection gatewayConnection, Transaction transaction,
                                    PaymentType paymentType, String remoteAddress) {
        CreateTransactionRequest apiRequest = new CreateTransactionRequest();
        apiRequest.setMerchantAuthentication(getMerchantAuth(gatewayConnection));

        TransactionRequestType txnRequest = new TransactionRequestType();
        txnRequest.setTransactionType(TransactionTypeEnum.VOID_TRANSACTION.value());
        txnRequest.setRefTransId(transaction.getGatewayReferenceId());
        txnRequest.setPayment(paymentType);
        apiRequest.setTransactionRequest(txnRequest);

        CreateTransactionController controller = new CreateTransactionController(apiRequest);
        controller.execute((gatewayConnection.getIsSandbox() ? Environment.SANDBOX : Environment.PRODUCTION));

        CreateTransactionResponse response = controller.getApiResponse();

        if (response != null) {
            if (response.getMessages().getResultCode() == MessageTypeEnum.OK) {
                return response.getTransactionResponse().getResponseCode().equals("1");
            } else {
                if (response.getMessages().getMessage().isEmpty() ||
                        !StringUtils.equals("E00027", response.getMessages().getMessage().get(0).getCode())) {
                    MDC.put("gatewayResponse", GsonUtil.getGson().toJson(response));
                    logger.error("AuthorizeNet failed to void transaction");
                }
                return false;
            }
        } else {
            if (controller.getErrorResponse() != null) {
                MDC.put("gatewayResponse", GsonUtil.getGson().toJson(controller.getErrorResponse()));
            }
            MDC.put("gatewayRequest", GsonUtil.getGson().toJson(txnRequest));
            MDC.put("gatewayResults", GsonUtil.getGson().toJson(controller.getResults()));
            logger.error("AuthorizeNet failed to void transaction");
            return false;
        }
    }

    private GetTransactionDetailsResponse getTransactionDetails(GatewayConnection gatewayConnection, Transaction transaction) {
        GetTransactionDetailsRequest getRequest = new GetTransactionDetailsRequest();
        getRequest.setMerchantAuthentication(getMerchantAuth(gatewayConnection));
        getRequest.setTransId(transaction.getGatewayReferenceId());

        GetTransactionDetailsController controller = new GetTransactionDetailsController(getRequest);
        controller.execute(gatewayConnection.getIsSandbox() ? Environment.SANDBOX : Environment.PRODUCTION);

        if (controller.getResultCode() == MessageTypeEnum.OK) {
            GetTransactionDetailsResponse detailsResponse = controller.getApiResponse();
            if (detailsResponse.getTransaction() == null) {
                MDC.put("gatewayRequest", GsonUtil.getGson().toJson(getRequest));
                MDC.put("gatewayResponse", GsonUtil.getGson().toJson(detailsResponse));
                logger.error("AuthorizeNet failed to get transaction details");
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
            }
            return detailsResponse;
        } else {
            if (controller.getErrorResponse() != null) {
                MDC.put("gatewayResponse", GsonUtil.getGson().toJson(controller.getErrorResponse()));
            }
            MDC.put("gatewayRequest", GsonUtil.getGson().toJson(getRequest));
            MDC.put("gatewayResults", GsonUtil.getGson().toJson(controller.getResults()));
            logger.error("AuthorizeNet failed to get transaction details");

            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
        }
    }

    @Override
    public Transaction updateTransactionStatus(SqlSession session, Transaction currentTransaction, GatewayConnection gatewayConnection, String remoteAddress) {
        GetTransactionDetailsResponse response = getTransactionDetails(gatewayConnection, currentTransaction);

        TransactionStatusEnum status = fromValue(response.getTransaction().getTransactionStatus());
        switch (status) {
            case SETTLED_SUCCESSFULLY:
                currentTransaction.setStatusCode("S");
            break;
            case VOIDED:
                currentTransaction.setStatusCode("V");
                break;
            case CHARGEBACK:
            case RETURNED_ITEM:
                currentTransaction.setStatusCode("R");
                break;
            case CHARGEBACK_REVERSAL:
                currentTransaction.setStatusCode("B");
                break;
            case GENERAL_ERROR:
            case COMMUNICATION_ERROR:
            case DECLINED:
            case SETTLEMENT_ERROR:
                currentTransaction.setStatusCode("E");
            case UPDATING_SETTLEMENT:
            case PENDING_FINAL_SETTLEMENT:
            case PENDING_SETTLEMENT:
            case CAPTURED_PENDING_SETTLEMENT:
            case AUTHORIZED_PENDING_CAPTURE:
            case APPROVED_REVIEW:
            case AUTHORIZED_PENDING_RELEASE:
            case COULD_NOT_VOID:
            case EXPIRED:
            case FAILED_REVIEW:
            case FDS_AUTHORIZED_PENDING_REVIEW:
            case FDS_PENDING_REVIEW:
            case REFUND_PENDING_SETTLEMENT:
            case REFUND_SETTLED_SUCCESSFULLY:
            case UNDER_REVIEW:
            default:

        }
        if (response.getTransaction().getBatch() != null) {
            TransactionBatch batch = session.getMapper(TransactionBatchMapper.class).findForExternalId(gatewayConnection.getId(),
                    response.getTransaction().getBatch().getBatchId());
            if (batch != null) {
                currentTransaction.setBatchId(batch.getId());
            }
        }
        session.getMapper(TransactionMapper.class).updateTransactionStatusAndBatch(currentTransaction);

        return null;
    }

    private TransactionRequestType buildTransactionRequestForCardPayment(Transaction transaction) {
        TransactionRequestType txnRequest = new TransactionRequestType();

        if (StringUtils.equalsIgnoreCase(transaction.getStatusCode(), "a")) {
            txnRequest.setTransactionType(TransactionTypeEnum.AUTH_ONLY_TRANSACTION.value());
        } else {
            txnRequest.setTransactionType(TransactionTypeEnum.AUTH_CAPTURE_TRANSACTION.value());
        }

        setPayerUserId(txnRequest, transaction);

//        if (transaction.getOrderId() != null) {
//            OrderExType order = new OrderExType();
//            order.setInvoiceNumber(transaction.getOrderId());
//            txnRequest.setOrder(order);
//        }
        if (NumberUtils.isNumber(transaction.getPayeeUserId())) {
            txnRequest.setTerminalNumber(transaction.getPayeeUserId());
        }
        if (transaction.getCard().getToken() != null) {
            if (transaction.getCard().getGatewayCustomerId() == null) {
                throw new ResponseException(HttpStatus.BAD_REQUEST, "gatewayCustomerId missing");
            }
            CustomerProfilePaymentType profileToCharge = new CustomerProfilePaymentType();
            profileToCharge.setCustomerProfileId(transaction.getCard().getGatewayCustomerId());
            PaymentProfile paymentProfile = new PaymentProfile();
            paymentProfile.setPaymentProfileId(transaction.getCard().getToken());
            profileToCharge.setPaymentProfile(paymentProfile);
            txnRequest.setProfile(profileToCharge);
        }else{
            txnRequest.setPayment(buildPaymentTypeForCardPayment(transaction));
            // Set billing info
            CustomerAddressType customerAddressType = setUpAddress(transaction.getBillingAddress());
            txnRequest.setBillTo(customerAddressType);
        }

        if (transaction.getShippingAddress() != null) {
            txnRequest.setShipTo(buildShippingAddress(transaction));
        }
        txnRequest.setPoNumber(transaction.getPoNumber());
        // Set amount
        txnRequest.setAmount(transaction.getAmount());
        // Set tax
        if (transaction.getSalesTax() != null) {
            ExtendedAmountType taxAmount = new ExtendedAmountType();
            taxAmount.setAmount(transaction.getSalesTax());
            txnRequest.setTax(taxAmount);
        }
        // Set shipping
        if (transaction.getShipping() != null) {
            ExtendedAmountType shippingAmount = new ExtendedAmountType();
            shippingAmount.setAmount(transaction.getShipping());
            txnRequest.setShipping(shippingAmount);
        }

        //TODO support this?
        //txnRequest.setShipTo();

        return txnRequest;
    }

    private void setPayerUserId(TransactionRequestType txnRequest, Transaction transaction) {
        if (StringUtils.isNotBlank(transaction.getPayerUserId())) {
            CustomerDataType customer = new CustomerDataType();
            customer.setId(transaction.getPayerUserId());
            customer.setType(CustomerTypeEnum.INDIVIDUAL);
            if (transaction.getBillingAddress() != null && StringUtils.isNotBlank(transaction.getBillingAddress().getEmail())) {
                customer.setEmail(transaction.getBillingAddress().getEmail());
            }
            txnRequest.setCustomer(customer);
        }
    }

    private TransactionRequestType buildTransactionRequestForCheckPayment(Transaction transaction) {
        TransactionRequestType txnRequest = new TransactionRequestType();

        setPayerUserId(txnRequest, transaction);

        if (StringUtils.equalsIgnoreCase(transaction.getStatusCode(), "a")) {
            txnRequest.setTransactionType(TransactionTypeEnum.AUTH_ONLY_TRANSACTION.value());
        } else {
            txnRequest.setTransactionType(TransactionTypeEnum.AUTH_CAPTURE_TRANSACTION.value());
        }
        txnRequest.setTerminalNumber(transaction.getPayeeUserId());
        txnRequest.setPayment(buildPaymentTypeForCheckPayment(transaction));
        txnRequest.setPoNumber(transaction.getPoNumber());
        // Set amount
        txnRequest.setAmount(transaction.getAmount());
        // Set tax
        if (transaction.getSalesTax() != null) {
            ExtendedAmountType taxAmount = new ExtendedAmountType();
            taxAmount.setAmount(transaction.getSalesTax());
            txnRequest.setTax(taxAmount);
        }
        // Set billing info
        CustomerAddressType customerAddressType = setUpAddress(transaction.getBillingAddress());
        txnRequest.setBillTo(customerAddressType);
        // Set shipping
        if (transaction.getShipping() != null) {
            ExtendedAmountType shippingAmount = new ExtendedAmountType();
            shippingAmount.setAmount(transaction.getShipping());
            txnRequest.setShipping(shippingAmount);
        }

        if (transaction.getShippingAddress() != null) {
            txnRequest.setShipTo(buildShippingAddress(transaction));
        }

        return txnRequest;
    }

    private PaymentType buildPaymentTypeForCheckPayment(Transaction transaction) {
        PaymentType paymentType = new PaymentType();

        BankAccountType bankAccountType = new BankAccountType();
        bankAccountType.setAccountNumber(transaction.getBankAccount().getNumber());
        bankAccountType.setRoutingNumber(transaction.getBankAccount().getRouting());
        bankAccountType.setAccountType(BankAccountTypeEnum.fromValue(transaction.getBankAccount().getType()));
        if (transaction.getBankAccount().getName() != null) {
            bankAccountType.setNameOnAccount(transaction.getBankAccount().getName());
        } else {
            bankAccountType.setNameOnAccount(transaction.getAccountHolder());
        }

        paymentType.setBankAccount(bankAccountType);
        return paymentType;
    }

    private PaymentType buildPaymentTypeForCardPayment(Transaction transaction) {
        PaymentType paymentType = new PaymentType();
        if (transaction.getCard().getEncMagstripe() != null) {
            EncodingType encodingType = EncodingType.fromValue(transaction.getCard().getEncMagstripe()); // TODO not sure how to do this either
            KeyValue keyValue = new KeyValue();
            keyValue.setEncoding(encodingType);
            EncryptedTrackDataType trackDataType = new EncryptedTrackDataType();
            KeyBlock keyBlock = new KeyBlock();
            keyBlock.setValue(keyValue);
            trackDataType.setFormOfPayment(keyBlock);
            paymentType.setEncryptedTrackData(trackDataType);
        } else if (transaction.getCard().getMagstripe() != null) {
            // TODO split magstripe and put tracks into fields
            //CreditCardTrackType creditCardTrackType = new CreditCardTrackType();
            //creditCardTrackType.setTrack1();
            //creditCardTrackType.setTrack2();
            //paymentType.setTrackData();
        } else {
            CreditCardType creditCard = new CreditCardType();
            creditCard.setCardNumber(transaction.getCard().getNumber());
            creditCard.setExpirationDate(String.format("%d%d", transaction.getCard().getMonth(), transaction.getCard().getYear()));
            creditCard.setCardCode(transaction.getCard().getCode());
            paymentType.setCreditCard(creditCard);
        }

        return paymentType;
    }

    private NameAndAddressType buildShippingAddress(Transaction transaction) {
        NameAndAddressType shippingAddressType = new NameAndAddressType();
        Address shippingAddress = transaction.getShippingAddress();
        if (shippingAddress != null) {
            shippingAddressType.setFirstName(shippingAddress.getFirstName());
            shippingAddressType.setLastName(shippingAddress.getLastName());
            shippingAddressType.setCompany(shippingAddress.getCompany());
            shippingAddressType.setAddress(shippingAddress.getStreet());
            shippingAddressType.setCity(shippingAddress.getCity());
            shippingAddressType.setState(shippingAddress.getState());
            shippingAddressType.setZip(shippingAddress.getPostalCode());
            shippingAddress.setCountryCode(shippingAddress.getCountryCode());
        }
        return shippingAddressType;
    }

    private MerchantAuthenticationType getMerchantAuth(GatewayConnection gatewayConnection) {
        MerchantAuthenticationType merchantAuthenticationType = new MerchantAuthenticationType();
        merchantAuthenticationType.setName(gatewayConnection.getUsername());
        merchantAuthenticationType.setTransactionKey(gatewayConnection.getPrivateKey());
        return merchantAuthenticationType;
    }

    private boolean isDuplicateTransaction(CreateTransactionResponse response) {
        if (response.getTransactionResponse() == null || response.getTransactionResponse().getErrors() == null ||
                response.getTransactionResponse().getErrors().getError() == null ||
                response.getTransactionResponse().getErrors().getError().isEmpty()) {
            // No errors exist
            return false;
        }
        for (net.authorize.api.contract.v1.TransactionResponse.Errors.Error error: response.getTransactionResponse().getErrors().getError()) {
            if (StringUtils.equals(error.getErrorCode(), "11")) {
                return true;
            }
        }
        return false;
    }

    private void setStatusAndResult(CreateTransactionResponse response, Transaction transaction) {
        if (isDuplicateTransaction(response)) {
            transaction.setStatusCode("D");
            transaction.updateResultAndCode(TransactionResult.Duplicate_Transaction.getResultCode());
            return;
        }

        switch (ResponseCode.findByResponseCode(response.getTransactionResponse().getResponseCode())) {
            case APPROVED:
                transaction.updateResultAndCode(TransactionResult.Success.getResultCode());
                if (StringUtils.equalsIgnoreCase(transaction.getStatusCode(), "a")) {
                    transaction.setStatusCode("A");
                } else {
                    transaction.setStatusCode("P");
                }
                break;
            case DECLINED:
            case ERROR:
                setStatusAndResult(response.getTransactionResponse().getErrors(), transaction);
                break;
            case REVIEW:
                // TODO should we be supporting this? It's possible this is authed but for review?
                transaction.setStatusCode("D");
                transaction.updateResultAndCode(TransactionResult.Declined.getResultCode());
            default:
                MDC.put("gatewayResponse", GsonUtil.getGson().toJson(response));
                logger.error("AuthorizeNet failed to parse status code");
                transaction.updateResultAndCode(TransactionResult.Unexpected.getResultCode());
                transaction.setStatusCode("E");
        }
    }

    private void setStatusAndResult(net.authorize.api.contract.v1.TransactionResponse.Errors errors, Transaction transaction) {
        if (errors == null || errors.getError().isEmpty()) {
            transaction.setStatusCode("E");
            transaction.updateResultAndCode(TransactionResult.Declined.getResultCode());
            return;
        }
        transaction.setStatusCode("D"); // Declined by default
        // https://developer.authorize.net/api/reference/responseCodes.html
        switch (errors.getError().get(0).getErrorCode()) {
            case "2":
            case "3":
            case "4":
                transaction.updateResultAndCode(TransactionResult.Declined.getResultCode());
                break;
            case "6":
            case "37":
                transaction.updateResultAndCode(TransactionResult.Invalid_Card_Number.getResultCode());
                break;
            case "7":
                transaction.updateResultAndCode(TransactionResult.Invalid_Expiration_Date.getResultCode());
                break;
            case "8":
                transaction.updateResultAndCode(TransactionResult.Card_Expired.getResultCode());
                break;
            case "9":
                transaction.updateResultAndCode(TransactionResult.Invalid_Routing_Number.getResultCode());
                break;
            case "10":
                transaction.updateResultAndCode(TransactionResult.Invalid_Checking_Number.getResultCode());
                break;
            case "17":
            case "28":
                transaction.updateResultAndCode(TransactionResult.Card_Not_Supported.getResultCode());
                break;
            case "18":
            case "90":
                transaction.updateResultAndCode(TransactionResult.Check_Transactions_Not_Supported.getResultCode());
                transaction.setStatusCode("E");
                break;
            case "20":
                transaction.updateResultAndCode(TransactionResult.Processor_Error.getResultCode());
                transaction.setStatusCode("E");
                break;
            case "27":
            case "127":
                transaction.updateResultAndCode(TransactionResult.Billing_Info_Wrong.getResultCode());
                break;
            case "30":
                transaction.updateResultAndCode(TransactionResult.Merchant_Invalid.getResultCode());
                transaction.setStatusCode("E");
                break;
            case "44":
            case "45":
            case "65":
            case "78":
                transaction.updateResultAndCode(TransactionResult.Invalid_Code.getResultCode());
                break;
            case "49":
                transaction.updateResultAndCode(TransactionResult.Maximum_Limit.getResultCode());
                break;
            case "56":
                transaction.updateResultAndCode(TransactionResult.Card_Transactions_Not_Supported.getResultCode());
                break;
            case "88":
            case "89":
                transaction.updateResultAndCode(TransactionResult.Invalid_Magstripe_Data.getResultCode());
                break;
            default:
                MDC.put("gatewayErrors", GsonUtil.getGson().toJson(errors));
                logger.error("AuthorizeNet failed to parse status code");
            case "57": // General error, couldn't process transaction. Possibly bad user input?
                transaction.updateResultAndCode(TransactionResult.Unexpected.getResultCode());
                transaction.setStatusCode("E");
                break;
        }
    }


    private CustomerPaymentProfileExType setupPaymentProfile(TokenRequest tokenRequest){
        CustomerPaymentProfileExType paymentProfile = new CustomerPaymentProfileExType();
        paymentProfile.setCustomerType(CustomerTypeEnum.INDIVIDUAL);
        if(tokenRequest.getAddress() != null){
            CustomerAddressType customerAddress = setUpAddress(tokenRequest.getAddress());
            paymentProfile.setBillTo(customerAddress);
        }
        if(tokenRequest.getCard() != null){
            CreditCardType creditCard = setUpCard(tokenRequest.getCard());
            PaymentType payment = new PaymentType();
            payment.setCreditCard(creditCard);
            paymentProfile.setPayment(payment);
        }
        return paymentProfile;
    }

    private CustomerAddressType setUpAddress(Address address){
        CustomerAddressType customerAddress = new CustomerAddressType();
        if (address != null) {
            customerAddress.setFirstName(address.getFirstName());
            customerAddress.setLastName(address.getLastName());
            customerAddress.setAddress(address.getStreet());
            customerAddress.setCity(address.getCity());
            customerAddress.setState(address.getState());
            customerAddress.setZip(address.getPostalCode());
            customerAddress.setCountry(address.getCountryCode());
            customerAddress.setEmail(address.getEmail());
            customerAddress.setCompany(address.getCompany());
            customerAddress.setPhoneNumber(address.getPhoneNumber());
            customerAddress.setFaxNumber(address.getFaxNumber());
        }
        return customerAddress;
    }

    private CreditCardType setUpCard(Card card){
        CreditCardType creditCard = new CreditCardType();
        creditCard.setCardNumber(card.getNumber());
        creditCard.setExpirationDate(card.getExpirationDate());
        creditCard.setCardCode(card.getCode());
        return creditCard;
    }

}
