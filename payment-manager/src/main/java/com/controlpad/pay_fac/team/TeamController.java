/*
* ===============================================================================
* Copyright 2015(c) ControlPad
* ===============================================================================
*/
package com.controlpad.pay_fac.team;

import com.controlpad.pay_fac.account.AccountUtils;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.pay_fac.util.TeamConverterUtil;
import com.controlpad.payman_common.account.AccountMapper;
import com.controlpad.payman_common.team.PayoutSchedule;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_common.validation.AlwaysCheck;
import com.controlpad.payman_common.validation.PostChecks;
import org.apache.ibatis.session.SqlSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import java.util.List;

@RestController
@RequestMapping(value = "/teams")
public class TeamController {

    @Autowired
    AccountUtils accountUtils;

    @Authorization(readPrivilege = 6, clientSqlSession = true)
    @RequestMapping(value = "", method = RequestMethod.GET)
    public List<Team> getTeams(HttpServletRequest request) {

        return RequestUtil.getClientSqlSession(request).getMapper(TeamMapper.class).list();
    }

    @Authorization(readPrivilege = 6, clientSqlSession = true)
    @RequestMapping(value = "/{team_id}", method = RequestMethod.GET)
    public Team getTeam(HttpServletRequest request,
                                      @PathVariable("team_id") String teamId,
                                      @RequestParam(value = "accounts", defaultValue = "false") Boolean showAccounts) {
        teamId = TeamConverterUtil.convert(teamId);

        SqlSession session = RequestUtil.getClientSqlSession(request);

        Team team = session.getMapper(TeamMapper.class).findById(teamId);

        if (team != null && showAccounts) {
            AccountMapper accountMapper = session.getMapper(AccountMapper.class);
            team.setAccount(accountMapper.findForId(team.getAccountId()));
            team.setConsignmentAccount(accountMapper.findForId(team.getConsignmentAccountId()));
            team.setTaxAccount(accountMapper.findForId(team.getTaxAccountId()));
        }

        return team;
    }

    @Authorization(createPrivilege = 1, clientSqlSession = true)
    @RequestMapping(value = "", method = RequestMethod.POST)
    public Team addTeam(HttpServletRequest request,
                        @RequestBody @Validated({PostChecks.class}) Team team) {

        SqlSession sqlSession = RequestUtil.getClientSqlSession(request);

        TeamMapper teamMapper = sqlSession.getMapper(TeamMapper.class);
        if (teamMapper.existsById(team.getId())) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Team id already exists");
        }

        if (team.getAccount() != null) {
            accountUtils.addAccount(sqlSession, team.getAccount());
            team.setAccountId(team.getAccount().getId());
        }
        if (team.getTaxAccount() != null) {
            accountUtils.addAccount(sqlSession, team.getTaxAccount());
            team.setTaxAccountId(team.getTaxAccount().getId());
        }
        if (team.getConsignmentAccount() != null) {
            accountUtils.addAccount(sqlSession, team.getConsignmentAccount());
            team.setConsignmentAccountId(team.getConsignmentAccount().getId());
        }

        teamMapper.insert(team);

        sqlSession.commit();

        return team;
    }

    @Authorization(writePrivilege = 1, clientSqlSession = true)
    @RequestMapping(value = "/{team_id}", method = RequestMethod.PATCH)
    public void patchTeam(HttpServletRequest request,
                          @PathVariable("team_id") String teamId,
                          @RequestBody @Validated({AlwaysCheck.class}) Team team) {
        teamId = TeamConverterUtil.convert(teamId);

        SqlSession session = RequestUtil.getClientSqlSession(request);

        TeamMapper teamMapper = session.getMapper(TeamMapper.class);
        AccountMapper accountMapper = session.getMapper(AccountMapper.class);

        Team currentTeam = teamMapper.findById(teamId);

        if (currentTeam == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Team doesn't exist.");
        }

        team.setId(teamId);

        if (team.getName() != null) {
            teamMapper.updateName(currentTeam);
        }

        if (team.getAccount() != null) {
            if (currentTeam.getAccountId() == null) {
                accountMapper.insert(team.getAccount());
                team.setAccountId(team.getAccount().getId());
                teamMapper.updateAccountId(team);
            } else {
                team.getAccount().setId(currentTeam.getAccountId());
                accountMapper.update(team.getAccount());
            }
        }
        if (team.getTaxAccount() != null) {
            if (currentTeam.getTaxAccountId() == null) {
                accountMapper.insert(team.getTaxAccount());
                team.setTaxAccountId(team.getTaxAccount().getId());
                teamMapper.updateTaxAccountId(team);
            } else {
                team.getTaxAccount().setId(currentTeam.getTaxAccountId());
                accountMapper.update(team.getTaxAccount());
            }
        }
        if (team.getConsignmentAccount() != null) {

            if (currentTeam.getConsignmentAccountId() == null) {
                accountMapper.insert(team.getConsignmentAccount());
                team.setConsignmentAccountId(team.getConsignmentAccount().getId());
                teamMapper.updateConsignmentAccountId(team);
            } else {
                team.getConsignmentAccount().setId(currentTeam.getConsignmentAccountId());
                accountMapper.update(team.getConsignmentAccount());
            }
        }
        if (team.getPaymentProviderId() != null) {
            teamMapper.updatePaymentProviderId(team);
        }

        if (team.getConfig() != null) {
            teamMapper.updateTeamConfig(team);
        }

        session.commit();
    }

    @Deprecated
    @Authorization(writePrivilege = 1, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/{team_id}/payout-schedule", method = RequestMethod.PUT)
    public void putPayoutSchedule(HttpServletRequest request,
                          @PathVariable("team_id") String teamId,
                          @RequestBody @Validated(AlwaysCheck.class) PayoutSchedule payoutSchedule) {
        teamId = TeamConverterUtil.convert(teamId);

        SqlSession session = RequestUtil.getClientSqlSession(request);

        TeamMapper teamMapper = session.getMapper(TeamMapper.class);
        if (!teamMapper.existsById(teamId)) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Team doesn't exist");
        }

        teamMapper.updatePayoutSchedule(payoutSchedule, teamId);
    }

    @Deprecated
    @Authorization(createPrivilege = 1, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/{team_id}/payout-schedule", method = RequestMethod.DELETE)
    public void removePayoutSchedule(HttpServletRequest request,
                                  @PathVariable("team_id") String teamId) {
        teamId = TeamConverterUtil.convert(teamId);

        SqlSession session = RequestUtil.getClientSqlSession(request);

        TeamMapper teamMapper = session.getMapper(TeamMapper.class);
        if (!teamMapper.existsById(teamId)) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Team doesn't exist");
        }

        teamMapper.updatePayoutSchedule(null, teamId);
    }

}