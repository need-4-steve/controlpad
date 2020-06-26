/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.api_key;

import com.controlpad.pay_fac.auth.AuthUtil;
import com.controlpad.pay_fac.client.ClientConfigUtil;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.interceptor.RequestAttributeKeys;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.payman_common.validation.PostChecks;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import java.sql.SQLException;
import java.util.List;

@RestController
@RequestMapping(value = "/api-keys")
public class APIKeyController {

    private static final int MAX_API_KEY_COUNT = 10;

    private static final Logger logger = LoggerFactory.getLogger(APIKeyController.class);

    @Autowired
    AuthUtil authUtil;
    @Autowired
    ClientConfigUtil clientConfigUtil;

    @Authorization(readPrivilege = 5, allowAPIKey = false, apiSqlSession = true)
    @RequestMapping(value = "", method = RequestMethod.GET)
    public List<APIKey> getAllClientAPIKeys(HttpServletRequest request) {

        SqlSession session = (SqlSession) request.getAttribute(RequestAttributeKeys.API_SQL_SESSION);

        return session.getMapper(APIKeyMapper.class).findAPIKeysForClientId(RequestUtil.getClientId(request));
    }

    @Authorization(createPrivilege = 5, allowAPIKey = false, apiSqlSession = true, apiSqlAutoCommit = true)
    @RequestMapping(value = "", method = RequestMethod.POST)
    public APIKey postNewAPIKey(HttpServletRequest request,
                                @RequestBody @Validated({PostChecks.class}) APIKey apiKey) {

        SqlSession apiSqlSession = RequestUtil.getApiSqlSession(request);
        APIKeyMapper apiKeyMapper = apiSqlSession.getMapper(APIKeyMapper.class);

        apiKey.setClientId(RequestUtil.getClientId(request));

        if (apiKeyMapper.isCountMax(apiKey.getClientId(), MAX_API_KEY_COUNT)) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Max api count reached");
        }

        boolean inserted;
        do {
            //Loop allows for duplicate handling
            inserted = insertNewApiKey(apiKey, apiKeyMapper);
        } while (!inserted);

        return apiKey;
    }

    @Authorization(writePrivilege = 5, allowAPIKey = false, apiSqlSession = true, apiSqlAutoCommit = true)
    @RequestMapping(value = "/{api_key}/config", method = RequestMethod.PUT)
    public APIKey updateAPIKey(HttpServletRequest request,
                               @RequestBody APIKeyConfig apiKeyConfig,
                               @PathVariable(value = "api_key") String apiKeyId) {

        SqlSession apiSqlSession = RequestUtil.getApiSqlSession(request);

        APIKeyMapper apiKeyMapper = apiSqlSession.getMapper(APIKeyMapper.class);

        checkOwnership(apiKeyMapper, RequestUtil.getClientId(request), apiKeyId);

        APIKey apiKey = apiKeyMapper.findAPIKeyForId(apiKeyId);
        apiKey.setConfig(apiKeyConfig);

        apiKeyMapper.updateAPIKeyConfig(apiKey);

        return apiKey;
    }

    @Authorization(writePrivilege = 5, allowAPIKey = false, apiSqlSession = true, apiSqlAutoCommit = true)
    @RequestMapping(value = "/{api_key}", method = RequestMethod.DELETE)
    @ResponseStatus(HttpStatus.OK)
    public void deleteApiKey(HttpServletRequest request,
                             @PathVariable(value = "api_key") String apiKeyId) {

        SqlSession apiSqlSession = RequestUtil.getApiSqlSession(request);

        APIKeyMapper apiKeyMapper = apiSqlSession.getMapper(APIKeyMapper.class);

        checkOwnership(apiKeyMapper, RequestUtil.getClientId(request), apiKeyId);

        apiKeyMapper.deleteAPIKey(apiKeyId);
    }

    private void checkOwnership(APIKeyMapper apiKeyMapper, String authClientId, String apiKeyId) {
        String clientId = apiKeyMapper.findClientIdForApiKey(apiKeyId);
        if (clientId == null || !StringUtils.equals(clientId, authClientId)) {
            // Not exposing info that non-owned key exists
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Api key invalid");
        }
    }

    /**
     * Generates a new {@link APIKey} id and inserts it into the database.
     * @param apiKey The base {@link APIKey} to generate a new id for
     * @param apiKeyMapper Mapper used to insert the record
     * @return Inserted. False if id is duplicate
     */
    private boolean insertNewApiKey(APIKey apiKey, APIKeyMapper apiKeyMapper) {
        try {
            apiKey.setId(authUtil.generateRandomApiKey(apiKey.getClientId()));
            if (apiKeyMapper.existsForId(apiKey.getId())) {
                return false;
            }
            apiKeyMapper.insertAPIKey(apiKey);
        } catch (Exception e) {
            logger.error(e.getMessage(), e);
            if (e.getCause() != null && e.getCause() instanceof SQLException) {
                SQLException sqlException = (SQLException)e.getCause();
                if (sqlException.getErrorCode() == 1062) {
                    //Duplicate detection for possible race condition (Unlikely but possible)
                    return false;
                } else {
                    throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
                }
            } else {
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
            }
        }
        return true;
    }
}