/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.auth;

import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.pay_fac.util.ResponseUtil;
import com.controlpad.payman_common.client.ClientMapper;
import com.controlpad.payman_common.payman_user.PayManUser;
import com.controlpad.payman_common.validation.PostChecks;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import java.sql.SQLException;

@RestController
public class AuthController {

    private static final Logger logger = LoggerFactory.getLogger(AuthController.class);

    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    AuthUtil authUtil;

    @RequestMapping(value = "/login", method = RequestMethod.POST)
    public Session login(@RequestBody @Validated({PostChecks.class}) LoginObject userCredentials) {

        SqlSession sqlSession = sqlSessionUtil.openPaymanSession(true);
        AuthMapper authMapper = sqlSession.getMapper(AuthMapper.class);

        PayManUser authUser = authMapper.findUserForUsername(userCredentials.getUsername());

        if (authUser == null || !new BCryptPasswordEncoder(7).matches(userCredentials.getPassword(), authUser.getPassword())) {
            throw ResponseUtil.getUnauthorized("Wrong username or password");
        }

        Session session = authMapper.findOpenSessionForUserId(authUser.getId());
        if (session != null) {
            authUtil.refreshSession(session, authMapper);
        } else {
            do {
                //Loop allows for duplicate handling
                session = insertNewSessionKey(authUser.getId(), authUser.getClientId(), authMapper);
            } while (session == null);
        }

        return session;
    }

    //@Authorization(admin = true, apiSqlSession = true, allowAPIKey = false, userSession = true)
    @Authorization(readPrivilege = 2, userSession = true, apiSqlSession = true)
    @RequestMapping(value = "/admin/bind-client/{clientId}", method = RequestMethod.GET)
    public Session changeAdminSessionClient(HttpServletRequest request,
                                @PathVariable(value = "clientId") String clientId) {

        AuthMapper authMapper = RequestUtil.getApiSqlSession(request).getMapper(AuthMapper.class);

        if (!RequestUtil.getApiSqlSession(request).getMapper(ClientMapper.class).existsForClientId(clientId)) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "clientId does not exist");
        }

        Session session = RequestUtil.findCurrentUserSession(request);
        session.setClientId(clientId);

        authMapper.updateSessionClientId(session.getId(), clientId);

        authUtil.refreshSession(session, authMapper);

        RequestUtil.getApiSqlSession(request).commit();
        return session;
    }

    //@Authorization(admin = true, apiSqlSession = true, allowAPIKey = false, userSession = true)
    @Authorization(readPrivilege = 2, userSession = true, apiSqlSession = true)
    @RequestMapping(value = "/admin/unbind-client", method = RequestMethod.GET)
    public Session removeClientFromAdminSession(HttpServletRequest request) {
        AuthMapper authMapper = RequestUtil.getApiSqlSession(request).getMapper(AuthMapper.class);
        Session session = RequestUtil.findCurrentUserSession(request);
        session.setClientId(null);

        authMapper.updateSessionClientId(session.getId(), null);

        authUtil.refreshSession(session, authMapper);

        RequestUtil.getApiSqlSession(request).commit();
        return session;
    }

    /**
     * Generates a new {@link Session} and inserts it into the database.
     * @param userId The user generating the session
     * @param authMapper Mapper used to insert the record
     * @return Session or null if duplicate key in database
     */
    private Session insertNewSessionKey(String userId, String clientId, AuthMapper authMapper) {
        Session session;
        try {
                session = authUtil.generateNewSession(userId, clientId);
                if (authMapper.existsSessionKey(session.getId())) {
                    return null;
                }
                authMapper.insertSession(session);
        } catch (Exception e){
            logger.error(e.getMessage(), e);
            if (e.getCause() != null && e.getCause() instanceof SQLException) {
                SQLException sqlException = (SQLException)e.getCause();
                if (sqlException.getErrorCode() == 1062) {
                    //Duplicate detection for possible race condition (Unlikely but possible)
                    session = null;
                } else {
                    throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
                }
            } else {
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
            }
        }
        return session;
    }
}