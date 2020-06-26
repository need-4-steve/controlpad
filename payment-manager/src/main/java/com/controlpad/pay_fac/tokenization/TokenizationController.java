package com.controlpad.pay_fac.tokenization;

import com.controlpad.pay_fac.gateway.GatewayUtil;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.payment_info.TokenRequest;
import com.controlpad.pay_fac.util.CardValidationUtil;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.pay_fac.util.TeamConverterUtil;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.validation.PostChecks;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;

@RestController
@RequestMapping("/tokenization")
public class TokenizationController {

    @Autowired
    GatewayUtil gatewayUtil;

    @Authorization(createPrivilege = 7, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/card", method = RequestMethod.POST)
    public TokenizeCardResponse saveCard(HttpServletRequest request,
                                         //@RequestParam(value = "gateway", defaultValue = "usaepay") String gateway,
                                         @RequestParam(value = "teamId", defaultValue = "company") String teamId,
                                         @RequestBody @Validated({PostChecks.class}) TokenRequest tokenRequest){

        if (tokenRequest.getCard() != null)
            CardValidationUtil.validateCard(tokenRequest.getCard());

        if (tokenRequest.getTeamId() != null) {
            teamId = tokenRequest.getTeamId();
        }
        teamId = TeamConverterUtil.convert(teamId);


        GatewayConnection gatewayConnection = gatewayUtil.getGatewayConnection(RequestUtil.getClientSqlSession(request), tokenRequest, teamId);
        if(gatewayConnection == null){
            return new TokenizeCardResponse(1, "Gateway connection not found");
        }

        return gatewayUtil.getGatewayApi(gatewayConnection).tokenizeCard(tokenRequest, gatewayConnection, request.getRemoteAddr());
    }

}