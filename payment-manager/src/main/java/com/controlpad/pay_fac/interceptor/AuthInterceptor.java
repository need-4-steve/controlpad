package com.controlpad.pay_fac.interceptor;

import com.controlpad.pay_fac.api_key.APIKey;
import com.controlpad.pay_fac.api_key.APIKeyMapper;
import com.controlpad.pay_fac.api_key.APIKeyUtil;
import com.controlpad.pay_fac.auth.AuthMapper;
import com.controlpad.pay_fac.auth.AuthUtil;
import com.controlpad.pay_fac.auth.Session;
import com.controlpad.pay_fac.client.ClientConfigUtil;
import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.pay_fac.util.ResponseUtil;
import com.controlpad.payman_common.client.ClientMapper;
import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.payman_user.PayManUser;
import com.controlpad.payman_common.payman_user.Privilege;
import com.controlpad.payman_common.util.GsonUtil;
import com.google.gson.JsonObject;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;
import org.springframework.http.HttpStatus;
import org.springframework.security.jwt.Jwt;
import org.springframework.security.jwt.JwtHelper;
import org.springframework.security.jwt.crypto.sign.InvalidSignatureException;
import org.springframework.security.jwt.crypto.sign.MacSigner;
import org.springframework.web.method.HandlerMethod;
import org.springframework.web.servlet.handler.AbstractHandlerMapping;
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import static com.controlpad.pay_fac.interceptor.RequestAttributeKeys.*;

public class AuthInterceptor extends HandlerInterceptorAdapter {
    /**
     * Levels
     * 0: Payman Superuser
     * 1: Payman Admin
     * 2: Payman Reporter - expected only for reads
     * 3: Payman APIKey
     * 5: Client Admin
     * 6: Client Reporter - expected only for reads
     * 7: Client APIKey
     * 8: Client User
     */
    private static final Logger logger = LoggerFactory.getLogger(AuthInterceptor.class);

    private static final String AUTHORIZATION_HEADER_KEY = "Authorization";
    private static final String SESSION_KEY_HEADER = "SessionKey";
    private static final String API_KEY_HEADER = "APIKey";
    private static final String BEARER_HEADER = "Bearer";

    private SqlSessionUtil sqlSessionUtil;
    private ClientConfigUtil clientConfigUtil;
    private AuthUtil authUtil;
    private APIKeyUtil apiKeyUtil;

    public AuthInterceptor(SqlSessionUtil sqlSessionUtil, ClientConfigUtil clientConfigUtil, AuthUtil authUtil, APIKeyUtil apiKeyUtil) {
        this.sqlSessionUtil = sqlSessionUtil;
        this.clientConfigUtil = clientConfigUtil;
        this.authUtil = authUtil;
        this.apiKeyUtil = apiKeyUtil;
    }

    @Override
    public boolean preHandle(HttpServletRequest request, HttpServletResponse response, Object handler) throws Exception {
        if (!(handler instanceof HandlerMethod)) {
            return true;
        }
        HandlerMethod handlerMethod = (HandlerMethod) handler;
        Authorization authorization = handlerMethod.getMethod().getAnnotation(Authorization.class);

        if (authorization != null) {
            SqlSession apiSqlSession = null;
            PayManUser authUser;
            String keyType;

            String key = request.getHeader(API_KEY_HEADER);
            if (key != null) {
                keyType = API_KEY_HEADER;
            } else if ((key = request.getHeader(SESSION_KEY_HEADER)) != null){
                keyType = SESSION_KEY_HEADER;
            } else {
                String authHeader = request.getHeader(AUTHORIZATION_HEADER_KEY);
                if (authHeader == null)
                    throw ResponseUtil.getUnauthorized("No authorization header found.");

                String[] authData = authHeader.split("\\s+");
                if (authData.length != 2)
                    throw ResponseUtil.getUnauthorized("Authorization header format invalid.");

                keyType = authData[0];
                key = authData[1];
            }

            switch (keyType) {
                case API_KEY_HEADER:
                    authUser = verifyAPIKey(key, authorization, handlerMethod, apiSqlSession);
                    break;
                case SESSION_KEY_HEADER:
                    authUser = verifySessionKey(key, request, authorization, handlerMethod);
                    break;
                case BEARER_HEADER:
                    authUser = verifyJWT(key);
                    break;
                default:
                    throw ResponseUtil.getUnauthorized("Authentication type not supported.");
            }
            verifyUserPrivilege(authorization, authUser, request);

            /**
             * Set up:
             * 1. client ID
             * 2. SqlSession
             */
            MDC.put("clientID", authUser.getClientId());

            request.setAttribute(CLIENT_ID, authUser.getClientId());
            request.setAttribute(USER_KEY, authUser);

            configSqlSession(request, authUser, apiSqlSession, authorization);

        }
        return true;
    }

    @Override
    public void afterCompletion(HttpServletRequest request, HttpServletResponse response, Object handler, Exception ex) throws Exception {
        RequestUtil.cleanupResources(request);
        MDC.clear();
    }

    private PayManUser verifyAPIKey(String key, Authorization authorization, HandlerMethod handlerMethod, SqlSession apiSqlSession) throws ResponseException{
        if(!authorization.allowAPIKey()){
            throw ResponseUtil.getInsufficientPrivileges("Insufficient privilege: API key is not allowed");
        }
        if(authorization.superuser()){
            throw ResponseUtil.getInsufficientPrivileges("Insufficient privilege: only superuser can access");
        }
        APIKey apiKey = apiKeyUtil.getApiKeyMap().get(key);

        if (apiKey == null) {
            apiSqlSession = sqlSessionUtil.openPaymanSession(authorization.apiSqlAutoCommit());
            apiKey = apiSqlSession.getMapper(APIKeyMapper.class).findAPIKeyForId(key);
        }

        if (apiKey == null || apiKey.getDisabled()) {
            throw ResponseUtil.getUnauthorized("Invalid key");
        }
        apiKey.verifyPermissions(handlerMethod.getMethod().getAnnotation(APIKeyPermissions.class));

        return PayManUser.createProxyUser(apiKey.getClientId());
    }

    private PayManUser verifySessionKey(String key, HttpServletRequest request, Authorization authorization, HandlerMethod handlerMethod) throws ResponseException{
        if(!authorization.allowSessionKey()) throw ResponseUtil.getUnauthorized("Cannot use session key to access this endpoint");

        SqlSession apiSqlSession = sqlSessionUtil.openPaymanSession(authorization.apiSqlAutoCommit());
        PayManUser authUser = apiSqlSession.getMapper(AuthMapper.class).findUserForSession(key);
        Session session = apiSqlSession.getMapper(AuthMapper.class).findSessionForId(key);
        if (session == null) throw ResponseUtil.getUnauthorized("Session Invalid");
        if (session.getExpiresAt() <= System.currentTimeMillis()) throw ResponseUtil.getUnauthorized("Session expired");
        if (authUser == null) throw ResponseUtil.getInsufficientPrivileges("User for session no longer exists");

        authUtil.refreshSession(key, apiSqlSession);
        if (!authorization.apiSqlAutoCommit()) apiSqlSession.commit();
        if (authUser.getClientId() == null) authUser.setClientId(session.getClientId());

        if (authorization.userSession()) {
            request.setAttribute(RequestAttributeKeys.USER_SESSION_KEY, session);
        }

        return authUser;
    }

    private void configSqlSession(HttpServletRequest request, PayManUser authUser, SqlSession apiSqlSession, Authorization authorization){
        //config client SQL session
        if (authorization.clientSqlSession()) {
            String clientId = authUser.getClientId();
            if (clientId == null) {
                throw ResponseUtil.getInsufficientPrivileges("Session requires a client id");
            }
            if (!sqlSessionUtil.existsDatasourceForClient(clientId)) {
                if (apiSqlSession == null) {
                    apiSqlSession = sqlSessionUtil.openPaymanSession(authorization.apiSqlAutoCommit());
                }
                ControlPadClient controlPadClient = apiSqlSession.getMapper(ClientMapper.class).findClientForId(clientId);
                if (controlPadClient == null) {
                    //TODO log
                    throw new ResponseException(HttpStatus.BAD_REQUEST, "No client record exists.");
                }
                clientConfigUtil.getClientMap().put(controlPadClient.getId(), controlPadClient);
                sqlSessionUtil.addClientDatasource(controlPadClient);
            }
            request.setAttribute(CLIENT_SQL_SESSION, sqlSessionUtil.openSession(clientId, authorization.clientSqlAutoCommit()));
        }

        //config API SQL Session
        if (authorization.apiSqlSession()) {
            request.setAttribute(API_SQL_SESSION, (apiSqlSession != null ? apiSqlSession : sqlSessionUtil.openPaymanSession(authorization.apiSqlAutoCommit())));
        } else if (apiSqlSession != null){
            apiSqlSession.close();
        }
    }

    private void verifyUserPrivilege(Authorization authorization, PayManUser authUser, HttpServletRequest request){
        if(authorization.superuser() && !authUser.getPrivilege().isSuperuser()){
            throw ResponseUtil.getInsufficientPrivileges("Insufficient privileges: need superuser");
        }

        if(StringUtils.equals(request.getMethod(), "GET") && authorization.readPrivilege() < authUser.getPrivilege().getReadPrivilege()){
            throw ResponseUtil.getInsufficientPrivileges("This session doesn't have the privilege to read");
        }
        if((StringUtils.equals(request.getMethod(), "PUT") || StringUtils.equals(request.getMethod(), "PATCH")) &&
                authorization.writePrivilege() < authUser.getPrivilege().getWritePrivilege()){
            throw ResponseUtil.getInsufficientPrivileges("This session doesn't have the privilege to write");
        }
        if((StringUtils.equals(request.getMethod(), "POST") || StringUtils.equals(request.getMethod(), "DELETE")) &&
                authorization.createPrivilege() < authUser.getPrivilege().getCreatePrivilege()){
            throw ResponseUtil.getInsufficientPrivileges("This session doesn't have the privilege to create");
        }
    }

    private PayManUser verifyJWT(String jwtToken) {
        try {
            Jwt jwt = JwtHelper.decode(jwtToken);
            JsonObject jwtBody = GsonUtil.getGson().fromJson(jwt.getClaims(), JsonObject.class);
            ControlPadClient client = clientConfigUtil.getClientByOrgId(jwtBody.get("orgId").getAsString());
            if (client == null) {
                throw new ResponseException(HttpStatus.UNAUTHORIZED, "Client key not set");
            }
            jwt.verifySignature(new MacSigner(client.getJwtKey()));

            return new PayManUser(
                    jwtBody.get("sub").getAsString(),
                    client.getId(),
                    null,
                    null,
                    null,
                    getPrivilegeForRole(jwtBody.get("role").getAsString())
            );
        } catch (ResponseException re) {
            // Rethrow response exception
            throw re;
        } catch (InvalidSignatureException | IllegalArgumentException ie) {
            // Signatures don't match
            logger.error("Invalid signature for token: " + jwtToken.substring(0, jwtToken.lastIndexOf(".")));
            throw new ResponseException(HttpStatus.UNAUTHORIZED);
        } catch (Exception e) {
            // Log all unexpected errors
            logger.error("Auth Failed for token: " + jwtToken.substring(0, jwtToken.lastIndexOf(".")), e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    private Privilege getPrivilegeForRole(String role) {
        int level;
        switch (role) {
            case "Superadmin":
            case "Admin":
                level = 5;
                break;
            case "Rep":
            case "Customer":
                level = 8;
                break;
            default:
                throw new ResponseException(HttpStatus.UNAUTHORIZED, "Role not supported: " + role);
        }
        return new Privilege(false, false, level, level, level);
    }
}