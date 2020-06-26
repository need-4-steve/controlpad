package com.controlpad.pay_fac.gateway;

import com.braintreegateway.*;
import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.gatewayconnection.SubAccountUser;
import com.controlpad.pay_fac.payment_info.TokenRequest;
import com.controlpad.pay_fac.report.gateway.GatewayTransaction;
import com.controlpad.pay_fac.tokenization.TokenizeCardResponse;
import com.controlpad.pay_fac.transaction.TransactionUtil;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.transaction.*;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.util.GsonUtil;
import com.google.gson.Gson;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;
import org.springframework.http.HttpStatus;

import java.math.BigInteger;
import java.util.List;

public class Braintree implements Gateway {

    private static final Logger logger = LoggerFactory.getLogger(Braintree.class);

    private IDUtil idUtil;

    Braintree(IDUtil idUtil) {
        this.idUtil = idUtil;
    }

    @Override
    public Transaction saleCard(SqlSession session, GatewayConnection gatewayConnection,
                                        String remoteAddress, Transaction transaction, TransactionType transactionType) {
        BraintreeGateway gateway = initialGateway(gatewayConnection);
        TransactionRequest transactionRequest = buildTransactionRequest(transaction);
        System.out.println("Gateway: " + GsonUtil.getGson().toJson(gatewayConnection));
        Result<com.braintreegateway.Transaction> result = gateway.transaction().sale(transactionRequest);
        transaction.setGatewayReferenceId(result.getTarget().getId());
        transaction.setGatewayConnectionId(gatewayConnection.getId());
        transaction.setSwiped(transaction.getCard().getEncMagstripe() != null || transaction.getCard().getMagstripe() != null);

        setStatusAndResult(result, transaction);

        TransactionUtil.insertTransaction(session, transaction, idUtil, transaction.getAffiliatePayouts());

        //Only for testing
        gateway.testing().settle(result.getTarget().getId());

        return transaction;
    }

    @Override
    public Transaction saleCheck(SqlSession session, GatewayConnection gatewayConnection, Transaction transaction,
                                         String remoteAddress, TransactionType transactionType) {
        return null;
    }

    @Override
    public Transaction updateTransactionStatus(SqlSession session, Transaction currentTransaction, GatewayConnection gatewayConnection, String remoteAddress) {
        BraintreeGateway gateway = initialGateway(gatewayConnection);
        com.braintreegateway.Transaction.Status status = getGatewayStatus(gateway, currentTransaction);
        switch (status) {
            case VOIDED:
                currentTransaction.setStatusCode("V");
                break;
            case SETTLED:
                currentTransaction.setStatusCode("S");
                break;
            case SETTLEMENT_DECLINED:
            case UNRECOGNIZED:
            case AUTHORIZATION_EXPIRED:
            case FAILED:
            case GATEWAY_REJECTED:
            case PROCESSOR_DECLINED:
                currentTransaction.setStatusCode("E");
                break;
            case AUTHORIZED:
            case AUTHORIZING:
            case SUBMITTED_FOR_SETTLEMENT:
            case SETTLING:
            case SETTLEMENT_PENDING:
            case SETTLEMENT_CONFIRMED:
                break;
            default:
                MDC.put("transactionId", currentTransaction.getId());
                MDC.put("braintreeTransactionStatus", GsonUtil.getGson().toJson(status));
                logger.error("Braintree unexpected status code");

        }
        session.getMapper(TransactionMapper.class).updateTransactionStatus(currentTransaction);
        return currentTransaction;
    }

    @Override
    public com.controlpad.payman_common.transaction.Transaction refundTransaction(GatewayConnection gatewayConnection, Transaction transaction,
                                                                                  Transaction refund, String remoteAddress, String clientId) {
        BraintreeGateway gateway = initialGateway(gatewayConnection);
        if("P".equals(transaction.getStatusCode()) && (refund.getAmount().compareTo(transaction.getAmount()) == 0)){
            if(voidTransaction(gateway, transaction)){
                refund.setGatewayConnectionId(gatewayConnection.getId());
                refund.setStatusCode("S");
                refund.updateResultAndCode(TransactionResult.Success.getResultCode());
                refund.setTransactionType(TransactionType.REFUND.slug);
                return refund;
            }
        }
        try {
            Result<com.braintreegateway.Transaction> result = gateway.transaction().refund(transaction.getGatewayReferenceId(), refund.getAmount());
            System.out.println("Refund Response: " + GsonUtil.getGson().toJson(result));
            refund.setGatewayReferenceId(result.getTransaction().getId());
            refund.setGatewayConnectionId(gatewayConnection.getId());
            if(result.isSuccess()){
                switch (result.getTarget().getStatus()) {
                    case AUTHORIZED:
                    case AUTHORIZING:
                    case SUBMITTED_FOR_SETTLEMENT:
                    case SETTLING:
                    case SETTLEMENT_PENDING:
                    case SETTLEMENT_CONFIRMED:
                    case SETTLED:
                        refund.setStatusCode("P");
                        refund.updateResultAndCode(TransactionResult.Success.getResultCode());
                        return refund;
                    default:
                        logger.error(String.format("Braintree unexpected status code found. Transaction ID: %s, Status: %s", result.getTarget().getId(), result.getTarget().getStatus()));
                        refund.updateResultAndCode(TransactionResult.Unexpected.getResultCode());
                        refund.setStatusCode("E");
                        return refund;
                }
            } else{
                MDC.put("gatewayResponse", GsonUtil.getGson().toJson(result));
                MDC.put("refund", GsonUtil.getGson().toJson(refund));
                logger.error("Braintree refund failed");
                refund.updateResultAndCode(TransactionResult.Unexpected.getResultCode());
                refund.setStatusCode("E");
                return refund;
            }
        } catch (Exception e){
            MDC.put("refund", GsonUtil.getGson().toJson(refund));
            logger.error("Braintree refund exception", e);
            refund.updateResultAndCode(TransactionResult.Unexpected.getResultCode());
            refund.setStatusCode("E");
            return refund;
        }
    }

    @Override
    //TODO double check how to do tokenization in Braintree. current structure need to be modified
    public TokenizeCardResponse tokenizeCard(TokenRequest creditTokenRequestData, GatewayConnection gatewayConnection, String remoteAddress) {
        CardType cardType = creditTokenRequestData.getType();
//        if (cardType == CardType.UNKNOWN) {
//            return new TokenizeCardResponse(3, "Error: Invalid TokenRequest Number");
//        }
        if(creditTokenRequestData.getCardNonce() == null){
            return new TokenizeCardResponse(3, "Error: payment method nonce cannot be null");
        }
        BraintreeGateway gateway = initialGateway(gatewayConnection);

        if(creditTokenRequestData.getGatewayCustomerId() == null){
            //create a new customer
            CustomerRequest request = new CustomerRequest()
                    .paymentMethodNonce(creditTokenRequestData.getCardNonce());

            Result<Customer> result = gateway.customer().create(request);
            if(result.isSuccess()){
                System.out.println(GsonUtil.getGson().toJson(result));
                return new TokenizeCardResponse(
                        new TokenizeCardResponse.CardToken(result.getTarget().getPaymentMethods().get(0).getToken(), null, creditTokenRequestData.getExpireDate(), creditTokenRequestData.getCard().getNumber(), cardType.getSlug(), result.getTarget().getId())
                );
            }else{
                return new TokenizeCardResponse(10, String.format("Error: create new customer failed, info: %s", GsonUtil.getGson().toJson(result)));
            }
        }else{
            //save credit card under the specific customer ID
            PaymentMethodRequest request = new PaymentMethodRequest()
                    .customerId(creditTokenRequestData.getGatewayCustomerId())
                    .paymentMethodNonce(creditTokenRequestData.getCardNonce());
            Result<? extends PaymentMethod> result = gateway.paymentMethod().create(request);
            if (result.isSuccess()){
                return new TokenizeCardResponse(
                        new TokenizeCardResponse.CardToken(result.getTarget().getToken(), null, creditTokenRequestData.getExpireDate(), creditTokenRequestData.getCard().getNumber(), cardType.getSlug(), result.getTarget().getCustomerId())
                );
            }else{
                return new TokenizeCardResponse(10, String.format("Error: save credit card failed, info: %s", GsonUtil.getGson().toJson(result)));
            }
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
        // TODO implement
        return false;
    }

    @Override
    public CommonResponse createSubAccount(String clientName, SqlSession clientSession, GatewayConnection masterConnection, SubAccountUser subAccountUser) {
        throw new ResponseException(HttpStatus.BAD_REQUEST, "Create sub account not supported for BrainTree");
    }

    @Override
    public boolean updateSubAccount(GatewayConnection gatewayConnection, SubAccountUser subAccountUser) {
        return false;
    }

    @Override
    public Transaction importTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction) {
        throw new ResponseException(HttpStatus.METHOD_NOT_ALLOWED, "Braintree not supported for this feature");
    }

    @Override
    public Transaction captureTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction, String clientId) {
        throw new ResponseException(HttpStatus.METHOD_NOT_ALLOWED, "Braintree not supported for this feature");
    }

    @Override
    public Object getTransaction(GatewayConnection gatewayConnection, Transaction transaction) {
        return null;
    }

    @Override
    public List<TransactionBatch> searchGatewayBatches(SqlSession clientSession, GatewayConnection gatewayConnection, DateTime startDate, DateTime endDate, BigInteger page, BigInteger limit) {
        // TODO can this be implemented?
        throw new ResponseException(HttpStatus.METHOD_NOT_ALLOWED, "Braintree not supported for this feature");
    }

    @Override
    public List<GatewayTransaction> searchTransactions(SqlSession clientSession, GatewayConnection gatewayConnection, String externalBatchId, BigInteger page, BigInteger limit) {
        return null;
    }

    public String getToken(GatewayConnection gatewayConnection){
        return initialGateway(gatewayConnection).clientToken().generate();
    }

    private BraintreeGateway initialGateway(GatewayConnection gatewayConnection){
        return new BraintreeGateway(
                gatewayConnection.getIsSandbox() ? Environment.SANDBOX : Environment.PRODUCTION,
                gatewayConnection.getUsername(),
                gatewayConnection.getPublicKey(),
                gatewayConnection.getPrivateKey()
        );
    }

    private TransactionRequest buildTransactionRequest(Transaction transaction){
        TransactionRequest transactionRequest = new TransactionRequest();
        if(transaction.getCard().getNonce() != null){
            transactionRequest.paymentMethodNonce(transaction.getCard().getNonce());
        }
        else if(transaction.getCard().getToken() != null){
            transactionRequest.paymentMethodToken(transaction.getCard().getToken());
        }
        else if(transaction.getCard() != null){
            Card card = transaction.getCard();
            transactionRequest.creditCard()
                    .number(card.getNumber())
                    .cardholderName(card.getName())
                    .cvv(card.getCode())
                    .expirationDate(card.getMonth() + "/" + card.getYear());
        }else if(transaction.getCard().getNumber() != null){
            //Deprecated
            transactionRequest.creditCard()
                    .number(transaction.getCard().getNumber())
                    .cardholderName(transaction.getAccountHolder())
                    .cvv(transaction.getCard().getCode())
                    .expirationDate(String.format("%02d/%d", transaction.getCard().getMonth(), transaction.getCard().getYear()));
        }
        transactionRequest.amount(transaction.getAmount());
        if (transaction.getSalesTax() != null) {
            transactionRequest.taxAmount(transaction.getSalesTax());
        }
        return transactionRequest.options().submitForSettlement(true).done();
    }

    private boolean voidTransaction(BraintreeGateway gateway, Transaction transaction){
       switch (transaction.getStatusCode()){
           case "S":
               throw new ResponseException(HttpStatus.PRECONDITION_FAILED, "Transaction already settled. Please process a refund.");
           case "R":
           case "V":
               throw new ResponseException(HttpStatus.OK, "Transaction already refunded.");
           case "A":
           case "P":
               Result<com.braintreegateway.Transaction> result = gateway.transaction().voidTransaction(transaction.getGatewayReferenceId());
               if(result.isSuccess()){
                   return true;
               }else{
                   MDC.put("transactionId", transaction.getId());
                   MDC.put("gatewayResponse", GsonUtil.getGson().toJson(result));
                   logger.info("Braintree failed to void transaction");
                   return false;
               }
           default:
               throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Transaction status code " + transaction.getStatusCode());
       }
    }

    private com.braintreegateway.Transaction.Status getGatewayStatus(BraintreeGateway gateway, Transaction transaction){
        com.braintreegateway.Transaction gatewayTransaction = gateway.transaction().find(transaction.getGatewayReferenceId());
        return gatewayTransaction.getStatus();
    }

    private void setStatusAndResult(Result<com.braintreegateway.Transaction> result, Transaction transaction) {
        if(result.isSuccess()){
            transaction.updateResultAndCode(TransactionResult.Success.getResultCode());
            transaction.setStatusCode("P");
            return;
        }

        com.braintreegateway.Transaction braintreeTransaction;
        if (result.getTransaction() != null) {
            braintreeTransaction = result.getTransaction();
        } else if (result.getTarget() != null) {
            braintreeTransaction = result.getTarget();
        } else {
            MDC.put("gatewayResponse", GsonUtil.getGson().toJson(result));
            logger.error("Braintree transaction status unexpected");
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
        // https://developers.braintreepayments.com/reference/general/processor-responses/authorization-responses
        int code = Integer.valueOf(braintreeTransaction.getProcessorResponseCode());
        transaction.setStatusCode("D");
        switch (code) {
            default:
                MDC.put("braintreeTransactionCode", String.valueOf(code));
                logger.error("Unexpected error code from braintree");
                MDC.remove("braintreeTransactionCode");
            case 2000: // Do Not Honor
            case 2002: // Limit Exceeded
            case 2003: // Cardholders activity limit exceeded
            case 2007: // No Account
            case 2011: // Voice Auth Required
            case 2012: // Processor Declined - Possible Lost TokenRequest
            case 2013: // Processor Declined - Possible Stolen TokenRequest
            case 2014: // Processor Declined - Fraud Suspected
            case 2015: // Transaction Not Allowed
            case 2019: // Invalid Transaction
            case 2020: // Violation
            case 2021: // Security Violation
            case 2022: // Updated Cardholder Available
            case 2024: // TokenRequest Type Not Enabled
            case 2038: // Processor Declined
            case 2041: // Call For Approval
            case 2044: // Call Issuer
            case 2046: // Declined
            case 2057: // Issuer or Cardholder has put a restriction on the card
                transaction.updateResultAndCode(TransactionResult.Declined.getResultCode());
            case 2001: // Insufficient Funds
                transaction.updateResultAndCode(TransactionResult.Insufficient_Funds.getResultCode());
            case 2004: // Expired TokenRequest
                transaction.updateResultAndCode(TransactionResult.Card_Expired.getResultCode());
            case 2005: // Invalid TokenRequest Number
            case 2008: // TokenRequest Account Length Error
            case 2009: // No Such Issuer
                transaction.updateResultAndCode(TransactionResult.Invalid_Card_Number.getResultCode());
            case 2010: //  TokenRequest Issuer Declined CVV
            case 2039: // Invalid Authorization code
                transaction.updateResultAndCode(TransactionResult.Invalid_Code.getResultCode());
            case 2016: // Duplicate transaction
                transaction.updateResultAndCode(TransactionResult.Duplicate_Transaction.getResultCode());
            case 2047: // Pick up card
            case 2053: // TokenRequest reported as lost or stolen
                transaction.updateResultAndCode(TransactionResult.Lost_Or_Stolen.getResultCode());
            case 2048: // Invalid Amount
            case 2056: // Transaction amount exceeds limit
                transaction.updateResultAndCode(TransactionResult.Maximum_Limit.getResultCode());
            case 2059: // Address Verification Failed
            case 2060: // Address Verification and TokenRequest Security Code Failed
                transaction.updateResultAndCode(TransactionResult.Billing_Info_Wrong.getResultCode());
        }
    }
}
