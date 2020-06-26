/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.client;

import com.controlpad.pay_fac.auth.AuthUtil;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.pay_fac.util.ParamValidations;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.payman_common.client.ClientConfig;
import com.controlpad.payman_common.client.ClientMapper;
import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.datasource.SqlConfig;
import com.controlpad.payman_common.payman_user.PayManUser;
import com.controlpad.payman_common.validation.PostChecks;
import org.apache.ibatis.session.SqlSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import java.util.List;

@RestController
@RequestMapping("/clients")
public class ClientController {

    @Autowired
    AuthUtil authUtil;
    @Autowired
    private IDUtil idUtil;

    @Authorization(readPrivilege = 2, apiSqlSession = true, allowAPIKey = false)
    @RequestMapping(value = "", method = RequestMethod.GET)
    public List<ControlPadClient> getAllClients(HttpServletRequest request,
                                                @RequestParam(value = "page", defaultValue = "1") Long page,
                                                @RequestParam(value = "count", defaultValue = "25") Integer count) {
        ParamValidations.checkPageCount(count, page);

        if (RequestUtil.getAuthUser(request).getPrivilege().getReadPrivilege() < 2) {
            return RequestUtil.getApiSqlSession(request).getMapper(ClientMapper.class).listClients((page - 1) * count, count);
        } else {
            return RequestUtil.getApiSqlSession(request).getMapper(ClientMapper.class).listClientsSecure((page - 1) * count, count);
        }
    }

    @Authorization(createPrivilege = 1, apiSqlSession = true, allowAPIKey = false)
    @RequestMapping(value = "", method = RequestMethod.POST)
    public ControlPadClient addNewClient(HttpServletRequest request,
                                         @Validated({PostChecks.class}) @RequestBody ControlPadClient controlPadClient) {

        // Generate an id for the client
        controlPadClient.setId(idUtil.generateId());

        SqlSession sqlSession = RequestUtil.getApiSqlSession(request);
        ClientMapper clientMapper = sqlSession.getMapper(ClientMapper.class);
        clientMapper.insertClient(controlPadClient);

        sqlSession.commit();
        return controlPadClient;
    }

    @Authorization(readPrivilege = 2, apiSqlSession = true, allowAPIKey = false)
    @RequestMapping(value = "/{clientId}", method = RequestMethod.GET)
    public ControlPadClient getClient(HttpServletRequest request,
                          @PathVariable(value = "clientId") String clientId) {

        PayManUser authUser = RequestUtil.getAuthUser(request);

        ControlPadClient controlPadClient;
        if (authUser.getPrivilege().getReadPrivilege() < 2) {
            // Only admins should be allowed to see sql config
            controlPadClient = RequestUtil.getApiSqlSession(request).getMapper(ClientMapper.class).findClientForId(clientId);
        } else {
            controlPadClient = RequestUtil.getApiSqlSession(request).getMapper(ClientMapper.class).findClientNoSqlForId(clientId);
        }

        return controlPadClient;
    }

    @Authorization(writePrivilege = 1, apiSqlSession = true, allowAPIKey = false, apiSqlAutoCommit = true)
    @RequestMapping(value = "/{clientId}/config", method = RequestMethod.PUT)
    public ControlPadClient updateClientConfig(HttpServletRequest request,
                                               @PathVariable(value = "clientId") String clientId,
                                               @RequestBody @Validated({PostChecks.class}) ClientConfig clientConfig) {

        ClientMapper clientMapper = RequestUtil.getApiSqlSession(request).getMapper(ClientMapper.class);
        clientMapper.updateClientConfig(clientId, clientConfig);

        return clientMapper.findClientNoSqlForId(clientId);
    }

    @Authorization(writePrivilege = 1, apiSqlSession = true, allowAPIKey = false, apiSqlAutoCommit = true)
    @RequestMapping(value = "/{clientId}/sql-config-write", method = RequestMethod.PUT)
    public ControlPadClient updateClientSqlConfigWrite(HttpServletRequest request,
                                               @PathVariable(value = "clientId") String clientId,
                                                  @RequestBody @Validated({PostChecks.class}) SqlConfig sqlConfig) {

        ClientMapper clientMapper = RequestUtil.getApiSqlSession(request).getMapper(ClientMapper.class);
        clientMapper.updateClientSqlConfigWrite(clientId, sqlConfig);

        return clientMapper.findClientForId(clientId);
    }

    @Authorization(writePrivilege = 1, apiSqlSession = true, allowAPIKey = false, apiSqlAutoCommit = true)
    @RequestMapping(value = "/{clientId}/sql-config-read", method = RequestMethod.PUT)
    public ControlPadClient updateClientSqlConfigRead(HttpServletRequest request,
                                                  @PathVariable(value = "clientId") String clientId,
                                                  @RequestBody @Validated({PostChecks.class}) SqlConfig sqlConfig) {

        ClientMapper clientMapper = RequestUtil.getApiSqlSession(request).getMapper(ClientMapper.class);
        clientMapper.updateClientSqlConfigRead(clientId, sqlConfig);

        return clientMapper.findClientForId(clientId);
    }

}