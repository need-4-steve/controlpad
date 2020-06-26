package com.controlpad.pay_fac.credits;

import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.payman_common.credits.CompanyCredit;
import com.controlpad.payman_common.credits.CreditsMapper;
import com.controlpad.payman_common.credits.TeamCredit;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.RestController;

import javax.servlet.http.HttpServletRequest;
import java.util.List;

@RestController
@RequestMapping("")
public class CreditsController {

    @Authorization(clientSqlSession = true, readPrivilege = 8, clientSqlAutoCommit = true)
    @RequestMapping(value = "/team-credits/{userId}", method = RequestMethod.GET)
    public List<TeamCredit> getTeamCreditsForUser(HttpServletRequest request,
                                                  @PathVariable("userId") String userId) {

        RequestUtil.checkOwnerRead(request, userId);

        return RequestUtil.getClientSqlSession(request).getMapper(CreditsMapper.class).listTeamCreditForuserId(userId);
    }

    @Authorization(clientSqlSession = true, readPrivilege = 8, clientSqlAutoCommit = true)
    @RequestMapping(value = "/team-credits/{userId}/{teamId}", method = RequestMethod.GET)
    public TeamCredit getTeamCreditsForUserTeam(HttpServletRequest request,
                                          @PathVariable("userId") String userId,
                                          @PathVariable("teamId") String teamId) {

        RequestUtil.checkOwnerRead(request, userId);

        return RequestUtil.getClientSqlSession(request).getMapper(CreditsMapper.class).findTeamCreditForUserAndTeam(userId, teamId);
    }

    @Authorization(clientSqlSession = true, readPrivilege = 8, clientSqlAutoCommit = true)
    @RequestMapping(value = "/company-credits/{userId}", method = RequestMethod.GET)
    public CompanyCredit getCompanyCreditsForUser(HttpServletRequest request,
                                                  @PathVariable("userId") String userId) {

        RequestUtil.checkOwnerRead(request, userId);

        return RequestUtil.getClientSqlSession(request).getMapper(CreditsMapper.class).findCompanyCreditForUserId(userId);
    }


}
