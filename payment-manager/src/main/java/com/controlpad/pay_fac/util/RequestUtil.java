package com.controlpad.pay_fac.util;

import com.controlpad.pay_fac.auth.Session;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.interceptor.RequestAttributeKeys;
import com.controlpad.payman_common.payman_user.PayManUser;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.HttpStatus;

import javax.servlet.http.HttpServletRequest;

public class RequestUtil {

    private static final Logger logger = LoggerFactory.getLogger(RequestUtil.class);

    public static PayManUser getAuthUser(HttpServletRequest request) {
        return (PayManUser) request.getAttribute(RequestAttributeKeys.USER_KEY);
    }

    public static SqlSession getClientSqlSession(HttpServletRequest request) {
        return (SqlSession) request.getAttribute(RequestAttributeKeys.CLIENT_SQL_SESSION);
    }

    public static String getClientId(HttpServletRequest request) {
        return (String) request.getAttribute(RequestAttributeKeys.CLIENT_ID);
    }

    public static SqlSession getApiSqlSession(HttpServletRequest request) {
        return (SqlSession) request.getAttribute(RequestAttributeKeys.API_SQL_SESSION);
    }

    public static Session findCurrentUserSession(HttpServletRequest request) {
        return (Session) request.getAttribute(RequestAttributeKeys.USER_SESSION_KEY);
    }

    public static void cleanupResources(HttpServletRequest request) {
        closeSession(getApiSqlSession(request));
        closeSession(getClientSqlSession(request));
    }

    public static void checkOwnerRead(HttpServletRequest request, String ownerId) {
        if (!getAuthUser(request).canReadOwner(ownerId)) {
            throw new ResponseException(HttpStatus.FORBIDDEN, "Admin or Owner");
        }
    }

    public static void checkOwnerWrite(HttpServletRequest request, String ownerId) {
        if (!getAuthUser(request).canWriteOwner(ownerId)) {
            throw new ResponseException(HttpStatus.FORBIDDEN, "Admin or Owner");
        }
    }

    public static void checkOwnerCreate(HttpServletRequest request, String ownerId) {
        if (!getAuthUser(request).canCreateOwner(ownerId)) {
            throw new ResponseException(HttpStatus.FORBIDDEN, "Admin or Owner");
        }
    }

    private static void closeSession(SqlSession session) {
        if (session == null) {
            return;
        }

        try {
            session.close();
        } catch (Exception e) {
            logger.error(e.getMessage(), e);
        }
    }
}
