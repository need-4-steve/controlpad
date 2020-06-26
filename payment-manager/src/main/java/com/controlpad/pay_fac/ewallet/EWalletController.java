package com.controlpad.pay_fac.ewallet;

import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.payman_common.ewallet.EWallet;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.springframework.http.HttpStatus;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import java.math.BigDecimal;

@RestController
@CrossOrigin(
        methods = {RequestMethod.GET, RequestMethod.OPTIONS},
        maxAge = 86400,
        origins = "*",
        allowedHeaders = "*"
)
@RequestMapping(value = "/e-wallets")
public class EWalletController {

    @Authorization(readPrivilege = 8, clientSqlSession = true)
    @RequestMapping(value = "/{userId}", method = RequestMethod.GET)
    public EWallet getEWallet(HttpServletRequest request,
                              @PathVariable("userId") String userId) {

        RequestUtil.checkOwnerRead(request, userId);

        UserBalances userBalances = RequestUtil.getClientSqlSession(request).getMapper(UserBalancesMapper.class).find(userId, "rep");

        return new EWallet(userId,
                "rep",
                (userBalances != null ? userBalances.getEWallet() : BigDecimal.ZERO),
                BigDecimal.ZERO,
                true);
    }

    @Authorization(writePrivilege = 0, allowSessionKey = false, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/{userId}", method = RequestMethod.PUT)
    public EWallet updateEWallet(HttpServletRequest request,
                                 @PathVariable("userId") String userId,
                                 @RequestBody @Validated({AlwaysCheck.class}) EWallet eWallet) {

        throw new ResponseException(HttpStatus.METHOD_NOT_ALLOWED);
    }

}