package com.controlpad.pay_fac.gatewayconnection;

import com.controlpad.pay_fac.client.ClientConfigUtil;
import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.gateway.GatewayUtil;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.user_account.UserAccountUtil;
import com.controlpad.pay_fac.util.ParamValidations;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.user_account.UserAccount;
import com.controlpad.payman_common.validation.FullCheck;
import org.apache.commons.validator.routines.RegexValidator;
import org.apache.commons.validator.routines.UrlValidator;
import org.apache.ibatis.session.SqlSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.RestController;

import javax.servlet.http.HttpServletRequest;

@RestController
@RequestMapping("/sub-accounts")
public class SubAccountController {

    @Autowired
    GatewayUtil gatewayUtil;
    @Autowired
    UserAccountUtil userAccountUtil;
    @Autowired
    ClientConfigUtil clientConfigUtil;

    private UrlValidator urlValidator;

    public SubAccountController() {
        urlValidator = new UrlValidator(new String[]{"http", "https", "ftp"}, new RegexValidator(".*"), 0L);
    }

    @Authorization(createPrivilege = 7, clientSqlSession = true)
    @RequestMapping(value = "", method = RequestMethod.POST)
    public CommonResponse createConnection(HttpServletRequest request,
                                           @RequestBody @Validated({FullCheck.class}) SubAccountUser subAccountUser) {

        String website = subAccountUser.getBusiness().getWebsite();
        String dob = subAccountUser.getBusiness().getOwner().getDob();
        String businessName = subAccountUser.getBusiness().getName();
        Account businessAccount = subAccountUser.getBusiness().getAccount();

        if (!urlValidator.isValid(website)) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "website url not valid format");
        }

        ParamValidations.validatePastBirthDate(dob, "dob");

        if (businessAccount.getNumber().length() < 5 || businessAccount.getNumber().length() > 20) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "account number must be between 5 and 20 characters");
        }

        SqlSession clientSession = RequestUtil.getClientSqlSession(request);
        GatewayConnection masterConnection = gatewayUtil.selectGatewayConnection(clientSession,
                subAccountUser.getMasterGatewayConnectionId(), subAccountUser.getTeamId(),
                null, null, null, null, null);

        if (masterConnection == null) {
            return new CommonResponse(false, 40, "No master gateway connection to create accounts under.");
        }
        // TODO maybe validate based on gateway type?
        if (!clientSession.getMapper(GatewayConnectionMapper.class).search(subAccountUser.getTeamId(), subAccountUser.getUserId(),
                null, null, null, null, null, 1, 0).isEmpty()) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Users can only have one merchant account");
        }
        String clientName = clientConfigUtil.getClientName(RequestUtil.getClientId(request));

        CommonResponse response = gatewayUtil.getGatewayApi(masterConnection).createSubAccount(clientName, clientSession, masterConnection, subAccountUser);

        // TODO make this configurable, this can't support multiple gateway connections
        // Set user account to match sub account

        userAccountUtil.putUserAccount(clientSession, new UserAccount(subAccountUser.getUserId(), businessName,
                businessAccount.getRouting(),
                businessAccount.getNumber(),
                businessAccount.getType(),
                businessAccount.getBankName(),
                null), RequestUtil.getClientId(request));

        clientSession.commit();
        return response;
    }

}