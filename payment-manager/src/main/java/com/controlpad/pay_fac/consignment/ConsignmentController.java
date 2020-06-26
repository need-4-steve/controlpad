/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.consignment;

import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.payman_common.consignment.Consignment;
import com.controlpad.payman_common.consignment.ConsignmentMapper;
import com.controlpad.payman_common.validation.PostChecks;
import org.apache.ibatis.session.SqlSession;
import org.springframework.http.HttpStatus;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;

@RestController
@RequestMapping("/consignments")
public class ConsignmentController {

    @Authorization(readPrivilege = 8, clientSqlSession = true)
    @RequestMapping(value = "/{userId}", method = RequestMethod.GET)
    public Consignment getUserConsignment(HttpServletRequest request,
                                          @PathVariable("userId") String userId) {

        if (!RequestUtil.getAuthUser(request).canReadOwner(userId)) {
            throw new ResponseException(HttpStatus.FORBIDDEN, "Admin or owner");
        }

        SqlSession session = RequestUtil.getClientSqlSession(request);

        return session.getMapper(ConsignmentMapper.class).findForUserId(userId);
    }

    @Authorization(writePrivilege = 5, clientSqlSession = true)
    @RequestMapping(value = "/{userId}", method = RequestMethod.PUT)
    public void putUserConsignment(HttpServletRequest request,
                                   @PathVariable("userId") String userId,
                                   @RequestBody @Validated({PostChecks.class}) Consignment consignment) {

        SqlSession session = RequestUtil.getClientSqlSession(request);
        ConsignmentMapper consignmentMapper = session.getMapper(ConsignmentMapper.class);

        consignment.setUserId(userId);
        if (consignmentMapper.exists(consignment.getUserId())) {
            consignmentMapper.updateAmount(consignment);
        } else {
            consignmentMapper.insert(consignment);
        }

        session.commit();
    }
}