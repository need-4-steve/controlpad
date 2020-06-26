package com.controlpad.payman_processor.gateway;

import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.controlpad.payman_common.util.MoneyUtil;
import com.controlpad.payman_processor.transaction_processing.TransactionUpdateResult;
import com.google.gson.Gson;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.mashape.unirest.http.HttpResponse;
import com.mashape.unirest.http.Unirest;
import com.mashape.unirest.http.exceptions.UnirestException;
import com.mashape.unirest.request.GetRequest;
import com.mashape.unirest.request.HttpRequestWithBody;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.joda.time.DateTimeZone;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.math.BigDecimal;
import java.math.RoundingMode;
import java.util.*;

import static com.controlpad.payman_processor.transaction_processing.TransactionUpdateResult.*;

public class SplashPayments implements Gateway {

    private static final String BASE_URL = "https://api.splashpayments.com";
    private static final String SANDBOX_BASE_URL = "https://test-api.splashpayments.com";
    private static final String DATE_TIME_FORMAT = "YYYY-MM-dd HH:mm:ss";

    private final Logger logger = LoggerFactory.getLogger(SplashPayments.class);
    private final DateTimeFormatter formatter = DateTimeFormat.forPattern(DATE_TIME_FORMAT);

    private Gson gson = new Gson();

    @Override
    public int updateTransactionStatus(SqlSession session, GatewayConnection gatewayConnection,
                                       Transaction currentTransaction, String clientId) {
        int result = 0;

        JsonObject transaction = (JsonObject) get("/txns/" + currentTransaction.getGatewayReferenceId(), gatewayConnection, null);
        if (transaction == null) {
            // TODO mark transaction as error?
            return SKIP;
        }
        switch (transaction.get("status").getAsInt()) {
            case 0: // Pending
            case 1: // Approved
                return SKIP; // We don't want to update transactions that are pending
            case 2:
                currentTransaction.setStatusCode("E");
                break;
            case 3: // Captured   settling at capture time, which is when fees come out and balance is affected
            case 4: // Settled
                currentTransaction.setStatusCode("S");
                break;
            case 5:
                currentTransaction.setStatusCode("R");
                break;
        }

        if (!transaction.has("batch") || transaction.get("batch").isJsonNull()) {
            if (currentTransaction.getTransactionType().equals("refund")){
                // For 'voids' that happen after capture time but before settlement time, no batch will be present
                // Assigning original transaction batch id just to fake it so that processing will happen
                Transaction oldTransaction = session.getMapper(TransactionMapper.class).findById(currentTransaction.getForTxnId());
                currentTransaction.setBatchId(oldTransaction.getBatchId());
                session.getMapper(TransactionMapper.class).updateTransactionStatusAndBatch(currentTransaction);
                return UPDATED;
            }
            logger.error("Transaction batch missing: {}", currentTransaction.getId());
            return ERROR;
        }
        String externalBatchId = transaction.get("batch").getAsString();

        TransactionBatchMapper transactionBatchMapper = session.getMapper(TransactionBatchMapper.class);
        Long batchId = transactionBatchMapper.findTransactionBatchIdForExternalId(currentTransaction.getGatewayConnectionId(), externalBatchId);
        if (batchId == null) { // Create a gateway batch record
            JsonObject batch = (JsonObject) get("/batches/" + externalBatchId, gatewayConnection, null);
            if (batch == null || !StringUtils.equalsIgnoreCase(batch.get("status").getAsString(), "processed")) { // If batch isn't settled
                return TransactionUpdateResult.STOP;
            }
            TransactionBatch transactionBatch = new TransactionBatch(currentTransaction.getGatewayConnectionId(), externalBatchId, null);
            transactionBatchMapper.insert(transactionBatch);
            currentTransaction.setBatchId(transactionBatch.getId());
            result ^= BATCH_CREATED;
        } else {  // Set batch id
            currentTransaction.setBatchId(batchId);
        }

        session.getMapper(TransactionMapper.class).updateTransactionStatusAndBatch(currentTransaction);
        result ^= UPDATED;

        return result;
    }

    @Override
    public List<FeeEntry> getInternalFees(GatewayConnection gatewayConnection, Transaction currentTransaction, Long userBalanceId) {
        // Find all fees collected during settlement
        Map<String, String> headers = new HashMap<>();

        // Pulling with from entity which actually retrieves the positive adjustment to controlpad because that is the only way the fee id will be attached
        headers.put("SEARCH",
                String.format(Locale.US, "eventId[equals]=%s&txn[exact]=null&fromentity[equals]=%s",
                        currentTransaction.getGatewayReferenceId(), gatewayConnection.getEntityId()));

        List<FeeEntry> feeEntries = new ArrayList<>();
        JsonElement response = get("/entries", gatewayConnection, headers);
        if (response == null) {
            throw new RuntimeException("Failed to get fees for transaction: " + currentTransaction.getId());
        }
        JsonArray entries;
        if (response instanceof JsonArray) {
            entries = (JsonArray) response;
        } else {
            entries = new JsonArray();
            if (response != null) {
                entries.add(response);
            }
        }
        if (entries.size() > 0) {
            JsonObject entry;
            for (int i = 0; i < entries.size(); i++) {
                entry = entries.get(i).getAsJsonObject();
                String feeId = null;
                if (entry.has("fee") && !entry.get("fee").isJsonNull()) {
                    feeId = entry.get("fee").getAsString();
                }
                feeEntries.add(new FeeEntry(userBalanceId, MoneyUtil.convertFromCents(entry.get("amount").getAsDouble()).negate(),
                        currentTransaction.getId(), null, null, PaymentType.FEE.slug, true, feeId));
            }
        }
        return feeEntries;
    }

    @Override
    public BigDecimal getSubAccountBalance(GatewayConnection gatewayConnection) {
        JsonObject entity = (JsonObject)get("/entities/"+gatewayConnection.getEntityId()+"?expand[funds][]", gatewayConnection, null);
        if (entity != null && entity.has("funds")) {
            JsonArray funds = entity.getAsJsonArray("funds");
            if (funds.size() > 0) {
                JsonObject fund =  funds.get(0).getAsJsonObject();
                if (!fund.get("inactive").getAsBoolean() && !fund.get("frozen").getAsBoolean()) {
                    return MoneyUtil.convertFromCents(fund.get("available").getAsDouble()).setScale(2, RoundingMode.FLOOR);
                }
            }
        }
        return BigDecimal.ZERO;
    }

    @Override
    public String createWithdraw(GatewayConnection gatewayConnection, Payment payment) {
        Map<String, String> accountHeaders = new HashMap<>();
        accountHeaders.put("SEARCH", String.format("entity[equals]=%s&primary[equals]=1", gatewayConnection.getEntityId()));
        JsonObject account = (JsonObject) get("/accounts", gatewayConnection, accountHeaders);
        if (account == null || !account.has("token") || account.get("inactive").getAsInt() == 1) {
            return null;
        }

        JsonObject withdraw = new JsonObject();
        withdraw.addProperty("entity", gatewayConnection.getEntityId());
        withdraw.addProperty("login", gatewayConnection.getUsername());
        withdraw.addProperty("amount", MoneyUtil.convertToCents(payment.getAmount()));
        withdraw.addProperty("schedule", 5);
        withdraw.addProperty("um", 2);
        withdraw.addProperty("account", account.get("token").getAsString());
        withdraw.addProperty("currency", "USD");
        withdraw.addProperty("start", DateTime.now().toString("yyyyMMdd"));

        JsonObject responseBody = (JsonObject) post("/payouts", gatewayConnection.getPrivateKey(), gson.toJson(withdraw), gatewayConnection.getIsSandbox());
        if (responseBody != null && responseBody.has("id")) {
            return responseBody.get("id").getAsString();
        } else {
            logger.error("Failed to create withdraw for payment: " + payment.getId() + " | " + gson.toJson(responseBody));
            return null;
        }
    }

    private String createFee(GatewayConnection gatewayConnection, Payment payment, PaymentType paymentType) {
        String name;
        switch (paymentType) {
            case SALES_TAX:
                name = "Tax";
                break;
            case CONSIGNMENT:
                name = "Consignment";
                break;
            default:
                    logger.error("Wrong fee type: " + paymentType.slug);
                    return null;
        }
        JsonObject fee = new JsonObject();
        fee.addProperty("entity", gatewayConnection.getMasterConnection().getEntityId());
        fee.addProperty("forentity", gatewayConnection.getEntityId());
        fee.addProperty("name", String.format(Locale.US, "%s Payment: %s", name, payment.getId()));
        fee.addProperty("schedule", 5);
        fee.addProperty("um", 2);
        fee.addProperty("amount", MoneyUtil.convertToCents(payment.getAmount()));
        fee.addProperty("currency", "USD");
        fee.addProperty("start", DateTime.now().toString("yyyyMMdd"));

        JsonObject responseBody = (JsonObject) post("/fees", gatewayConnection.getPrivateKey(), gson.toJson(fee), gatewayConnection.getIsSandbox());
        if (responseBody != null && responseBody.has("id")) {
            return responseBody.get("id").getAsString();
        } else {
            logger.error("Failed to create fee: " + gson.toJson(responseBody));
            throw new RuntimeException("Failed to create fee id");
        }
    }

    @Override
    public String createConsignmentFee(GatewayConnection gatewayConnection, Payment payment) {
        return createFee(gatewayConnection, payment, PaymentType.CONSIGNMENT);
    }

    @Override
    public String createTaxFee(GatewayConnection gatewayConnection, Payment payment) {
        return createFee(gatewayConnection, payment, PaymentType.SALES_TAX);
    }

    @Override
    public boolean checkTransactionBatch(SqlSession clientSession, GatewayConnection gatewayConnection, TransactionBatch transactionBatch) {
        JsonObject batch = (JsonObject) get(String.format(Locale.US, "/batches/%s", transactionBatch.getExternalId()), gatewayConnection, null);
        if (batch != null && batch.has("status") && StringUtils.equalsIgnoreCase(batch.get("status").getAsString(), "processed")) {
            // Convert from Splash Payments server time, modified is the only date time given that can match settlement
            String modifiedAtString = batch.get("modified").getAsString();
            if (modifiedAtString.contains(".")) {
                modifiedAtString = modifiedAtString.substring(0, modifiedAtString.indexOf("."));
            }
            DateTime modifiedAt = DateTime.parse(modifiedAtString, formatter);
            modifiedAt = modifiedAt.withZoneRetainFields(DateTimeZone.forTimeZone(TimeZone.getTimeZone("America/New_York")));
            modifiedAt = modifiedAt.withZone(DateTimeZone.UTC);
            transactionBatch.setSettledAt(modifiedAt);
            // TODO transaction count
            // TODO net amount
            // TODO looks like this would involve iterating through the whole transaction list for the batch and calculating manually
            return true;
        } else {
            return false;
        }
    }

    private JsonElement get(String path, GatewayConnection gatewayConnection, Map<String, String> headers) {
        try {
            GetRequest request = Unirest.get((gatewayConnection.getIsSandbox() ? SANDBOX_BASE_URL : BASE_URL) + path);
            if (headers != null)
                request.headers(headers);
            request.header("Accept", "application/json");
            request.header("APIKEY", gatewayConnection.getPrivateKey());
            return parseResponse(request.asString());
        } catch (UnirestException e) {
            logger.error("SplashPayments request failure", e);
            return null;
        }
    }

    private JsonElement post(String path, String apikey, String body, boolean sandbox) {
        try {
            HttpRequestWithBody postRequest = Unirest.post((sandbox ? SANDBOX_BASE_URL : BASE_URL) + path);
            postRequest.header("Accept", "application/json");
            postRequest.header("Content-Type", "application/json");
            postRequest.header("APIKEY", apikey);
            postRequest.body(body);
            return parseResponse(postRequest.asString());
        } catch (UnirestException e) {
            logger.error("SplashPayments request failure", e);
            return null;
        }
    }

    private JsonElement parseResponse(HttpResponse<String> sloppyResponse) {
        if (sloppyResponse.getStatus() != 200) {
            logger.error(String.format(Locale.US, "Failed to communicate with splash payments: %s", gson.toJson(sloppyResponse)));
            return null;
        }
        JsonObject responseBody = gson.fromJson(sloppyResponse.getBody(), JsonObject.class).getAsJsonObject("response");
        if(responseBody != null){
            if (responseBody.has("data")) {
                JsonArray data = responseBody.getAsJsonArray("data");
                if (data.size() == 1) {
                    return data.get(0).getAsJsonObject();
                } else if (data.size() > 1) {
                    return data;
                }
            }
            // TODO it's possible but not likely to be missing fees, especially with testing, touch this up
            logger.error(String.format(Locale.US, "Failed to parse SplashPayments response: %s", gson.toJson(responseBody)));
        }
        return null;
    }

}
