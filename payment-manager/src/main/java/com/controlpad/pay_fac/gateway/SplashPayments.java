package com.controlpad.pay_fac.gateway;

import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.gateway.splash_payments.*;
import com.controlpad.pay_fac.gatewayconnection.SubAccountUser;
import com.controlpad.pay_fac.payment_info.TokenRequest;
import com.controlpad.pay_fac.report.gateway.GatewayTransaction;
import com.controlpad.pay_fac.tokenization.TokenizeCardResponse;
import com.controlpad.pay_fac.transaction.TransactionUtil;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.address.Address;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionType;
import com.controlpad.payman_common.transaction.*;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.util.GsonUtil;
import com.controlpad.payman_common.util.MoneyUtil;
import com.google.gson.Gson;
import com.google.gson.JsonArray;
import com.google.gson.JsonObject;
import com.google.gson.reflect.TypeToken;
import com.mashape.unirest.http.HttpResponse;
import com.mashape.unirest.http.Unirest;
import com.mashape.unirest.http.exceptions.UnirestException;
import com.mashape.unirest.request.GetRequest;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;
import org.springframework.http.HttpStatus;

import java.lang.reflect.Type;
import java.math.BigDecimal;
import java.math.BigInteger;
import java.util.Arrays;
import java.util.List;
import java.util.Locale;

public class SplashPayments implements Gateway {

    private static final String BASE_URL = "https://api.splashpayments.com";
    private static final String SANDBOX_BASE_URL = "https://test-api.splashpayments.com";

    private static final Type transactionResponseType = new TypeToken<SplashBaseResponse<SplashTransaction>>(){}.getType();
    private static final Type entityResponseType = new TypeToken<SplashBaseResponse<SplashEntity>>(){}.getType();
    private static final Type transactionResultResponseType = new TypeToken<SplashBaseResponse<SplashTransactionResult>>(){}.getType();
    private static final Type accountResponseType = new TypeToken<SplashBaseResponse<SplashAccount>>(){}.getType();
    private static final Type customerResponseType = new TypeToken<SplashBaseResponse<SplashCustomer>>(){}.getType();
    private static final Type tokenResponseType = new TypeToken<SplashBaseResponse<SplashToken>>(){}.getType();
    private static final Type merchantResponseType = new TypeToken<SplashBaseResponse<SplashMerchant>>(){}.getType();

    private final Logger logger = LoggerFactory.getLogger(SplashPayments.class);

    private IDUtil idUtil;
    private Gson gson;

    SplashPayments(IDUtil idUtil) {
        this.idUtil = idUtil;
        gson = new Gson();
    }

    @Override
    public Transaction saleCard(SqlSession session, GatewayConnection gatewayConnection, String remoteAddress,
                                        Transaction transaction, TransactionType transactionType) {
        return processSale(session, gatewayConnection, buildCardSaleBody(gatewayConnection.getMerchantId(),transaction),
                transaction, transactionType);
    }

    @Override
    public Transaction saleCheck(SqlSession session, GatewayConnection gatewayConnection, Transaction transaction,
                                         String remoteAddress, TransactionType transactionType) {
        return processSale(session, gatewayConnection, buildCheckSaleBody(gatewayConnection.getMerchantId(),
                transaction), transaction, transactionType);
    }

    private Transaction processSale(SqlSession session, GatewayConnection gatewayConnection, String requestBody,
                                            Transaction transaction, TransactionType transactionType) {

        String responseString = post("/txns", gatewayConnection.getPrivateKey(),
                requestBody, gatewayConnection.getIsSandbox());
        SplashBaseResponse<SplashTransaction> response = gson.fromJson(responseString, transactionResponseType);

        SplashTransaction splashTransaction = response.getData();
        if (splashTransaction != null) {
            transaction.setGatewayReferenceId(splashTransaction.getId());
            transaction.setGatewayConnectionId(gatewayConnection.getId());
            transaction.setCard(null);
            transaction.setBankAccount(null);

            if (response.getData().getSwiped() != null) {
                transaction.setSwiped(response.getData().getSwiped() > 0);
            }

            setStatusAndResult(gatewayConnection, splashTransaction, transaction);


            TransactionUtil.insertTransaction(session, transaction, idUtil, transaction.getAffiliatePayouts());

            return transaction;
        } else {
            MDC.put("gatewayResponse", responseString);
            logger.error("SplashPayments didn't return a transaction or error");
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    @Override
    public Transaction updateTransactionStatus(SqlSession session, Transaction currentTransaction, GatewayConnection gatewayConnection, String remoteAddress) {

        return null;
    }

    @Override
    public Transaction refundTransaction(GatewayConnection gatewayConnection, Transaction currentTransaction,
                                                 Transaction refund, String remoteAddress, String clientId) {

        SplashBaseResponse<SplashTransaction> originalSale = gson.fromJson(get("/txns/" + currentTransaction.getGatewayReferenceId(),
                gatewayConnection.getPrivateKey(), gatewayConnection.getIsSandbox(), null), transactionResponseType);

        refund.setGatewayConnectionId(gatewayConnection.getId());

        switch (originalSale.getData().getStatus()) {
            case 3: // Captured
            case 4: // Settled
                return refundTransaction(gatewayConnection, currentTransaction, refund, 5); // 5: Refund
            case 0: // Pending
            case 1: // Approved
                if (refund.getAmount().compareTo(currentTransaction.getAmount()) == 0) {
                    refund = voidTransaction(gatewayConnection, currentTransaction, refund);
                } else {
                    refund = refundTransaction(gatewayConnection, currentTransaction, refund, 4); // 4: Reverse Auth
                }
                if (refund.getResultCode() == TransactionResult.Unexpected.getResultCode()) {
                    // TODO check for a more specific error
                    // Trying to do a normal refund if the transaction settled as a race condition
                    return refundTransaction(gatewayConnection, currentTransaction, refund, 5);
                }
                return refund;
            case 5: // returned already or is this for chargebacks?, this might not be good handling if vterm was used to refund and a partial comes in through the api
                refund.setStatusCode("D");
                refund.updateResultAndCode(TransactionResult.Maximum_Limit.getResultCode());  // TODO does a partial refund get this state? Should the error just be that it's already refunded?
                return refund;
            default:
                refund.updateResultAndCode(TransactionResult.Unexpected.getResultCode());
                refund.setStatusCode("E");
                return refund;
        }
   }

   private Transaction voidTransaction(GatewayConnection gatewayConnection, Transaction currentTransaction, Transaction refund) {
       // For void just remove from batch
       String responseString = put("/txns/" + currentTransaction.getGatewayReferenceId(), gatewayConnection.getPrivateKey(),
               "{\"batch\":null}", gatewayConnection.getIsSandbox());
       SplashBaseResponse<SplashTransaction> updateResult = gson.fromJson(responseString, transactionResponseType);
       // TODO might need to catch a failure if there is a race condition
       if (updateResult == null || updateResult.getData() == null || updateResult.getData().getBatch() != null) {
           MDC.put("transactionId", currentTransaction.getId());
           MDC.put("gatewayResponse", responseString);
           logger.error("SplashPayments failed to void transaction");
           refund.setStatusCode("E");
           refund.updateResultAndCode(TransactionResult.Unexpected.getResultCode());
           return refund;
       }

       refund.updateResultAndCode(TransactionResult.Success.getResultCode());
       refund.setStatusCode("S");
       refund.setTransactionType(TransactionType.VOID.slug);
       return refund;
   }

   private Transaction refundTransaction(GatewayConnection gatewayConnection, Transaction currentTransaction,
                                                 Transaction refund, int type) {
        // Should only use 4 and 5 for type | https://portal.splashpayments.com/docs/api#txns
       String responseString = post("/txns", gatewayConnection.getPrivateKey(),
               buildRefundBody(gatewayConnection.getMerchantId() ,currentTransaction, refund, type), gatewayConnection.getIsSandbox());
       SplashBaseResponse<SplashTransaction> refundResponse = gson.fromJson(responseString, transactionResponseType);

       if (refundResponse == null || refundResponse.getData() == null) {
           MDC.put("gatewayResponse", responseString);
           logger.error("SplashPayments failed to refund transaction");
           refund.setStatusCode("E");
           refund.updateResultAndCode(TransactionResult.Unexpected.getResultCode());
           return refund;
       }
       // TODO might have to manually parse/catch errors
       refund.setGatewayReferenceId(refundResponse.getData().getId());
       refund.setGatewayConnectionId(gatewayConnection.getId());
       refund.setStatusCode("P");
       refund.updateResultAndCode(TransactionResult.Success.getResultCode());
       return refund;
   }

    @Override
    public TokenizeCardResponse tokenizeCard(TokenRequest tokenRequest, GatewayConnection gatewayConnection, String remoteAddress) {
        // TODO put in validation for first/last name and email
        CardType cardType = tokenRequest.getType();
        if (cardType == CardType.UNKNOWN) {
            return new TokenizeCardResponse(3, "Error: Invalid TokenRequest Number");
        }
        if(tokenRequest.getGatewayCustomerId() == null){
            //Generate customer first, then create token
            SplashBaseResponse<SplashCustomer> customerResponse = gson.fromJson(post("/customers ", gatewayConnection.getPrivateKey(),
                    buildCustomerBody(tokenRequest), gatewayConnection.getIsSandbox()), customerResponseType);

            SplashBaseResponse<SplashToken> tokenResponse = gson.fromJson(post("/tokens", gatewayConnection.getPrivateKey(),
                    buildTokenBody(customerResponse.getData().getId(), tokenRequest), gatewayConnection.getIsSandbox()), tokenResponseType);
            return new TokenizeCardResponse(
                    new TokenizeCardResponse.CardToken(tokenResponse.getData().getToken(), tokenResponse.getData().getId(), tokenRequest.getExpireDate(), tokenRequest.getCard().getNumber(), cardType.getSlug(), tokenResponse.getData().getCustomer())
            );

        }else if(tokenRequest.getCurrentToken() == null){
            SplashBaseResponse<SplashToken> tokenResponse = gson.fromJson(post("/tokens", gatewayConnection.getPrivateKey(),
                    buildTokenBody(tokenRequest.getGatewayCustomerId(), tokenRequest), gatewayConnection.getIsSandbox()), tokenResponseType);
            return new TokenizeCardResponse(
                    new TokenizeCardResponse.CardToken(tokenResponse.getData().getToken(), tokenResponse.getData().getId(), tokenRequest.getExpireDate(), tokenRequest.getCard().getNumber(), cardType.getSlug(), tokenResponse.getData().getCustomer())
            );
        }else{
            SplashBaseResponse<SplashToken> tokenResponse = gson.fromJson(put("/tokens/" + tokenRequest.getGatewayTokenId(), gatewayConnection.getPrivateKey(),
                    buildTokenBody(tokenRequest.getGatewayCustomerId(), tokenRequest), gatewayConnection.getIsSandbox()), tokenResponseType);

            return new TokenizeCardResponse(
                    new TokenizeCardResponse.CardToken(tokenRequest.getCurrentToken(), tokenRequest.getGatewayTokenId(), tokenRequest.getExpireDate(), tokenRequest.getCard().getNumber(), cardType.getSlug(), tokenRequest.getGatewayCustomerId())
            );
        }
    }

    @Override
    public Boolean isAccountSame(GatewayConnection currentConnection, GatewayConnection newConnection, SqlSession session) {
        return StringUtils.equals(currentConnection.getUsername(), newConnection.getUsername());
    }

    @Override
    public boolean checkCredentials(GatewayConnection gatewayConnection) {
        // Public key will get rejected by this call and won't work for transactions either so we can't allow public key to sneaky in
        try {
            JsonObject response = gson.fromJson(Unirest.get((gatewayConnection.getIsSandbox() ? SANDBOX_BASE_URL : BASE_URL) +
                    "/merchants/" + gatewayConnection.getMerchantId())
                    .header("Accept", "application/json")
                    .header("APIKEY", gatewayConnection.getPrivateKey())
                    .asString().getBody(), JsonObject.class);
            return response.has("response");
            // TODO fix this to work on the entity id if it doesn't take sales, and to check for an actual data in the response
        } catch (UnirestException e) {
            logger.error("checkCredentials error", e);
            return false;
        }
    }

    @Override
    public CommonResponse createSubAccount(String clientName, SqlSession clientSession, GatewayConnection masterConnection, SubAccountUser subAccountUser) {

        String body = buildEntityBody(clientName, masterConnection, subAccountUser.getBusiness());

        String responseBody = post("/entities",
                masterConnection.getPrivateKey(),
                body,
                masterConnection.getIsSandbox());
        SplashBaseResponse<SplashEntity> entity = gson.fromJson(responseBody, entityResponseType);
        if (entity == null || entity.getData() == null) {
            subAccountUser.getBusiness().obscure();
            MDC.put("subAccountUser", GsonUtil.getGson().toJson(subAccountUser));
            MDC.put("gatewayResponse", responseBody);
            logger.error("Failed to create sub account for splashpayments");
            return new CommonResponse(false, 99, "Unexpected error");
        }

        // Save user gateway connection
        GatewayConnection subAccountGC = new GatewayConnection(subAccountUser.getTeamId(), subAccountUser.getUserId(),
                subAccountUser.getBusiness().getName(), masterConnection.getUsername(), entity.getData().getId(),
                entity.getData().getMerchant().getId(), masterConnection.getPrivateKey(), GatewayConnectionType.SPLASH_PAYMENTS.slug,
                masterConnection.getIsSandbox(), false, true, false, true,
                masterConnection.getId(), false, true);

        clientSession.getMapper(GatewayConnectionMapper.class).insert(subAccountGC);

        return new CommonResponse<>(true, 1, "Success");
    }

    @Override
    public boolean updateSubAccount(GatewayConnection gatewayConnection, SubAccountUser subAccountUser) {

        // TODO update info

        // An account should already exist from signup
        if (subAccountUser.getBusiness().getAccount() != null) {
            try {
                String responseString = get("/entities/" + gatewayConnection.getEntityId() + "/accounts",
                        gatewayConnection.getPrivateKey(), gatewayConnection.getIsSandbox(), null);
                SplashBaseResponse<SplashAccount> accountResponse = gson.fromJson(responseString, accountResponseType);

                if (accountResponse.getData() == null) {
                    MDC.put("gatewayResponse", responseString);
                    MDC.put("subAccountUser", GsonUtil.getGson().toJson(subAccountUser));
                    logger.error("SplashPayments failed to update subaccount user");
                    return false;
                }

                // Update account
                JsonObject accountUpdateBody = new JsonObject();
                accountUpdateBody.addProperty("entity", gatewayConnection.getEntityId());
                accountUpdateBody.addProperty("primary", 1);
                accountUpdateBody.addProperty("status", 1);
                accountUpdateBody.addProperty("currency", "USD");
                JsonObject account = new JsonObject();
                account.addProperty("number", subAccountUser.getBusiness().getAccount().getNumber());
                account.addProperty("routing", subAccountUser.getBusiness().getAccount().getRouting());
                account.addProperty("method", parseMethod(subAccountUser.getBusiness().getAccount().getType()));
                accountUpdateBody.add("account", account);

                put("/accounts/" + accountResponse.getData().getId(), gatewayConnection.getPrivateKey(),
                        gson.toJson(accountUpdateBody), gatewayConnection.getIsSandbox());
            } catch (Exception e) {
                logger.error("SplashPayments failed to update subaccount user", e);
                return false;
            }
        }

        return true;
    }

    @Override
    public Transaction importTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction) {
        String responseString = get("/txns/" + transaction.getGatewayReferenceId(),
                gatewayConnection.getPrivateKey(), gatewayConnection.getIsSandbox(), "merchant[equals]=" + gatewayConnection.getMerchantId());
        SplashBaseResponse<SplashTransaction> splashTransactionResponse = gson.fromJson(responseString, transactionResponseType);
        if (!splashTransactionResponse.getErrors().isEmpty()) {
            MDC.put("gatewayResponse", responseString);
            logger.error("SplashPayments failed to import transaction");
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
        if (splashTransactionResponse.getData() == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Transaction not found");
        }

        String statusCode;
        Integer resultCode;
        switch (splashTransactionResponse.getData().getStatus()) {
            // TODO test these status codes against auth/capture flows
            case 0: // Pending
            case 1: // Accepted
                if (splashTransactionResponse.getData().getType() == 2) {
                    statusCode = "A";
                } else {
                    statusCode = "P";
                }
                resultCode = TransactionResult.Success.getResultCode();
                break;
            case 3: // Captured
            case 4: // Settled
                statusCode = "P";
                resultCode = TransactionResult.Success.getResultCode();
                // Force processor to observe and save batch info by marking it pending
                break;
            case 2: // Error
                resultCode = TransactionResult.Declined.getResultCode();
                statusCode = "E";
                break;
            case 5: // Returned
                resultCode = TransactionResult.Refunded.getResultCode();
                statusCode = "R";
                break;
            default:
                MDC.put("gatewayResponse", responseString);
                MDC.put("message", "status code unexpected");
                logger.error("SplashPayments failed to import transaction");
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
        }
        // TODO any concern for reserved transactions? Maybe it should be forced to a state of pending and in processor as well

        String forTxnId = null;
        switch (splashTransactionResponse.getData().getType()) {
            case 1: // Sale
            case 2: // Auth
            case 3: // Capture
                if (transaction.getTransactionType().equalsIgnoreCase(TransactionType.REFUND.slug)){
                    throw new ResponseException(HttpStatus.BAD_REQUEST, "Cannot import a refund as a sale transaction");
                }
                break;
            case 4: // Reverse Auth
                // Unsupported
                throw new ResponseException(HttpStatus.BAD_REQUEST, "Reverse Auth transactions not supported for import");
            case 5: // Refund
                if (!transaction.getTransactionType().equalsIgnoreCase(TransactionType.REFUND.slug)){
                    throw new ResponseException(HttpStatus.BAD_REQUEST, "Cannot import a sale as a refund transaction");
                }
                forTxnId = sqlSession.getMapper(TransactionMapper.class).findIdForGatewayReference(gatewayConnection.getId(), splashTransactionResponse.getData().getForTxn());
        }

        BigDecimal tax;
        if (transaction.getSalesTax() == null) {
            tax = (splashTransactionResponse.getData().getTax() == null ? BigDecimal.ZERO : MoneyUtil.convertFromCents(splashTransactionResponse.getData().getTax()));
        }  else {
            tax = transaction.getSalesTax();
        }

        Transaction saveTransaction = new Transaction(null,
                transaction.getPayeeUserId(), transaction.getPayerUserId(), transaction.getTeamId(),
                transaction.getGatewayReferenceId(), transaction.getTransactionType(),
                MoneyUtil.convertFromCents(splashTransactionResponse.getData().getTotal()),
                tax, BigDecimal.ZERO, statusCode, resultCode,
                gatewayConnection.getId(), splashTransactionResponse.getData().getDescription(),
                StringUtils.join(Arrays.asList(splashTransactionResponse.getData().getFirst(), splashTransactionResponse.getData().getLast()), " ")
        );
        saveTransaction.setForTxnId(forTxnId);
        saveTransaction.setSwiped(splashTransactionResponse.getData().getSwiped() > 0);

        TransactionUtil.insertTransaction(sqlSession, saveTransaction, idUtil, null);

        return saveTransaction;
    }

    @Override
    public Transaction captureTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction, String clientId) {
        throw new ResponseException(HttpStatus.METHOD_NOT_ALLOWED, "SplashPayments not supported");
    }

    @Override
    public Object getTransaction(GatewayConnection gatewayConnection, Transaction transaction) {
        return gson.fromJson(
                get("/txns/" + transaction.getGatewayReferenceId(),
                        gatewayConnection.getPrivateKey(), gatewayConnection.getIsSandbox(), null),
                SplashTransaction.class);
    }

    @Override
    public List<TransactionBatch> searchGatewayBatches(SqlSession clientSession, GatewayConnection gatewayConnection, DateTime startDate,
                                                       DateTime endDate, BigInteger page, BigInteger limit) {
        // TODO implement
        return null;
    }

    @Override
    public List<GatewayTransaction> searchTransactions(SqlSession clientSession, GatewayConnection gatewayConnection, String externalBatchId, BigInteger page, BigInteger limit) {
        // TODO implement
        return null;
    }

    public CommonResponse<GatewayConnection> createExpressMerchant(String clientName, GatewayConnection masterConnection, CreateMerchantBody createMerchantBody) {
        String responseString = post("/entities",
                masterConnection.getPrivateKey(),
                buildEntityBody(clientName, masterConnection, createMerchantBody.getBusiness()),
                masterConnection.getIsSandbox());

        SplashBaseResponse<SplashEntity> entity = gson.fromJson(responseString, entityResponseType);
        if (entity.getData() == null || entity.getData().getMerchant() == null) {
            MDC.put("gatewayResponse", responseString);
            logger.error("SplashPayments failed to create merchant");
            return new CommonResponse<>(false, 99, "Unexpected error");
        }

        GatewayConnection merchantAccountGC = new GatewayConnection(null, null,
                createMerchantBody.getBusiness().getName(), masterConnection.getUsername(), entity.getData().getId(),
                entity.getData().getMerchant().getId(), masterConnection.getPrivateKey(), GatewayConnectionType.SPLASH_PAYMENTS.slug,
                masterConnection.getIsSandbox(), true, true, false, false,
                null, false, true);

        return new CommonResponse<GatewayConnection>(true, 1, "Success").setData(merchantAccountGC);
    }

    private String get(String path, String apikey, boolean sandbox, String searchHeader) {
        try {
            GetRequest request = Unirest.get((sandbox ? SANDBOX_BASE_URL : BASE_URL) + path)
                    .header("Accept", "application/json")
                    .header("APIKEY", apikey);

            if (searchHeader != null) {
                    request.header("SEARCH", searchHeader);
            }
            HttpResponse<String> sloppyResponse =
                    request.asString();

            return parseResponse(sloppyResponse);
        } catch (UnirestException e) {
            logger.error("SplashPayments request failure", e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    private String post(String path, String apikey, String body, boolean sandbox) {
        try {
            HttpResponse<String> sloppyResponse = Unirest.post((sandbox ? SANDBOX_BASE_URL : BASE_URL) + path)
                    .header("Accept", "application/json")
                    .header("Content-Type", "application/json")
                    .header("APIKEY", apikey)
                    .body(body)
                    .asString();
            return parseResponse(sloppyResponse);
        } catch (UnirestException e) {
            logger.error("SplashPayments request failure", e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    private String put(String path, String apikey, String body, boolean sandbox) {
        try {
            HttpResponse<String> sloppyResponse = Unirest.put((sandbox ? SANDBOX_BASE_URL : BASE_URL) + path)
                    .header("Accept", "application/json")
                    .header("Content-Type", "application/json")
                    .header("APIKEY", apikey)
                    .body(body)
                    .asString();
            return parseResponse(sloppyResponse);
        } catch (UnirestException e) {
            logger.error("SplashPayments request failure", e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    private String parseResponse(HttpResponse<String> sloppyResponse) {
        if (sloppyResponse.getStatus() != 200) {
            MDC.put("gatewayResponse", sloppyResponse.getBody());
            MDC.put("gatewayResponseStatusText", sloppyResponse.getStatusText());
            MDC.put("gatewayResponseStatus", String.valueOf(sloppyResponse.getStatus()));
            logger.error("SplashPayments request failure");
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Failed to communicate with SplashPayments");
        } else {
            return sloppyResponse.getBody();
        }
    }

    private String buildApikeyBody(String login) {
        JsonObject body = new JsonObject();
        body.addProperty("login", login);
        body.addProperty("public", 0);
        return gson.toJson(body);
    }

    private String buildEntityBody(String clientName, GatewayConnection masterConnection, Business business) {
        // Add entity to request
        JsonObject entity = new JsonObject();
        entity.addProperty("login", masterConnection.getUsername());
        entity.addProperty("country", business.getAddress().getCountryCode());
        entity.addProperty("name", business.getName());
        entity.addProperty("type", business.getType());
        entity.addProperty("ein", business.getEin());
        entity.addProperty("email", business.getEmail());
        entity.addProperty("phone", business.getPhone());
        packAddress(entity, business.getAddress());
        entity.addProperty("website", business.getWebsite());
        if (clientName != null)
            entity.addProperty("custom", clientName);

        packFeeGroup(entity, masterConnection);
        packAccount(entity, business.getAccount());

        // Add merchant to request
        JsonObject merchant = new JsonObject();
        merchant.addProperty("new", 1);
        merchant.addProperty("annualCCSales", 0);
        if (business.getDba() != null) {
            merchant.addProperty("dba", business.getDba());
        }
        merchant.addProperty("mcc", business.getMerchantCategoryCode()); // TODO Adam has to get us approved for types, not sure what list or possible validation might exist
        merchant.addProperty("status", 1);

        // Add members to merchant
        JsonArray members = new JsonArray();
        JsonObject memberHolder;
        memberHolder = new JsonObject();
        memberHolder.addProperty("primary", 1);
        memberHolder.addProperty("first", business.getOwner().getFirstName());
        memberHolder.addProperty("last", business.getOwner().getLastName());
        memberHolder.addProperty("dob", business.getOwner().getDob());
        memberHolder.addProperty("title", "Owner");
        memberHolder.addProperty("ownership", MoneyUtil.convertToCents(business.getOwner().getOwnership())); // This field is in basis points
        memberHolder.addProperty("ssn", business.getOwner().getSsn());
        packAddress(memberHolder, business.getOwner().getAddress());
        memberHolder.addProperty("email", business.getOwner().getEmail());
        memberHolder.addProperty("phone", business.getOwner().getPhone());

        members.add(memberHolder);
        merchant.add("members", members);
        entity.add("merchant", merchant);

        return gson.toJson(entity);
    }

    private void packPaymentInfo(JsonObject body, Transaction transaction) {
        body.addProperty("description", buildSaleDescription(transaction));
        if (transaction.getSalesTax() != null) {
            body.addProperty("tax", MoneyUtil.convertToCents(transaction.getSalesTax())); // In cents
        }
        body.addProperty("total", MoneyUtil.convertToCents(transaction.getAmount())); // In cents

        packAddress(body, transaction.getBillingAddress());
    }

    private void packFeeGroup(JsonObject entity, GatewayConnection masterConnection) {
        if (masterConnection.getFeeGroupId() != null) {
            // Add org entities to attach fees to transactions for this merchant directly through splash
            JsonObject feeGroup = new JsonObject();
            feeGroup.addProperty("org", masterConnection.getFeeGroupId());
            JsonArray orgEntities = new JsonArray();
            orgEntities.add(feeGroup);
            entity.add("orgEntities", orgEntities);
        }
    }

    private void packAccount(JsonObject entity, Account businessAccount) {
        // Add accounts to request
        JsonArray accounts = new JsonArray();
        JsonObject account = new JsonObject();
        account.addProperty("routing", businessAccount.getRouting());
        account.addProperty("number", businessAccount.getNumber());
        account.addProperty("method", parseMethod(businessAccount.getType()));

        JsonObject accountWrapper = new JsonObject();
        accountWrapper.addProperty("primary", 1);
        accountWrapper.addProperty("status", 1);
        accountWrapper.add("account", account);
        accounts.add(accountWrapper);
        entity.add("accounts", accounts);
    }

    private String buildCheckSaleBody(String merchantId, Transaction transaction) {
        JsonObject body = new JsonObject();
        packPaymentInfo(body, transaction);
        body.addProperty("type", 7);
        body.addProperty("origin", 2);
        body.addProperty("merchant", merchantId);
        body.addProperty("signature", 0);

        JsonObject payment = new JsonObject();
        payment.addProperty("method", parseMethod(transaction.getBankAccount().getType()));
        payment.addProperty("routing", transaction.getBankAccount().getRouting());
        payment.addProperty("number", transaction.getBankAccount().getNumber());

        body.add("payment", payment);

        // TODO force verification?  body.addProperty("verify", 1);

        return gson.toJson(body);
    }

    private String buildCardSaleBody(String merchantId, Transaction transaction) {
        JsonObject body = new JsonObject();
        packPaymentInfo(body, transaction);
        body.addProperty("type", 1);
        body.addProperty("origin", 2);
        body.addProperty("merchant", merchantId);
        body.addProperty("signature", 0);
        if (StringUtils.isNotBlank(transaction.getOrderId())) {
            body.addProperty("order", transaction.getOrderId());
        }

        if (transaction.getCard().getToken() != null) {
            body.addProperty("token", transaction.getCard().getToken());
        } else if (transaction.getCard().getMagstripe() != null) {
            JsonObject card = new JsonObject();
            card.addProperty("track", transaction.getCard().getMagstripe());
            card.addProperty("cvv", transaction.getCard().getCode());
            body.add("payment", card);
            // TODO what about encryption or format?
        } else {
            body.add("payment", buildPaymentBody(transaction.getCard()));
        }
        return gson.toJson(body);
    }

    private String buildRefundBody(String merchantId, Transaction originalTransaction, Transaction refund, int type) {
        JsonObject body = new JsonObject();
        body.addProperty("merchant", merchantId);
        body.addProperty("fortxn", originalTransaction.getGatewayReferenceId());
        body.addProperty("type", type);
        body.addProperty("origin", 2);
        if (refund.getDescription() != null) {
            body.addProperty("description", (refund.getDescription().length() > 100 ? refund.getDescription().substring(0, 100) : refund.getDescription()));
        }
        body.addProperty("total", MoneyUtil.convertToCents(refund.getAmount())); // In cents
        if (refund.getSalesTax() != null) {
            body.addProperty("tax", MoneyUtil.convertToCents(refund.getSalesTax()));
        }


        return gson.toJson(body);
    }

    private String buildCustomerBody(TokenRequest tokenRequest){
        JsonObject body = new JsonObject();
        if (tokenRequest.getAddress() != null) {
            body.addProperty("first", tokenRequest.getAddress().getFirstName());
            body.addProperty("last", tokenRequest.getAddress().getLastName());
        }
        body.addProperty("email", tokenRequest.getEmail());
        return gson.toJson(body);
    }

    private String buildTokenBody(String customerId, TokenRequest tokenRequest) {
        JsonObject body = new JsonObject();
        body.addProperty("customer", customerId);
        body.add("payment", buildPaymentBody(tokenRequest.getCard()));
        return gson.toJson(body);
    }

    private JsonObject buildPaymentBody(Card card){
        JsonObject cardInfo = new JsonObject();
        cardInfo.addProperty("method", parseMethod(card.getType()));
        cardInfo.addProperty("number", card.getNumber());
        cardInfo.addProperty("cvv", card.getCode());
        cardInfo.addProperty("expiration", card.getExpirationDate());
        return cardInfo;
    }

    private void packAddress(JsonObject body, Address address) {
        if (address != null) {
            if (address.getFirstName() != null) {
                body.addProperty("first", address.getFirstName());
            }
            if (address.getLastName() != null) {
                body.addProperty("last", address.getLastName());
            }

            body.addProperty("address1", address.getLine1());
            if (!StringUtils.isBlank(address.getLine2())) {
                body.addProperty("address2", address.getLine2());
            }
            body.addProperty("city", address.getCity());
            body.addProperty("state", address.getState());
            body.addProperty("zip", address.getPostalCode());
            body.addProperty("country", address.getCountryCode());
            if (StringUtils.isNotBlank(address.getPhoneNumber())) {
                body.addProperty("phone", address.getPhoneNumber());
            }
            if (StringUtils.isNotBlank(address.getEmail())) {
                body.addProperty("email", address.getEmail());
            }
            if (StringUtils.isNotBlank(address.getCompany())) {
                body.addProperty("company", address.getCompany());
            }
        }
    }

    private Integer parseMethod(Card card) {
        return parseMethod(card.getType());
    }

    private Integer parseMethod(CardType cardType) {
        switch (cardType) {
            case AMERICAN_EXPRESS:
                return 1;
            case VISA:
                return 2;
            case MASTERCARD:
                return 3;
            case DINERS_CLUB:
                return 4;
            case DISCOVER:
                return 5;
            //TODO debit card?
            default:
                return null;
        }
    }

    private int parseMethod(String accountType) {
        switch (StringUtils.lowerCase(accountType)) {
            case "checking":
                return 8;
            case "savings":
                return 9;
            case "corporate-checking":
                return 10;
            case "corporate-savings":
                return 11;
            default:
                throw new ResponseException(HttpStatus.BAD_REQUEST, "Account type not supported: " + accountType);
        }
    }

    private void setStatusAndResult(GatewayConnection gatewayConnection, SplashTransaction splashTransaction, Transaction transaction) {
        /**
         * The status of the Transaction. Valid values are '0' (pending), '1' (approved), '2' (failed), '3' (captured), '4' (settled) and '5' (returned).
         */
        switch (splashTransaction.getStatus()) {
            case 0:
                // Decline all approval required transactions until we have a way to handle the case
                MDC.put("gatewayTransaction", GsonUtil.getGson().toJson(splashTransaction));
                logger.error("SplashPayments transaction approval required");
                transaction.setStatusCode("D");
                transaction.updateResultAndCode(TransactionResult.Declined.getResultCode());
                break;
            case 1:
            case 3:
            case 4:
                transaction.setStatusCode("P");
                transaction.updateResultAndCode(TransactionResult.Success.getResultCode());
                break;
            case 2:
                // Get error code
                transaction.setStatusCode("D");

                SplashBaseResponse<SplashTransactionResult> txnResultsResponse =
                        gson.fromJson(get("/txnResults", gatewayConnection.getPrivateKey(), gatewayConnection.getIsSandbox(),
                                "txn[equals]=" + splashTransaction.getId()), transactionResultResponseType);

                if (txnResultsResponse.getDataList().isEmpty()) {
                    transaction.updateResultAndCode(TransactionResult.Unexpected.getResultCode());
                    MDC.put("gatewayTransaction", GsonUtil.getGson().toJson(splashTransaction));
                    logger.error("SplashPayments transaction missing results");
                    return;
                }

                for (SplashTransactionResult splashTransactionResult : txnResultsResponse.getDataList()) {
                    if (splashTransactionResult.getCode() == 2) {
                        if (splashTransactionResult.getMessage() != null) {
                            switch (splashTransactionResult.getMessage()) {
                                case "Decline CVV2/CID Fail":
                                    transaction.updateResultAndCode(TransactionResult.Invalid_Code.getResultCode());
                                    return;
                                case "Invalid Account Number":
                                    transaction.updateResultAndCode(TransactionResult.Invalid_Account_Number.getResultCode());
                                    return;
                                case "Insufficient Funds":
                                    transaction.updateResultAndCode(TransactionResult.Insufficient_Funds.getResultCode());
                                    return;
                                case "Do Not Honor":
                                case "No such issuer":
                                    transaction.updateResultAndCode(TransactionResult.Declined.getResultCode());
                                    return;
                                default:
                                    transaction.updateResultAndCode(TransactionResult.Unexpected.getResultCode());
                                    return;
                            }
                        } else {
                            transaction.updateResultAndCode(TransactionResult.Declined.getResultCode());
                        }
                        break;
                    }
                }
                break;
            default:
                MDC.put("gatewayTransaction", GsonUtil.getGson().toJson(splashTransaction));
                logger.error("SplashPayments transaction status not parsed");
                transaction.updateResultAndCode(TransactionResult.Unexpected.getResultCode());
                transaction.setStatusCode("E");
                break;
        }
    }

    private String buildSaleDescription(Transaction transaction) {
        String description = String.format(Locale.US, "[%s]%s", transaction.getPayeeUserId(), transaction.getDescription());
        if (description.length() > 100) {
            description = description.substring(0, 100); // Field can only be 100 characters
        }
        return description;
    }
}
