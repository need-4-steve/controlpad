package com.controlpad.pay_fac.payment_provider;

import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.payman_common.merchant.Merchant;
import com.controlpad.payman_common.merchant.MerchantMapper;
import com.controlpad.payman_common.payment_provider.PaymentProvider;
import com.controlpad.payman_common.payment_provider.PaymentProviderMapper;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_common.util.GsonUtil;
import com.controlpad.payman_common.validation.PostChecks;
import com.google.gson.JsonObject;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;
import org.springframework.http.HttpStatus;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import java.util.List;

@RestController
@RequestMapping(value = "/payment-providers")
public class PaymentProviderController {

    private final Logger logger = LoggerFactory.getLogger(PaymentProviderController.class);

    PayQuicker payQuicker;
    MockProvider mockProvider;

    public PaymentProviderController() {
        payQuicker = new PayQuicker();
        mockProvider = new MockProvider();
    }

    @Authorization(readPrivilege = 2, clientSqlSession = true)
    @RequestMapping(value = "", method = RequestMethod.GET)
    public PaginatedResponse<PaymentProvider> listProviders(HttpServletRequest request,
                                                            @RequestParam(value = "page", defaultValue = "1") Long page,
                                                            @RequestParam(value = "count", defaultValue = "50") Integer count) {

        List<PaymentProvider> paymentProviderList = RequestUtil.getClientSqlSession(request).getMapper(PaymentProviderMapper.class).search(count, (page - 1) * count);
        return new PaginatedResponse<>((long) paymentProviderList.size(), paymentProviderList.size(), paymentProviderList);
    }

    @Authorization(createPrivilege = 1, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "", method = RequestMethod.POST)
    public PaymentProvider createProvider(HttpServletRequest request,
                                          @RequestBody @Validated({PostChecks.class}) PaymentProvider paymentProvider) {

        validateFieldsForCreate(paymentProvider);

        getProviderInterface(paymentProvider).validateCredentials(paymentProvider);

        RequestUtil.getClientSqlSession(request).getMapper(PaymentProviderMapper.class).insert(paymentProvider);

        return paymentProvider;
    }

    @Authorization(clientSqlSession = true, createPrivilege =  7, clientSqlAutoCommit = true)
    @RequestMapping(value = "/payquicker/invitation", method = RequestMethod.POST)
    public JsonObject createPayquickerInvitation(HttpServletRequest request,
                                                 @RequestBody @Validated({PostChecks.class}) PayquickerInvitationRequest invitationRequest) {

        SqlSession sqlSession = RequestUtil.getClientSqlSession(request);
        Team team = sqlSession.getMapper(TeamMapper.class).findById(invitationRequest.getTeamId());
        if (team.getPaymentProviderId() != null) {
            PaymentProvider paymentProvider = sqlSession.getMapper(PaymentProviderMapper.class).findById(team.getPaymentProviderId());
            if (paymentProvider.getType().equalsIgnoreCase("payquicker")) {
                JsonObject payquickerResponse = payQuicker.createInvitation(paymentProvider, invitationRequest);
                if (payquickerResponse.has("invitationUrl")) {
                    // If successful add the merchant email because it's needed for payouts
                    MerchantMapper merchantMapper = sqlSession.getMapper(MerchantMapper.class);
                    if (merchantMapper.existsForId(invitationRequest.getUserId())) {
                        merchantMapper.updateEmailIfNeeded(invitationRequest.getUserId(), invitationRequest.getEmail());
                    } else {
                        merchantMapper.insert(new Merchant(invitationRequest.getUserId(), invitationRequest.getEmail(), "rep"));
                    }
                }
                return payquickerResponse;
            } else {
                MDC.put("invitationRequest", GsonUtil.getGson().toJson(invitationRequest));
                logger.error("Call to payquicker while no provider credentials exist");
                throw new ResponseException(HttpStatus.BAD_REQUEST, "Not using payquicker for this account");
            }
        } else {
            MDC.put("invitationRequest", GsonUtil.getGson().toJson(invitationRequest));
            logger.error("Call to payquicker while not using ");
            throw new ResponseException(HttpStatus.BAD_REQUEST, "No payment provider account set up");
        }
    }

    private PaymentProviderInterface getProviderInterface(PaymentProvider paymentProvider) {
        switch (paymentProvider.getType()) {
            case "payquicker":
                return payQuicker;
            case "mock":
                return mockProvider;
            default:
                throw new ResponseException(HttpStatus.BAD_REQUEST, "No provider interface found for type: " + paymentProvider.getType());
        }
    }

    private void validateFieldsForCreate(PaymentProvider paymentProvider) {
        switch (paymentProvider.getType()) {
            case "payquicker":
                if (StringUtils.isBlank(paymentProvider.getCredentials().getFundingAccountPublicId())) {
                    throw new ResponseException(HttpStatus.BAD_REQUEST, "credentials.fundingAccountPublicId required for payquicker");
                }
                if (StringUtils.isBlank(paymentProvider.getCredentials().getId())) {
                    throw new ResponseException(HttpStatus.BAD_REQUEST, "credentials.id required for payquicker");
                }
                break;
            case "mock":
                // No extra requirements
                break;
            default:
                throw new ResponseException(HttpStatus.BAD_REQUEST, "No provider interface found for type: " + paymentProvider.getType());
        }
    }
}
