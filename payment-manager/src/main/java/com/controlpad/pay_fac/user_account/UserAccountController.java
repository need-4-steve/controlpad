package com.controlpad.pay_fac.user_account;

import com.controlpad.pay_fac.client.ClientConfigUtil;
import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.gateway.GatewayUtil;
import com.controlpad.pay_fac.gatewayconnection.SubAccountUser;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.ParamValidations;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.pay_fac.util.ResponseUtil;
import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.user_account.UserAccount;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.controlpad.payman_common.user_account.UserAccountValidation;
import com.controlpad.payman_common.validation.PostChecks;
import org.apache.ibatis.session.SqlSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import java.util.List;

@RestController
@CrossOrigin(
        methods = {RequestMethod.GET, RequestMethod.PUT, RequestMethod.OPTIONS},
        maxAge = 86400,
        origins = "*",
        allowedHeaders = "*"
)
@RequestMapping("/user-accounts")
public class UserAccountController {

    @Autowired
    UserAccountUtil userUtil;
    @Autowired
    ClientConfigUtil clientConfigUtil;
    @Autowired
    GatewayUtil gatewayUtil;

    @Authorization(readPrivilege = 8, clientSqlSession = true)
    @RequestMapping(value = "/{userId}", method = RequestMethod.GET)
    public UserAccount getUserAccount(HttpServletRequest request,
                                      @PathVariable("userId") String userId) {

        RequestUtil.checkOwnerRead(request, userId);
        UserAccount account = RequestUtil.getClientSqlSession(request).getMapper(UserAccountMapper.class).findAccountForUserId(userId);
        if (account != null && RequestUtil.getAuthUser(request).getPrivilege().getReadPrivilege() > 7) {
            account.obscure();
        }
        return account;
    }

    @Authorization(writePrivilege = 8, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/{userId}", method = RequestMethod.PUT)
    public UserAccount putUserAccount(HttpServletRequest request,
                               @PathVariable("userId") String userId,
                               @RequestParam(value = "teamId", defaultValue = "rep") String teamId,
                               @RequestBody @Validated({PostChecks.class}) UserAccount account) {

        RequestUtil.checkOwnerWrite(request, userId);

        account.setUserId(userId);

        // Update merchant accounts that have a master account

        SqlSession clientSession = RequestUtil.getClientSqlSession(request);
        List<GatewayConnection> connections = clientSession.getMapper(GatewayConnectionMapper.class)
                .search(null, userId, null, null, null, null, true, 10, 0);

        if (connections.isEmpty()) {
            // check for an emvio connection on rep team and create a rep
            GatewayConnection masterEmvioConnection = gatewayUtil.selectGatewayConnection(clientSession, null,
                    teamId, null, null, null, true, "emvio");

            if (masterEmvioConnection != null) {
                String clientName = clientConfigUtil.getClientName(RequestUtil.getClientId(request));

                SubAccountUser subAccountUser = new SubAccountUser(account);
                subAccountUser.setTeamId(teamId);
                subAccountUser.setUserId(userId);
                subAccountUser.getBusiness().setName(account.getName());

                gatewayUtil.getGatewayApi(masterEmvioConnection).createSubAccount(clientName, clientSession,
                        masterEmvioConnection, subAccountUser);
            }

        } else {
            for (GatewayConnection gatewayConnection : connections) {
                if (gatewayConnection.getMasterConnectionId() != null && !gatewayConnection.fundsMaster() && !gatewayConnection.fundsCompany()) // update sub-accounts
                    gatewayUtil.getGatewayApi(gatewayConnection).updateSubAccount(gatewayConnection, new SubAccountUser(account));
            }
        }
        // TODO --

        userUtil.putUserAccount(clientSession, account, RequestUtil.getClientId(request));
        if (RequestUtil.getAuthUser(request).getPrivilege().getReadPrivilege() > 7) {
            account.obscure();
        }
        return account;
    }

    @Authorization(readPrivilege = 7, clientSqlSession = true)
    @RequestMapping(value = "", method = RequestMethod.GET)
    public PaginatedResponse<UserAccount> getAllUserAccountPaginate(HttpServletRequest request,
                                                                    @RequestParam(value = "page") Long page,
                                                                    @RequestParam(value = "count") Integer count){

        ParamValidations.checkPageCount(count, page);
        UserAccountMapper userAccountMapper = RequestUtil.getClientSqlSession(request).getMapper(UserAccountMapper.class);
        List<UserAccount> data = userAccountMapper.getAllUserAccountPaginate(count, (page-1) * count);
        Long totalRecords = userAccountMapper.getUserAccountCount();
        return new PaginatedResponse<>(totalRecords, count, data);
    }

    @Authorization(readPrivilege = 8, clientSqlSession = true)
    @RequestMapping(value = "/validate", method = RequestMethod.GET)
    public CommonResponse validateAccount(HttpServletRequest request,
                                          @RequestParam("userId") String userId,
                                          @RequestParam(value = "amount1") String amount1,
                                          @RequestParam(value = "amount2") String amount2) {

        RequestUtil.checkOwnerRead(request, userId);

        SqlSession session = RequestUtil.getClientSqlSession(request);

        CommonResponse response = userUtil.validateUserAccount(session, userId, amount1, amount2);

        session.commit();
        return response;
    }

    @Authorization(readPrivilege = 5, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/{user-id}/invalidate", method = RequestMethod.GET)
    public void invalidateAccount(HttpServletRequest request,
                                  @PathVariable("user-id") String userId) {

        RequestUtil.getClientSqlSession(request).getMapper(UserAccountMapper.class).markAccountInvalid(userId);
    }


    // Payout related

    @Authorization(readPrivilege = 7, clientSqlSession = true)
    @RequestMapping(value = "/list-validated-users", method = RequestMethod.GET)
    public List<Long> listUserAccountValidation(HttpServletRequest request) {

        return RequestUtil.getClientSqlSession(request).getMapper(UserAccountMapper.class).listUserAccountsValidated();
    }




    // Dev controls

    @Authorization(readPrivilege = 7, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/{user-id}/fake-validation-submit", method = RequestMethod.GET)
    public UserAccountValidation fakeValidationSubmit(HttpServletRequest request,
                                                      @PathVariable("user-id") String userId) {

        ControlPadClient client = clientConfigUtil.getClientMap().get(RequestUtil.getClientId(request));
        if (client == null) {
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Client info not found");
        }
        if (!client.getSandbox()) {
            throw ResponseUtil.getInsufficientPrivileges("Only for sandbox accounts");
        }

        UserAccountMapper userAccountMapper = RequestUtil.getClientSqlSession(request).getMapper(UserAccountMapper.class);
        UserAccount userAccount = userAccountMapper.findAccountForUserId(userId);
        if (userAccount == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "No account for user id");
        }

        UserAccountValidation userAccountValidation = userAccountMapper.findCurrentUserAccountValidation(userId);
        if (userAccountValidation == null) {
            userAccountValidation = UserAccountValidation.generateNew(userAccount);
            userAccountMapper.insertDevAccountValidation(userAccountValidation);
        } else {
            userAccountValidation.setAccountHash(userAccount.getHash());
            userAccountMapper.updateDevAccountValidationHash(userAccountValidation);
        }
        userAccountMapper.markValidationSubmittedForId(userAccountValidation.getId());

        return userAccountValidation;
    }

    @Authorization(readPrivilege = 7, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/{user-id}/current-validation", method = RequestMethod.GET)
    public UserAccountValidation getCurrentValidation(HttpServletRequest request,
                                     @PathVariable("user-id") String userId) {

        ControlPadClient client = clientConfigUtil.getClientMap().get(RequestUtil.getClientId(request));
        if (client == null) {
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Client info not found");
        }
        if (!client.getSandbox()) {
            throw ResponseUtil.getInsufficientPrivileges("Only for sandbox accounts");
        }

        return RequestUtil.getClientSqlSession(request).getMapper(UserAccountMapper.class).findCurrentUserAccountValidation(userId);
    }
}