package com.controlpad.pay_fac.gatewayconnection;

import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.gateway.Gateway;
import com.controlpad.pay_fac.gateway.GatewayUtil;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.ParamValidations;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_common.validation.AlwaysCheck;
import com.controlpad.payman_common.validation.PostChecks;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import java.util.Locale;

@RestController
@RequestMapping(value = "/gateway-connections")
public class GatewayConnectionController {

    @Autowired
    GatewayUtil gatewayUtil;

    @Authorization(readPrivilege = 2, clientSqlSession = true, allowAPIKey = false)
    @RequestMapping(value = "", method = RequestMethod.GET)
    public PaginatedResponse<GatewayConnection> getGatewayConnectionList(HttpServletRequest request,
                                                                              @RequestParam(value = "teamId", required = false) String teamId,
                                                                              @RequestParam(value = "userId", required = false) String userId,
                                                                              @RequestParam(value = "processCards", required = false) Boolean processCards,
                                                                              @RequestParam(value = "processChecks", required = false) Boolean processChecks,
                                                                              @RequestParam(value = "processInternal", required = false) Boolean processInternal,
                                                                              @RequestParam(value = "type", required = false) String type,
                                                                              @RequestParam(value = "active", required = false) Boolean active,
                                                                              @RequestParam(value = "page") Long page,
                                                                              @RequestParam(value = "count") Integer count) {

        ParamValidations.checkPageCount(count, page);

        GatewayConnectionMapper gatewayConnectionMapper = RequestUtil.getClientSqlSession(request).getMapper(GatewayConnectionMapper.class);
        Long totalRecords = gatewayConnectionMapper.searchCount(teamId, userId, processCards, processChecks, processInternal, type, active);
        return new PaginatedResponse<>(totalRecords, count,
                gatewayConnectionMapper.searchSecure(teamId, userId, processCards, processChecks, processInternal, type, active, count, (page-1) * count));
    }

    @Authorization(createPrivilege = 1, clientSqlSession = true, allowAPIKey = false)
    @RequestMapping(value = "", method = RequestMethod.POST)
    public CommonResponse<GatewayConnection> addGatewayConnection(HttpServletRequest request,
                                                  @RequestBody @Validated({PostChecks.class}) GatewayConnection gatewayConnection) {

        if (!gatewayUtil.getGatewayApi(gatewayConnection).checkCredentials(gatewayConnection)) {
            return new CommonResponse<>(false, 21, "Payment provider credentials invalid");
        }

        SqlSession session = RequestUtil.getClientSqlSession(request);

        if (!session.getMapper(TeamMapper.class).existsById(gatewayConnection.getTeamId())) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, String.format(Locale.US, "Team %s doesn't exist", gatewayConnection.getTeamId()));
        }

        GatewayConnectionMapper gatewayConnectionMapper = session.getMapper(GatewayConnectionMapper.class);

        gatewayConnectionMapper.insert(gatewayConnection);

        session.commit();
        return new CommonResponse<GatewayConnection>(true, 1, "Created").setData(gatewayConnection);
    }

    @Authorization(readPrivilege = 2, clientSqlSession = true, allowAPIKey = false)
    @RequestMapping(value = "/{id}", method = RequestMethod.GET)
    public GatewayConnection getConnection(HttpServletRequest request,
                                       @PathVariable(value = "id") Long id) {

        SqlSession session = RequestUtil.getClientSqlSession(request);

        if (RequestUtil.getAuthUser(request).getPrivilege().getReadPrivilege() < 2) {
            return session.getMapper(GatewayConnectionMapper.class).findById(id);
        } else {
            return session.getMapper(GatewayConnectionMapper.class).findByIdSecure(id);
        }
    }

    /**
     * Only for use to fix the connection information. Do not insert a new connection into the same id.
     * It is important to keep the connection if existing transactions are attached to it.
     */
    @Authorization(writePrivilege = 1, clientSqlSession = true, allowAPIKey = false, clientSqlAutoCommit = true)
    @ResponseStatus(HttpStatus.OK)
    @RequestMapping(value = "/{id}", method = RequestMethod.PUT)
    public CommonResponse<GatewayConnection> putConnection(HttpServletRequest request,
                              @PathVariable(value = "id") Long id,
                              @RequestBody @Validated({AlwaysCheck.class}) GatewayConnection gatewayConnection) {

        gatewayConnection.setId(id);

        SqlSession session = RequestUtil.getClientSqlSession(request);

        GatewayConnectionMapper gatewayConnectionMapper = session.getMapper(GatewayConnectionMapper.class);

        GatewayConnection currentConnection = gatewayConnectionMapper.findById(id);
        Gateway gateway = gatewayUtil.getGatewayApi(currentConnection);
        // Check that gateway types are the same, gateway accounts are the same, and new credentials are valid
        if (currentConnection != null) {
            if (!StringUtils.equalsIgnoreCase(gatewayConnection.getType(), currentConnection.getType()) ||
                !gateway.isAccountSame(currentConnection, gatewayConnection, session)) {
                return new CommonResponse<>(false, 20, "Account isn't the same");
            }
            if (!gateway.checkCredentials(gatewayConnection)) {
                return new CommonResponse<>(false, 21, "Payment provider credentials invalid");
            }
            gatewayConnectionMapper.update(gatewayConnection);
            return new CommonResponse<GatewayConnection>(true, 2, "Updated").setData(gatewayConnectionMapper.findById(id));
        } else {
            throw new ResponseException(HttpStatus.CONFLICT, "Connection doesn't exist");
        }
    }

    @Authorization(createPrivilege = 1)
    @RequestMapping(value = "/verify", method = RequestMethod.POST)
    public CommonResponse<String> verifyConnection(@RequestBody @Validated({AlwaysCheck.class}) GatewayConnection gatewayConnection) {
        if (gatewayUtil.getGatewayApi(gatewayConnection).checkCredentials(gatewayConnection)) {
            return new CommonResponse<>(true, 1, "Payment provider credentials are good");
        } else {
            return new CommonResponse<>(false, 21, "Payment provider credentials invalid");
        }
    }
}