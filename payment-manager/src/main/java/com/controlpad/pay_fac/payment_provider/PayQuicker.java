package com.controlpad.pay_fac.payment_provider;

import com.controlpad.pay_fac.exceptions.ResponseException;
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
import org.json.JSONObject;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;
import org.springframework.http.HttpStatus;

import java.util.Base64;

public class PayQuicker implements PaymentProviderInterface {

    private final Logger logger = LoggerFactory.getLogger(PayQuicker.class);

    private String tokenUrlFormat = "https://identity.mypayquicker.%s/core/connect/token";
    private String invitationUrlFormat = "https://platform.mypayquicker.%s/api/v1/companies/users/invitations";

    public JsonObject createInvitation(PaymentProvider paymentProvider, PayquickerInvitationRequest invitationRequest) {
        String token = getToken(paymentProvider);

        try {
            JSONObject requestBody = new JSONObject();
            requestBody.put("userCompanyAssignedUniqueKey", invitationRequest.getUserId());
            requestBody.put("userNotificationEmailAddress", invitationRequest.getEmail());
            requestBody.put("firstName", invitationRequest.getFirstName());
            requestBody.put("lastName", invitationRequest.getLastName());
            requestBody.put("fundingAccountPublicId", paymentProvider.getCredentials().getFundingAccountPublicId());
            requestBody.put("issuePlasticCard", true);

            HttpResponse<String> response = Unirest.post(formatUrl(invitationUrlFormat, paymentProvider))
                    .header("Authorization", "Bearer " + token)
                    .header("Accept", "application/json;charset=utf-8")
                    .header("Content-Type", "application/json")
                    .header("X-MyPayQuicker-Version", "10-11-2016")
                    .body(requestBody)
                    .asString();

            if (response.getStatus() != 201 && response.getStatus() != 200) {
                MDC.put("payquickerResponse", response.getBody());
                MDC.put("payquickerResponseStatus", String.valueOf(response.getStatus()));
                MDC.put("invitationRequest", GsonUtil.getGson().toJson(invitationRequest));
                logger.error("Payquicker failed to create invitation");
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Failed to authenticate with payquicker");
            }

            JsonElement responseBody = GsonUtil.getGson().fromJson(response.getBody(), JsonElement.class);

            if (responseBody.isJsonArray()) {
                JsonObject invitationResult = ((JsonArray)responseBody).get(0).getAsJsonObject();
                invitationResult.addProperty("invitationUrl",
                        String.format("https://%s.mypayquicker.com/Welcome?invitationId=%s",
                                paymentProvider.getSubdomain(),
                                invitationResult.get("invitationKey").getAsString()));
                return invitationResult;
            } else {
                JsonObject invitationResult = responseBody.getAsJsonObject();
                invitationResult.addProperty("invitationUrl",
                        String.format("https://%s.mypayquicker.com/Welcome?invitationId=%s",
                                paymentProvider.getSubdomain(),
                                invitationResult.get("invitationKey").getAsString()));
                return invitationResult;
            }
        } catch (UnirestException responseException) {
            logger.error("Failed to communicate with payquicker", responseException);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Failed to communicate with payquicker");
        } catch(ResponseException re) {
            throw re;
        } catch (Exception e) {
            logger.error(e.getMessage(), e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    private String formatUrl(String urlFormat, PaymentProvider paymentProvider) {
        return String.format(urlFormat, (BooleanUtils.isTrue(paymentProvider.getCredentials().isSandbox()) ? "xyz" : "com"));
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
                MDC.put("payquickerResponse", GsonUtil.getGson().toJson(response.getBody()));
                MDC.put("payquickerResponseStatus", String.valueOf(response.getStatus()));
                logger.error("Payquicker failed to get token");
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Failed to authenticate with payquicker");
            }

            return response.getBody().getObject().getString("access_token");
        } catch (UnirestException responseException) {
            logger.error("Failed to communicate with payquicker", responseException);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Failed to communicate with payquicker");
        } catch (Exception e) {
            if (e instanceof ResponseException) {
                throw e;
            }
            logger.error(e.getMessage(), e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    @Override
    public boolean validateCredentials(PaymentProvider paymentProvider) {
        getToken(paymentProvider); // throws an exception if not working
        return true;
    }
}
