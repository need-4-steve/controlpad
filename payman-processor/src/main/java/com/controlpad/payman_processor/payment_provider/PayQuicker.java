package com.controlpad.payman_processor.payment_provider;

import com.controlpad.payman_common.merchant.Merchant;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.payment_provider.PaymentProvider;
import com.controlpad.payman_common.util.GsonUtil;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.mashape.unirest.http.HttpResponse;
import com.mashape.unirest.http.JsonNode;
import com.mashape.unirest.http.Unirest;
import com.mashape.unirest.http.exceptions.UnirestException;
import org.apache.commons.lang3.BooleanUtils;
import org.apache.commons.lang3.StringUtils;
import org.json.JSONArray;
import org.json.JSONObject;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;

import java.util.Base64;

public class PayQuicker implements PaymentProviderInterface {

    private static final Logger logger = LoggerFactory.getLogger(PayQuicker.class);

    private String tokenUrlFormat = "https://identity.mypayquicker.%s/core/connect/token";
    private String paymentUrlFormat = "https://platform.mypayquicker.%s/api/v1/companies/accounts/payments";

    @Override
    public Payment createPayment(PaymentProvider paymentProvider, Payment payment, Merchant merchant) {
        String token = getToken(paymentProvider);
        if (merchant == null || StringUtils.isBlank(merchant.getEmail())) {
            // Skip user if not yet invited, this should probably not happen in our flow though
            logger.error("Merchant email not found during payquicker payout | UserId {}", payment.getUserId());
            return null;
        }
        if (token == null) {
            return null;
        }

        try {
            JSONObject money = new JSONObject();
            money.put("amount", payment.getAmount());
            money.put("currency", "Currency_USD");

            JSONObject paymentObject = new JSONObject();
            paymentObject.put("userCompanyAssignedUniqueKey", payment.getUserId());
            paymentObject.put("fundingAccountPublicId", paymentProvider.getCredentials().getFundingAccountPublicId());
            paymentObject.put("userNotificationEmailAddress", merchant.getEmail());
            paymentObject.put("monetary", money);
            paymentObject.put("issuePlasticCard", true);

            JSONArray paymentArray = new JSONArray();
            paymentArray.put(paymentObject);

            JSONObject requestBody = new JSONObject();
            requestBody.put("Payments", paymentArray);


            HttpResponse<String> response = Unirest.post(formatUrl(paymentUrlFormat, paymentProvider))
                    .header("Authorization", "Bearer " + token)
                    .header("Accept", "application/json;charset=utf-8")
                    .header("Content-Type", "application/json")
                    .header("X-MyPayQuicker-Version", "10-11-2016")
                    .body(requestBody)
                    .asString();

            if (response.getStatus() != 201 && response.getStatus() != 200) {
                MDC.put("responseStatusCode", String.valueOf(response.getStatus()));
                MDC.put("responseStatus", response.getStatusText());
                MDC.put("response", response.getBody());
                logger.error("Failed to communicate with payQuicker");
                MDC.remove("responseStatusCode");
                MDC.remove("responseStatus");
                MDC.remove("response");
                return null;
            }

            JsonElement responseBody = GsonUtil.getGson().fromJson(response.getBody(), JsonElement.class);

            JsonObject paymentResponse = ((JsonArray)responseBody).get(0).getAsJsonObject().get("payments").getAsJsonArray()
                    .get(0).getAsJsonObject();

            switch (paymentResponse.get("transactionStatusType").getAsString()) {
                case "TransactionStatusType_Complete":
                case "TransactionStatusType_Pending":
                case "TransactionStatusType_Scheduled":
                    // Transfer was accepted
                    String transactionPublicId = paymentResponse.get("transactionPublicId").getAsString();
                    payment.setReferenceId(transactionPublicId);
                    return payment;
                default:
                    logger.error("Failed to send a payment with payquicker: " + GsonUtil.getGson().toJson(responseBody));
                    return null;
            }
        } catch (UnirestException responseException) {
            logger.error("Failed to communicate with payquicker", responseException);
            return null;
        } catch (Exception e) {
            logger.error(e.getMessage(), e);
            return null;
        }
    }

    /**
     *
     * @param paymentProvider
     * @return Token contains fields 'access_token', 'expires_in', 'token_type'
     */
    public String getToken(PaymentProvider paymentProvider) {
        try {
            HttpResponse<JsonNode> response = Unirest.post(formatUrl(tokenUrlFormat, paymentProvider))
                    .header("Content-Type", "application/x-www-form-urlencoded")
                    .header("Authorization", "Basic " + Base64.getEncoder().encodeToString(String.format("%s:%s", paymentProvider.getCredentials().getId(), paymentProvider.getCredentials().getPrivateKey()).getBytes()))
                    .header("Accept", "application/json;charset=utf-8")
                    .body("grant_type=client_credentials&scope=api+useraccount_balance+useraccount_debit+useraccount_payment+useraccount_invitation")
                    .asJson();

            if (response.getStatus() != 200) {
                MDC.put("responseStatusCode", String.valueOf(response.getStatus()));
                MDC.put("responseStatus", response.getStatusText());
                MDC.put("response", GsonUtil.getGson().toJson(response.getBody()));
                logger.error("Failed to communicate with payQuicker");
                MDC.remove("responseStatusCode");
                MDC.remove("responseStatus");
                MDC.remove("response");
                return null;
            }

            return response.getBody().getObject().getString("access_token");
        } catch (Exception e) {
            logger.error(e.getMessage(), e);
        }
        return null;
    }

    private String formatUrl(String urlFormat, PaymentProvider paymentProvider) {
        return String.format(urlFormat, (BooleanUtils.isTrue(paymentProvider.getCredentials().isSandbox()) ? "xyz" : "com"));
    }


// payment response format example
//[
//    {
//        "payments": [
//        {
//            "transactionPublicId": "DEMS02RWO42KLP64O",
//                "authDate": "2017-11-01T16:00:31Z",
//                "monetary": {
//            "amount": -0.02,
//                    "currency": "Currency_USD",
//                    "language": "Languages_EN_US",
//                    "formattedAmount": "($0.02) USD"
//        },
//            "transactionStatusType": "TransactionStatusType_Complete"
//        }
//        ]
//    }
//]
}
