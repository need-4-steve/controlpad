package com.controlpad.pay_fac.gateway;

import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.gatewayconnection.SubAccountUser;
import com.controlpad.pay_fac.payment_info.TokenRequest;
import com.controlpad.pay_fac.report.gateway.GatewayTransaction;
import com.controlpad.pay_fac.tokenization.TokenizeCardResponse;
import com.controlpad.pay_fac.transaction.EMVIOToken;
import com.controlpad.pay_fac.transaction.TransactionUtil;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionType;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction.TransactionResult;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.util.GsonUtil;
import com.mashape.unirest.http.HttpResponse;
import com.mashape.unirest.http.JsonNode;
import com.mashape.unirest.http.Unirest;
import com.mashape.unirest.http.exceptions.UnirestException;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.json.JSONObject;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;
import org.springframework.http.HttpStatus;

import java.math.BigDecimal;
import java.math.BigInteger;
import java.util.List;

public class EMVIO implements Gateway {

    private final Logger logger = LoggerFactory.getLogger(EMVIO.class);

    // Custom 1 = sales tax
    // Custom 2 = shipping
    private IDUtil idUtil;


    public EMVIO(IDUtil idUtil) {
        this.idUtil = idUtil;
    }

    @Override
    public Transaction saleCard(SqlSession session, GatewayConnection gatewayConnection, String remoteAddress, Transaction transaction, TransactionType transactionType) {
        return null; // unused
    }

    @Override
    public Transaction saleCheck(SqlSession session, GatewayConnection gatewayConnection, Transaction transaction, String remoteAddress, TransactionType transactionType) {
        return null;// unused
    }

    @Override
    public Transaction updateTransactionStatus(SqlSession session, Transaction currentTransaction, GatewayConnection gatewayConnection, String remoteAddress) {
        return null;
    }

    @Override
    public Transaction refundTransaction(GatewayConnection gatewayConnection, Transaction originalTransaction, Transaction refund, String remoteAddress, String clientId) {
        return null; // TODO implement
    }

    @Override
    public TokenizeCardResponse tokenizeCard(TokenRequest creditTokenRequestData, GatewayConnection gatewayConnection, String remoteAddress) {
        return null; // TODO how can we tokenize with the form?
    }

    @Override
    public Boolean isAccountSame(GatewayConnection currentConnection, GatewayConnection newConnection, SqlSession session) {
        return null; // TODO implement
    }

    @Override
    public boolean checkCredentials(GatewayConnection gatewayConnection) {
        return false; // TODO implement
    }

    @Override
    public CommonResponse createSubAccount(String clientName, SqlSession clientSession, GatewayConnection masterConnection, SubAccountUser subAccountUser) {

        try {
            JSONObject requestBody = new JSONObject();
            requestBody.put("contact", subAccountUser.getBusiness().getAccount().getName());
            requestBody.put("routing", subAccountUser.getBusiness().getAccount().getRouting());
            requestBody.put("account", subAccountUser.getBusiness().getAccount().getNumber());

            HttpResponse<JsonNode> response = Unirest.post("https://api2.emvio.com/v2/representative")
                    .header("Content-Type", "application/json")
                    .header("Accept", "application/json")
                    .header("X-Authorization", masterConnection.getPrivateKey())
                    .body(requestBody).asJson();

            if (response.getStatus() >= 400) {
                MDC.put("gatewayRequest", GsonUtil.getGson().toJson(requestBody));
                MDC.put("gatewayResponse", GsonUtil.getGson().toJson(response.getBody()));
                logger.error("EMVIO create account error");
                return new CommonResponse(false, 99, "Unexpected error");
            }

            JSONObject data = response.getBody().getObject().getJSONObject("data");

            String merchantId = Long.toString(data.getLong("id")); // emvio rep id

            // Save user gateway connection
            GatewayConnection subAccountGC = new GatewayConnection(subAccountUser.getTeamId(), subAccountUser.getUserId(),
                    subAccountUser.getBusiness().getName(), masterConnection.getUsername(), null,
                    merchantId, masterConnection.getPrivateKey(), GatewayConnectionType.NEXIO_PAY.slug,
                    masterConnection.getIsSandbox(), false, true, false, true,
                    masterConnection.getId(), false, true);
            subAccountGC.setPin(masterConnection.getPin());

            clientSession.getMapper(GatewayConnectionMapper.class).insert(subAccountGC);
            return new CommonResponse(true, 1, "Success");
        } catch (UnirestException failure) {
            MDC.put("subAccountUser", GsonUtil.getGson().toJson(subAccountUser));
            logger.error("EMVIO create account error", failure);
            return new CommonResponse(false, 99, "Unexpected error");
        }
    }

    @Override
    public boolean updateSubAccount(GatewayConnection gatewayConnection, SubAccountUser subAccountUser) {
        try {
            JSONObject requestBody = new JSONObject();
            requestBody.put("contact", subAccountUser.getBusiness().getAccount().getName());
            requestBody.put("routing", subAccountUser.getBusiness().getAccount().getRouting());
            requestBody.put("account", subAccountUser.getBusiness().getAccount().getNumber());

            HttpResponse<JsonNode> response = Unirest.put(String.format("https://api2.emvio.com/v2/representative/%s", gatewayConnection.getMerchantId()))
                    .header("Content-Type", "application/json")
                    .header("Accept", "application/json")
                    .header("X-Authorization", gatewayConnection.getPrivateKey())
                    .body(requestBody).asJson();

            if (response.getStatus() >= 400) {
                MDC.put("gatewayResponse", GsonUtil.getGson().toJson(response.getBody()));
                MDC.put("gatewayResponseStatus", String.valueOf(response.getStatus()));
                MDC.put("gatewayRequest", GsonUtil.getGson().toJson(requestBody));
                logger.error("EMVIO update account error");
                return false;
            }
            return true;
        } catch (UnirestException failure) {
            logger.error("EMVIO update account error", failure);
            return false;
        }
    }

    @Override
    public Transaction importTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction) {
        try {
            HttpResponse<JsonNode> response = Unirest.get(String.format("https://api2.emvio.com/v2/transactions/%s", transaction.getGatewayReferenceId()))
                    .header("Content-Type", "application/json")
                    .header("Accept", "application/json")
                    .header("X-Authorization", gatewayConnection.getPrivateKey())
                    .asJson();

            if (response.getStatus() >= 400) {
                MDC.put("gatewayResponse", GsonUtil.getGson().toJson(response.getBody()));
                MDC.put("gatewayResponseStatus", String.valueOf(response.getStatus()));
                logger.error("EMVIO import transaction error");
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
            }

            JSONObject data = response.getBody().getObject().getJSONObject("data");
            JSONObject status = data.getJSONObject("status");

            JSONObject details = data.getJSONObject("details");
            String description = (transaction.getDescription() != null ? transaction.getDescription() : details.getString("description"));
            String name;
            if (transaction.getAccountHolder() != null) {
                name = transaction.getAccountHolder();
            } else if (data.has("customer") && data.getJSONObject("customer").has("name")
                    && !data.getJSONObject("customer").isNull("name")) {
                name = data.getJSONObject("customer").getString("name");
            } else {
                name = null;
            }

            BigDecimal salesTax = null;
            BigDecimal shipping = null;
            if (new Money(details.getString("amount")).compareTo(transaction.getAmount()) != 0) {
                throw new ResponseException(HttpStatus.BAD_REQUEST, "Transaction amount doesn't match");
            }
            if (details.has("custom")) {
                JSONObject custom = details.getJSONObject("custom");
                if (custom.has("custom1")) {
                    salesTax = new Money(custom.getDouble("custom1"));
                } else {
                    salesTax = transaction.getSalesTax();
                }

                if (custom.has("custom2")) {
                    shipping = new Money(custom.getDouble("custom2"));
                } else {
                    shipping = transaction.getShipping();
                }
            }

            Transaction saveTransaction = new Transaction(null,
                    transaction.getPayeeUserId(), transaction.getPayerUserId(), transaction.getTeamId(), transaction.getGatewayReferenceId(),
                    transaction.getTransactionType(), new Money(details.getDouble("amount")), salesTax, shipping,
                    null, null, gatewayConnection.getId(), description, name
            );

            saveTransaction.setSwiped(false);

            setResultAndStatus(status, saveTransaction);

            TransactionUtil.insertTransaction(sqlSession, saveTransaction, idUtil, null);

            return saveTransaction;
        } catch (UnirestException failure) {
            logger.error("EMVIO import exception", failure);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    @Override
    public Transaction captureTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction, String clientId) {
        try {
            HttpResponse<JsonNode> response = Unirest.get(String.format("https://api2.emvio.com/v2/transactions/%s/capture", transaction.getGatewayReferenceId()))
                    .header("Content-Type", "application/json")
                    .header("Accept", "application/json")
                    .header("X-Authorization", gatewayConnection.getPrivateKey())
                    .asJson();

            JSONObject data = response.getBody().getObject().getJSONObject("data");
            JSONObject status = data.getJSONObject("status");

            setResultAndStatus(status, transaction);
            sqlSession.getMapper(TransactionMapper.class).updateTransactionStatusOrderId(transaction);
            return transaction;
        } catch (UnirestException failure) {
            failure.printStackTrace();
            MDC.put("transactionId", transaction.getId());
            logger.error("EMVIO captureTransaction exception", failure);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    @Override
    public Object getTransaction(GatewayConnection gatewayConnection, Transaction transaction) {
        return null;
    }

    @Override
    public List<TransactionBatch> searchGatewayBatches(SqlSession clientSession, GatewayConnection gatewayConnection, DateTime startDate, DateTime endDate, BigInteger page, BigInteger limit) {
        return null;
    }

    @Override
    public List<GatewayTransaction> searchTransactions(SqlSession clientSession, GatewayConnection gatewayConnection, String externalBatchId, BigInteger page, BigInteger limit) {
        return null;
    }

    public EMVIOToken createPayment(GatewayConnection gatewayConnection, Transaction transaction, String clientId) {
        JSONObject data = new JSONObject();
        data.put("version", "portal");
        data.put("key", gatewayConnection.getPin());
        data.put("amount", transaction.getAmount());
        data.put("mode", "authonly");
        data.put("repid", gatewayConnection.getMerchantId());
        data.put("description", transaction.getDescription());

        if (transaction.getSalesTax() != null) {
            data.put("custom1", transaction.getSalesTax());
        }
        if (transaction.getShipping() != null) {
            data.put("custom2", transaction.getShipping());
        }
        if (transaction.getPayerUserId() != null) {
            data.put("custid", transaction.getPayerUserId());
            if (transaction.getBillingAddress() != null) {
                data.put("name", transaction.getBillingAddress().getFullName());
                data.put("address1", transaction.getBillingAddress().getLine1());
                data.put("address2", transaction.getBillingAddress().getLine2());
                data.put("city", transaction.getBillingAddress().getCity());
                data.put("state", transaction.getBillingAddress().getState());
                data.put("zip", transaction.getBillingAddress().getPostalCode());
                data.put("country", transaction.getBillingAddress().getCountryCode());
            }
        }

        JSONObject requestBody = new JSONObject();
        requestBody.put("api_key", gatewayConnection.getPrivateKey());
        requestBody.put("data", data);

        try {
            HttpResponse<JsonNode> response = Unirest.post("https://plugin.emvio.com/api/request")
                    .header("Content-Type", "application/json")
                    .header("Accept", "application/json")
                    .body(requestBody).asJson();

            if (response.getStatus() >= 400 || response.getBody().getObject().getBoolean("error")) {
                MDC.put("gatewayResponse", GsonUtil.getGson().toJson(response.getBody()));
                MDC.put("gatewayResponseStatus", String.valueOf(response.getStatus()));
                MDC.put("gatewayRequest", GsonUtil.getGson().toJson(requestBody));
                logger.error("EMVIO create payment error");
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
            }

            JSONObject responseData = response.getBody().getObject().getJSONObject("response_data");
            return new EMVIOToken(responseData.getString("token"), responseData.getLong("expire"));
        } catch (UnirestException failure) {
            MDC.put("gatewayRequest", GsonUtil.getGson().toJson(requestBody));
            logger.error("EMVIO createPayment exception", failure);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    private void setResultAndStatus(JSONObject status, Transaction transaction) {
        TransactionResult resultCode;
        String statusCode;

//        if (status.getBoolean("error")) {  //TODO FML this will be changing after I reported a bug
//            logger.error("EMVIO response error\nReferenceId: {}\nStatus({}): {}",
//                    transaction.getGatewayReferenceId(), transaction.getGatewayReferenceId(), GsonUtil.getGson().toJson(status));
//            statusCode = "E";
//            resultCode = TransactionResult.Unexpected;
//        } else
        if (StringUtils.equals(status.getString("resultCode"), "A")) {
            // Approved
            resultCode = TransactionResult.Success;
            if (StringUtils.equals(status.getString("result"), "authonly")) {  // TODO double check this
                statusCode = "A"; // Accepted
            } else {
                statusCode = "P"; // Pending 'Sale'
            }
        } else {
            // If not accepted it's a decline. Logging codes since there isn't any docs
            statusCode = "D";
            if (!status.has("error")) {
                MDC.put("gatewayTransactionStatus", GsonUtil.getGson().toJson(status));
                logger.error("EMVIO status structure changed");
                MDC.remove("gatewayTransactionStatus");
                resultCode = TransactionResult.Unexpected;
            } else {
                switch (status.getString("error")) {
                    default:
                        MDC.put("gatewayTransactionStatus", GsonUtil.getGson().toJson(status));
                        logger.error("EMVIO unexpected error code");
                        MDC.remove("gatewayTransactionStatus");
                        resultCode = TransactionResult.Unexpected;
                        statusCode = "E";
                }
            }
        }
        transaction.updateResultAndCode(resultCode.getResultCode());
        transaction.setStatusCode(statusCode);
    }
}
