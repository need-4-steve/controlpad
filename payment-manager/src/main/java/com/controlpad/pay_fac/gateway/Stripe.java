package com.controlpad.pay_fac.gateway;


import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.gatewayconnection.SubAccountUser;
import com.controlpad.pay_fac.payment_info.TokenRequest;
import com.controlpad.pay_fac.report.gateway.GatewayTransaction;
import com.controlpad.pay_fac.tokenization.TokenizeCardResponse;
import com.controlpad.pay_fac.transaction.TransactionResponse;
import com.controlpad.pay_fac.transaction.TransactionUtil;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.payman_common.address.Address;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.transaction.*;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.controlpad.payman_common.util.MoneyUtil;
import com.stripe.exception.*;
import com.stripe.model.Account;
import com.stripe.model.Charge;
import com.stripe.model.Customer;
import com.stripe.model.ExternalAccount;
import com.stripe.net.RequestOptions;
import org.apache.commons.lang3.ObjectUtils;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;
import org.springframework.http.HttpStatus;

import java.math.BigInteger;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class Stripe implements Gateway {

    private final Logger logger = LoggerFactory.getLogger(Stripe.class);

    private IDUtil idUtil;

    Stripe(IDUtil idUtil) {
        this.idUtil = idUtil;
    }

    @Override
    public Transaction saleCard(SqlSession session, GatewayConnection gatewayConnection,
                                        String remoteAddress, Transaction transaction, TransactionType transactionType) {
        System.out.println("Stripe saleCard is called");
        try {
            RequestOptions requestOptions = RequestOptions.builder().setApiKey(gatewayConnection.getPrivateKey()).build();

            Map<String, Object> params = new HashMap<>();
            params.put("amount", MoneyUtil.convertToCents(transaction.getAmount()));
            params.put("currency", "usd");
            addCardData(transaction, params);
            Charge charge = Charge.create(params, requestOptions);

            transaction.setGatewayConnectionId(gatewayConnection.getId());
            transaction.setGatewayReferenceId(charge.getId());
            // make sure accepted
            if (StringUtils.equalsIgnoreCase(charge.getStatus(), "succeeded")) {
                transaction.setStatusCode("P");
                transaction.updateResultAndCode(TransactionResult.Success.getResultCode());

                transaction.setSwiped(transaction.getCard().getEncMagstripe() != null || transaction.getCard().getMagstripe() != null);

                TransactionUtil.insertTransaction(session, transaction, idUtil, transaction.getAffiliatePayouts());

                return transaction;
            } else {
                // TODO is this an error with the request?
                transaction.updateResultAndCode(TransactionResult.Declined.getResultCode());
                transaction.setStatusCode("D");
                return transaction;
            }
        } catch (APIException | APIConnectionException | AuthenticationException e) {
            logger.error(e.getMessage(), e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, e.getMessage());
        } catch (InvalidRequestException ire) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, ire.getMessage());
        } catch (CardException ce) {
            setStatusAndResult(ce, transaction);
            return transaction;
        }
    }

    @Override
    public Transaction saleCheck(SqlSession session, GatewayConnection gatewayConnection, Transaction transaction,
                                 String remoteAddress, TransactionType transactionType) {
        // TODO in order to support e-check (ach) through stripe there is a verification process involving deposits similar to payman account validation
        throw new ResponseException(HttpStatus.BAD_REQUEST, "e-check not supported for stripe yet");
    }

    @Override
    public Transaction updateTransactionStatus(SqlSession session, Transaction currentTransaction, GatewayConnection gatewayConnection, String remoteAddress) {
        try {
            RequestOptions requestOptions = RequestOptions.builder().setApiKey(gatewayConnection.getPrivateKey()).build();

            Charge charge = Charge.retrieve(currentTransaction.getGatewayReferenceId(), requestOptions);
            if (charge.getTransfer() != null) {
                // TODO figure out if refunded in the same transfer and if not already processed
                if (StringUtils.equalsIgnoreCase(currentTransaction.getStatusCode(), "P")) {
                    currentTransaction.setStatusCode("S");
                }
                TransactionBatch gatewayBatch = session.getMapper(TransactionBatchMapper.class).findForExternalId(gatewayConnection.getId(), String.valueOf(charge.getTransfer()));
                if (gatewayBatch != null && !ObjectUtils.equals(currentTransaction.getBatchId(), gatewayBatch.getId())) {
                    currentTransaction.setBatchId(gatewayBatch.getId());
                }
                session.getMapper(TransactionMapper.class).updateTransactionStatusAndBatch(currentTransaction);
            }
            return currentTransaction;
        } catch (Exception e) {
            logger.error(e.getMessage(), e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "stripe error");
        }
    }

    @Override
    public Transaction refundTransaction(GatewayConnection gatewayConnection, Transaction transaction,
                                                 Transaction refund, String remoteAddress, String clientId) {
        try {
            RequestOptions requestOptions = RequestOptions.builder().setApiKey(gatewayConnection.getPrivateKey()).build();

            Map<String, Object> params = new HashMap<>();
            params.put("amount", MoneyUtil.convertToCents(refund.getAmount()));
            params.put("charge", transaction.getGatewayReferenceId());

            com.stripe.model.Refund stripeRefund = com.stripe.model.Refund.create(params, requestOptions);

            refund.setGatewayConnectionId(gatewayConnection.getId());
            refund.setGatewayReferenceId(stripeRefund.getId());
            if (StringUtils.equalsIgnoreCase(stripeRefund.getStatus(), "succeeded")) {
                refund.setStatusCode("P");
                refund.updateResultAndCode(TransactionResult.Success.getResultCode());
                // TODO if the amount is the entire transaction amount and the transaction is pending we would mark the transaction as returned?
                // TODO is there a void
            } else {
                // TODO what kind of errors can we get?
                refund.setStatusCode("D");
                refund.updateResultAndCode(TransactionResult.Unexpected.getResultCode());
            }
            return refund;
        } catch (Exception e) {
            // TODO error code handling
            e.printStackTrace();
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    @Override
    public TokenizeCardResponse tokenizeCard(TokenRequest tokenRequest, GatewayConnection gatewayConnection, String remoteAddress) {
        CardType cardType = tokenRequest.getType();
        if (cardType == CardType.UNKNOWN) {
            return new TokenizeCardResponse(3, "Error: Invalid TokenRequest Number");
        }
        RequestOptions requestOptions = RequestOptions.builder().setApiKey(gatewayConnection.getPrivateKey()).build();
        Map<String, Object> customerParams = new HashMap<>();
        Map<String, Object> sourceParams = setCardSource(tokenRequest);
        customerParams.put("source", sourceParams);
        customerParams.put("name", tokenRequest.getCard().getName());
        if (tokenRequest.getAddress() != null) {
            customerParams.put("address", convertAddressParams(tokenRequest.getAddress()));
        }
        if (tokenRequest.getEmail() != null) {
            customerParams.put("email", tokenRequest.getEmail());
        }

        Customer customer = null;
        if (tokenRequest.getGatewayCustomerId() != null) {
            try {
                customer = Customer.retrieve(tokenRequest.getGatewayCustomerId(), requestOptions);
            } catch (InvalidRequestException e) {
                // Ignore
            } catch (Exception e) {
                logger.error("Unexpected exception retrieving token customer", e);
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, e.getMessage());
            }
        }

        try {
            if (customer == null) {
                customer = Customer.create(customerParams, requestOptions);
            } else if (tokenRequest.getCurrentToken() == null) {
                customer = Customer.retrieve(tokenRequest.getGatewayCustomerId(), requestOptions);
                customer.update(customerParams, requestOptions);
                customer = Customer.retrieve(customer.getId(), requestOptions);
            } else {
                customer = Customer.retrieve(tokenRequest.getGatewayCustomerId(), requestOptions);
                com.stripe.model.Card card = null;
                for (ExternalAccount externalAccount : customer.getSources().getData()) {
                    if (externalAccount instanceof com.stripe.model.Card && externalAccount.getId().equals(tokenRequest.getCurrentToken())) {
                        card = (com.stripe.model.Card) externalAccount;
                    }
                }
                if (card != null) {
                    if (card.getLast4().equals(tokenRequest.getCard().getNumber().substring(tokenRequest.getCard().getNumber().length() - 4))) {
                        // If card is same we can just update info other than number and object
                        sourceParams.remove("object");
                        sourceParams.remove("number");
                        card.update(sourceParams, requestOptions);
                        return new TokenizeCardResponse(new TokenizeCardResponse.CardToken(card.getId(), null, tokenRequest.getExpireDate(), card.getLast4(), cardType.getSlug(), customer.getId()));
                    } else {
                        // If replacing token with new card we have to delete the old one
                        card.delete(requestOptions);
                        customer.update(customerParams, requestOptions);
                        customer = Customer.retrieve(customer.getId(), requestOptions);
                    }
                }
            }
            return new TokenizeCardResponse(
                    new TokenizeCardResponse.CardToken(
                            customer.getSources().getData().get(0).getId(),
                            null,
                            tokenRequest.getExpireDate(),
                            tokenRequest.getCard().getNumber(),
                            cardType.getSlug(),
                            customer.getId())
            );
        } catch (APIConnectionException | AuthenticationException e) {
            e.printStackTrace();
            logger.error(e.getMessage(), e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, e.getMessage());
        } catch (APIException apiException) {
            apiException.printStackTrace();
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, apiException.getMessage());
        } catch (InvalidRequestException ire) {
            ire.printStackTrace();
            throw new ResponseException(HttpStatus.BAD_REQUEST, ire.getMessage());
        } catch (CardException ce) {
            ce.printStackTrace();
            TransactionResponse transactionResponse = new TransactionResponse(getTransactionResult(ce));
            transactionResponse.setTransactionResponse(ce);
            throw new ResponseException(transactionResponse);
        }
    }

    @Override
    public Boolean isAccountSame(GatewayConnection currentConnection, GatewayConnection newConnection, SqlSession session) {
        return false;
        // TODO fix this
//        logger.info(String.format("[Stripe.isAccountSame] Current Connection ID: %s, check: old key: %s, new key: %s", currentConnection.getId(), currentConnection.getPrivateKey(), newConnection.getPrivateKey()));
//        TransactionMapper transactionMapper = session.getMapper(TransactionMapper.class);
//        String referenceId = transactionMapper.getReferenceIdByGatewayId(currentConnection.getId() + "");
//        if(referenceId == null){
//            return true;
//        }
//        RequestOptions requestOptions = RequestOptions.builder().setApiKey(newConnection.getPrivateKey()).build();
//        try{
//            Charge charge = Charge.retrieve(referenceId, requestOptions);
//            System.out.println("Charge: " + GsonUtil.getGson().toJson(charge));
//            return "succeeded".equals(charge.getStatus());
//        } catch(Exception e){
//            return false;
//        }
    }

    @Override
    public boolean checkCredentials(GatewayConnection gatewayConnection) {
        try {
            RequestOptions requestOptions = RequestOptions.builder().setApiKey(gatewayConnection.getPrivateKey()).build();
            Account account = Account.retrieve(requestOptions);
            return account != null;
        } catch (AuthenticationException authException) {
            return false;
        } catch (Exception e) {
            logger.error("Stripe.checkCredentials", e);
            return false;
        }
    }

    @Override
    public CommonResponse createSubAccount(String clientName, SqlSession clientSession, GatewayConnection masterConnection, SubAccountUser subAccountUser) {
        throw new ResponseException(HttpStatus.BAD_REQUEST, "Create sub account not supported for Stripe");
    }

    @Override
    public boolean updateSubAccount(GatewayConnection gatewayConnection, SubAccountUser subAccountUser) {
        return false;
    }

    @Override
    public Transaction importTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction) {
        throw new ResponseException(HttpStatus.METHOD_NOT_ALLOWED, "Stripe not supported");
    }

    @Override
    public Transaction captureTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction, String clientId) {
        throw new ResponseException(HttpStatus.METHOD_NOT_ALLOWED, "Stripe not supported");
    }

    @Override
    public Object getTransaction(GatewayConnection gatewayConnection, Transaction transaction) {
        RequestOptions requestOptions = RequestOptions.builder().setApiKey(gatewayConnection.getPrivateKey()).build();

        try {
            return Charge.retrieve(transaction.getGatewayReferenceId(), requestOptions);
        } catch (Exception e) {
            MDC.put("transactionId", transaction.getId());
            logger.error("Stripe failed to get transaction", e);
        }
        return null;
    }

    @Override
    public List<TransactionBatch> searchGatewayBatches(SqlSession clientSession, GatewayConnection gatewayConnection, DateTime startDate,
                                                       DateTime endDate, BigInteger page, BigInteger limit) {
        // TODO implement
        return null;
    }

    @Override
    public List<GatewayTransaction> searchTransactions(SqlSession clientSession, GatewayConnection gatewayConnection, String externalBatchId, BigInteger page, BigInteger limit) {
        return null;
    }

    /**
     * Insert card data into the request params
     */
    private void addCardData(Transaction transaction, Map<String, Object> params) {
        Map<String, Object> cardParams = new HashMap<>();
        if (transaction.getCard().getGatewayCustomerId() != null && transaction.getCard().getToken() != null) {
            params.put("customer", transaction.getCard().getGatewayCustomerId());
            params.put("source", transaction.getCard().getToken());
        } else {
            Card card = transaction.getCard();
            cardParams.put("number", card.getNumber());
            cardParams.put("exp_month", card.getMonth());
            cardParams.put("exp_year", card.getYear());
            cardParams.put("cvc", card.getCode());
            cardParams.put("name", card.getName());
            if (transaction.getBillingAddress() != null) {
                cardParams.put("address_line1", transaction.getBillingAddress().getLine1());
                if (transaction.getBillingAddress().getLine2() != null && !transaction.getBillingAddress().getLine2().isEmpty()) {
                    cardParams.put("address_line2", transaction.getBillingAddress().getLine2());
                }
                cardParams.put("address_city", transaction.getBillingAddress().getCity());
                cardParams.put("address_state", transaction.getBillingAddress().getState());
                if (transaction.getBillingAddress().getCountryCode() != null) {
                    cardParams.put("address_country", transaction.getBillingAddress().getCountryCode());
                }
                cardParams.put("address_zip", transaction.getBillingAddress().getPostalCode());
            }
            params.put("source", cardParams);
        }
    }

    private void setStatusAndResult(CardException ce, Transaction transaction) {
        transaction.setStatusCode("D");
        switch (ce.getCode()) {
            case "incorrect_number":
                transaction.updateResultAndCode(TransactionResult.Invalid_Card_Number.getResultCode());
                return;
            case "incorrect_cvc":
            case "invalid_cvc":
                transaction.updateResultAndCode(TransactionResult.Invalid_Code.getResultCode());
                return;
            case "expired_card":
                transaction.updateResultAndCode(TransactionResult.Card_Expired.getResultCode());
                return;
            default:
                // TODO log meta data
                logger.error("Unexpected code from Stripe:" + ce.getCode());
            case "card_declined":
                transaction.updateResultAndCode(TransactionResult.Unexpected.getResultCode());
        }
    }

    private TransactionResult getTransactionResult(CardException ce) {
        switch (ce.getCode()) {
            case "incorrect_number":
                return TransactionResult.Invalid_Card_Number;
            case "incorrect_cvc":
            case "invalid_cvc":
                return TransactionResult.Invalid_Code;
            case "expired_card":
                return TransactionResult.Card_Expired;
            default:
                // TODO log meta data
                logger.error("Unexpected code from Stripe:" + ce.getCode());
            case "card_declined":
                return TransactionResult.Unexpected;

        }
    }

    private TransactionType getCardTransactionType(TransactionType originalType, Charge charge) {
        if (charge.getSource() instanceof com.stripe.model.Card) {
            switch (((com.stripe.model.Card)charge.getSource()).getFunding()) {
                case "credit":
                case "prepaid":
                    if (originalType == TransactionType.DEBIT_CARD_SALE)
                        return TransactionType.CREDIT_CARD_SALE;
                    else if (originalType == TransactionType.DEBIT_CARD_SUB)
                        return TransactionType.DEBIT_CARD_SUB;
                    else
                        return originalType;
                case "debit":
                    if (originalType == TransactionType.CREDIT_CARD_SALE)
                        return TransactionType.DEBIT_CARD_SALE;
                    else if (originalType == TransactionType.CREDIT_CARD_SUB)
                        return TransactionType.DEBIT_CARD_SUB;
                    else
                        return originalType;
            }
        }
        return originalType;
    }

    private Map<String, Object> setCardSource(TokenRequest tokenRequest){
        Map<String, Object> sourceParams = new HashMap<>();
        sourceParams.put("object", "card");
        sourceParams.put("exp_month", tokenRequest.getCard().getMonth());
        sourceParams.put("exp_year", tokenRequest.getCard().getYear());
        sourceParams.put("number", tokenRequest.getCard().getNumber());
        return sourceParams;
    }

    private Map<String, Object> convertAddressParams(Address address) {
        Map<String, Object> addressParams = new HashMap<>();
        addressParams.put("line1", address.getLine1());
        if (address.getLine2() != null && !address.getLine2().isEmpty()) {
            addressParams.put("line2", address.getLine2());
        }
        addressParams.put("city", address.getCity());
        addressParams.put("state", address.getState());
        if (address.getCountryCode() != null) {
            addressParams.put("country", address.getCountryCode());
        }
        addressParams.put("postal_code", address.getPostalCode());
        return addressParams;
    }
}