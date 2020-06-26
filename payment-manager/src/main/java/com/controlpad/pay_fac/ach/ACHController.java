/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.ach;

import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.payman_common.ach.ACH;
import com.controlpad.payman_common.ach.ACHMapper;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import java.util.List;


@RestController
@RequestMapping("/ach")
public class ACHController {

    //@Authorization(admin = true, clientSqlSession = true, clientSqlAutoCommit = true, allowAPIKey = false)
    @Authorization(readPrivilege = 2, clientSqlSession = true, clientSqlAutoCommit = true, allowAPIKey = false)
    @RequestMapping(value = "", method = RequestMethod.GET)
    public List<ACH> listACH(HttpServletRequest request) {

        return RequestUtil.getClientSqlSession(request).getMapper(ACHMapper.class).list();
    }

    //@Authorization(admin = true, clientSqlSession = true, clientSqlAutoCommit = true, allowAPIKey = false)
    @Authorization(readPrivilege = 2, clientSqlSession = true, clientSqlAutoCommit = true, allowAPIKey = false)
    @RequestMapping(value = "/{ach-id}", method = RequestMethod.GET)
    public ACH getACHById(HttpServletRequest request,
                            @PathVariable("ach-id") Long achId) {

        return RequestUtil.getClientSqlSession(request).getMapper(ACHMapper.class).findForId(achId);
    }

    @Authorization(writePrivilege = 1, clientSqlSession = true, clientSqlAutoCommit = true, allowAPIKey = false)
    @RequestMapping(value = "/{ach-id}", method = RequestMethod.PUT)
    public void putACHById(HttpServletRequest request,
                            @PathVariable("ach-id") Long achId,
                            @RequestBody @Validated(AlwaysCheck.class) ACH ach) {

        ach.setId(achId);

        ACHMapper achMapper = RequestUtil.getClientSqlSession(request).getMapper(ACHMapper.class);
        if (achMapper.existsForId(achId)) {
            achMapper.updateById(ach);
        } else {
            achMapper.insert(ach);
        }
    }
}