package com.controlpad.pay_fac.fee;

import com.controlpad.pay_fac.account.AccountUtils;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.fee.FeeMapper;
import com.controlpad.payman_common.fee.TeamFeeSet;
import com.controlpad.payman_common.validation.PostChecks;
import org.apache.ibatis.session.SqlSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import java.util.List;

@RestController
public class FeeController {

    @Autowired
    AccountUtils accountUtils;

    @Authorization(clientSqlSession = true, readPrivilege = 2)
    @RequestMapping(value = "/fees", method = RequestMethod.GET)
    public List<Fee> listAllFees(HttpServletRequest request) {
        return RequestUtil.getClientSqlSession(request).getMapper(FeeMapper.class).listAllFees();
    }

    @Authorization(createPrivilege = 1, allowAPIKey = false, clientSqlSession = true, superuser = true)
    @RequestMapping(value = "/fees", method = RequestMethod.POST)
    public Fee postFee(HttpServletRequest request,
                        @RequestBody @Validated(PostChecks.class) Fee fee) {

        SqlSession clientSqlSession = RequestUtil.getClientSqlSession(request);

        if (fee.getAccount() != null) {
            accountUtils.addAccount(clientSqlSession, fee.getAccount());
        }

        clientSqlSession.getMapper(FeeMapper.class).insertFee(fee);
        clientSqlSession.commit();
        return fee;
    }

    @Authorization(writePrivilege = 1, allowAPIKey = false, clientSqlSession = true, superuser = true)
    @RequestMapping(value = "/fees/{feeId}/account", method = RequestMethod.PUT)
    public void updateFeeAccount(HttpServletRequest request,
                                 @PathVariable("feeId") Long feeId,
                                 @RequestBody @Validated(PostChecks.class) Account account) {

        SqlSession clientSqlSession = RequestUtil.getClientSqlSession(request);

        if(!clientSqlSession.getMapper(FeeMapper.class).existsFeeForId(feeId)) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Fee doesn't exist");
        }

        Long accountId = clientSqlSession.getMapper(FeeMapper.class).findAccountIdForFeeId(feeId);
        if (accountId == null) {
            accountUtils.addAccount(clientSqlSession, account);
            clientSqlSession.getMapper(FeeMapper.class).updateFeeAccountId(feeId, account.getId());
        } else {
            account.setId(accountId);
            accountUtils.updateAccount(clientSqlSession, account);
        }

        clientSqlSession.commit();
    }

    // Team fee sets

    @Authorization(clientSqlSession = true, readPrivilege = 8)
    @RequestMapping(value = "/team-feesets/{teamId}", method = RequestMethod.GET)
    public List<TeamFeeSet> listTeamFeeSets(HttpServletRequest request,
                                                   @PathVariable("teamId") String teamId) {

        return RequestUtil.getClientSqlSession(request).getMapper(FeeMapper.class).listTeamFeeSets(teamId);
    }

    @Authorization(clientSqlSession = true, readPrivilege = 8)
    @RequestMapping(value = "/team-feesets/{teamId}/{transactionType}", method = RequestMethod.GET)
    public TeamFeeSet getTeamFeeSetForType(HttpServletRequest request,
                                           @PathVariable("teamId") String teamId,
                                           @PathVariable("transactionType") String transactionType) {

        return RequestUtil.getClientSqlSession(request).getMapper(FeeMapper.class).findTeamFeeSetForType(teamId, transactionType);
    }

    @Authorization(writePrivilege = 1, allowAPIKey = false, clientSqlSession = true, clientSqlAutoCommit = true, superuser = true)
    @RequestMapping(value = "/team-feesets/{teamId}/{transactionType}", method = RequestMethod.PUT)
    public void putTeamFeeSet(HttpServletRequest request,
                              @PathVariable("teamId") String teamId,
                              @PathVariable("transactionType") String transactionType,
                              @RequestBody @Validated(PostChecks.class) TeamFeeSet teamFeeSet) {

        FeeMapper feeMapper = RequestUtil.getClientSqlSession(request).getMapper(FeeMapper.class);
        if (!feeMapper.isValidTeamAndType(teamId, transactionType)) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Must use a valid team and type");
        }

        teamFeeSet.setTeamId(teamId);
        teamFeeSet.setTransactionType(transactionType);

        if (feeMapper.existsTeamFeeSet(teamId, transactionType)) {
            feeMapper.updateTeamFeeSet(teamFeeSet);
        } else {
            feeMapper.insertTeamFeeSet(teamFeeSet);
        }
    }
}