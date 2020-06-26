package com.controlpad.pay_fac.express;

import com.controlpad.pay_fac.account.AccountUtils;
import com.controlpad.pay_fac.api_key.APIKey;
import com.controlpad.pay_fac.api_key.APIKeyConfig;
import com.controlpad.pay_fac.api_key.APIKeyMapper;
import com.controlpad.pay_fac.auth.AuthUtil;
import com.controlpad.pay_fac.client.ClientConfigUtil;
import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.gateway.GatewayUtil;
import com.controlpad.pay_fac.gateway.splash_payments.CreateMerchantBody;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.interceptor.RequestAttributeKeys;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.pay_fac.util.ParamValidations;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.pay_fac.util.ResponseUtil;
import com.controlpad.payman_common.client.ClientConfig;
import com.controlpad.payman_common.client.ClientMapper;
import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.client.Features;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionType;
import com.controlpad.payman_common.migration.DatabaseType;
import com.controlpad.payman_common.migration.MigrationUtil;
import com.controlpad.payman_common.payman_user.PayManUser;
import com.controlpad.payman_common.payman_user.PayManUserMapper;
import com.controlpad.payman_common.payman_user.Privilege;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamConfig;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_common.validation.AlwaysCheck;
import com.controlpad.payman_common.validation.FullCheck;
import org.apache.commons.lang3.StringUtils;
import org.apache.commons.validator.routines.RegexValidator;
import org.apache.commons.validator.routines.UrlValidator;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.RestController;

import javax.servlet.http.HttpServletRequest;

@RestController
@RequestMapping(value = "/express")
public class ExpressSignupController {

    public static final String EXPRESS_KEY = "14MrSd8BjJXYtIwdmbVU24boqmR5JLT8iIeCFTlc";

    private static final Logger logger = LoggerFactory.getLogger(ExpressSignupController.class);

    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    AuthUtil authUtil;
    @Autowired
    AccountUtils accountUtils;
    @Autowired
    ClientConfigUtil clientConfigUtil;
    @Autowired
    GatewayUtil gatewayUtil;
    @Autowired
    IDUtil idUtil;

    private UrlValidator urlValidator;
    private MigrationUtil migrationUtil;

    public ExpressSignupController() {
        urlValidator = new UrlValidator(new String[]{"http", "https", "ftp"}, new RegexValidator(".*"), 0L);
        migrationUtil = new MigrationUtil();
    }

    @RequestMapping(value = "/default-client-model")
    public ExpressClientResponse createExpressClient(HttpServletRequest request,
                                              @RequestBody @Validated({AlwaysCheck.class}) ExpressClientRequest expressClientRequest) {
        validateAuth(request);

        SqlSession paymanSession = sqlSessionUtil.openPaymanSession(false);
        request.setAttribute(RequestAttributeKeys.API_SQL_SESSION, paymanSession); // For auto close
        PayManUserMapper userMapper = paymanSession.getMapper(PayManUserMapper.class);
        ClientMapper clientMapper = paymanSession.getMapper(ClientMapper.class);

        if (expressClientRequest.getUser() != null && userMapper.existsForUsername(expressClientRequest.getUser().getUsername())) {
            return new ExpressClientResponse(false, 11, "Username already taken");
        }

        // Migrate database
        expressClientRequest.getSqlConfig().setDbType(DatabaseType.PAYMAN_CLIENT.getSlug());
        if (!migrationUtil.migrate(expressClientRequest.getSqlConfig())) {
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Failed to migrate database");
        }

        // Create client record
        ClientConfig clientConfig = new ClientConfig(new Features(true, true, false, false, true, true));
        ControlPadClient client = new ControlPadClient(idUtil.generateId(), expressClientRequest.getClientName(),
                clientConfig, expressClientRequest.getSqlConfig(), expressClientRequest.getSandbox());
        clientMapper.insertClient(client);

        if (expressClientRequest.getUser() != null) {
            // Add client admin user
            PayManUser clientAdmin = new PayManUser(idUtil.generateId(), client.getId(), expressClientRequest.getUser().getUsername(),
                    authUtil.encodePassword(expressClientRequest.getUser().getPassword()),
                    null, new Privilege(false, false, 0, 0, 0));
            userMapper.insertPayManUser(clientAdmin);
        }

        String apiKey = createApiKey(client, paymanSession.getMapper(APIKeyMapper.class));

        sqlSessionUtil.addClientDatasource(client);
        SqlSession clientSession = sqlSessionUtil.openSession(client.getId(), false);
        request.setAttribute(RequestAttributeKeys.CLIENT_SQL_SESSION, clientSession); // For auto close

        setupTeams(clientSession);

        setupGatewayConnections(clientSession, expressClientRequest);

        paymanSession.commit();
        clientSession.commit();
        return new ExpressClientResponse(new ExpressClientResponse.ExpressClientData(apiKey));
    }

    @RequestMapping(value = "/merchant", method = RequestMethod.POST)
    @Authorization(writePrivilege = 1, clientSqlSession = true)
    public CommonResponse<GatewayConnection> createSplashPaymentsMerchant(HttpServletRequest request,
                                                                          @RequestBody @Validated({FullCheck.class})
                                                                                           CreateMerchantBody createMerchantBody) {

        if (!urlValidator.isValid(createMerchantBody.getBusiness().getWebsite())) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "website url not valid format");
        }
        ParamValidations.validatePastBirthDate(createMerchantBody.getBusiness().getOwner().getDob(), "dob");

        GatewayConnection masterConnection = gatewayUtil.selectGatewayConnection(RequestUtil.getClientSqlSession(request),
                null, "splash", null, null, null, true, GatewayConnectionType.SPLASH_PAYMENTS.slug);
        if (masterConnection == null) {
            return new CommonResponse<>(false, 40, "No master gateway connection to create accounts under.");
        }
        String clientName = clientConfigUtil.getClientName(RequestUtil.getClientId(request));

        return gatewayUtil.getSplashPayments().createExpressMerchant(clientName, masterConnection, createMerchantBody);
    }

    private void validateAuth(HttpServletRequest request) {
        String authHeader = request.getHeader("APIKEY");
        if (authHeader == null) {
            authHeader = request.getHeader("Authorization");
        }

        if (authHeader == null || !StringUtils.equals(authHeader, EXPRESS_KEY)) {
            throw ResponseUtil.getUnauthorized(null);
        }
    }

    private String createApiKey(ControlPadClient client, APIKeyMapper apiKeyMapper) {
        try {
            APIKey apiKey = authUtil.buildNewApiKey(client.getId(), new APIKeyConfig(true, true, false));
            apiKeyMapper.insertAPIKey(apiKey);
            return apiKey.getId();
        } catch (Exception e) {
            logger.error("Failed to generate api key");
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Failed to generate APIKey, ERROR:" + e);
        }
    }

    private void setupTeams(SqlSession clientSession) {
        Team company = new Team("company", "Company Team",
                new TeamConfig(false, false, false, false, false,
                    TeamConfig.EWALLET_LIMIT_DEFAULT, null));
        Team wholesale = new Team("wholesale", "Wholesale Team",
                new TeamConfig(false, false, false, false, false,
                        TeamConfig.EWALLET_LIMIT_DEFAULT, null));
        Team repTeam = new Team("rep", "Rep Team",
                new TeamConfig(false, false, false, false, false,
                        TeamConfig.EWALLET_LIMIT_DEFAULT, null));
        Team controlpadTeam = new Team("controlpad", "Controlpad Services",
                new TeamConfig(false, false, false, false, false, TeamConfig.EWALLET_LIMIT_DEFAULT,
                        null));

        TeamMapper teamMapper = clientSession.getMapper(TeamMapper.class);
        teamMapper.insert(wholesale);
        teamMapper.insert(company);
        teamMapper.insert(repTeam);
        teamMapper.insert(controlpadTeam);
    }

    private void setupGatewayConnections(SqlSession clientSession, ExpressClientRequest expressClientRequest) {
        GatewayConnectionMapper gatewayConnectionMapper = clientSession.getMapper(GatewayConnectionMapper.class);
        expressClientRequest.getGatewayConnection().setTeamId("company");
        expressClientRequest.getGatewayConnection().setUserId(null);
        if (expressClientRequest.getGatewayConnection().processCards() == null) {
            expressClientRequest.getGatewayConnection().setProcessCards(true);
        }
        if (expressClientRequest.getGatewayConnection().processChecks() == null) {
            expressClientRequest.getGatewayConnection().setProcessChecks(false);
        }
        if (expressClientRequest.getGatewayConnection().processInternal() == null) {
            expressClientRequest.getGatewayConnection().setProcessInternal(false);
        }
        if (expressClientRequest.getGatewayConnection().getActive() == null) {
            expressClientRequest.getGatewayConnection().setActive(true);
        }
        gatewayConnectionMapper.insert(expressClientRequest.getGatewayConnection());
        // Set gateway connection to wholesale as well
        expressClientRequest.getGatewayConnection().setTeamId("wholesale");
        gatewayConnectionMapper.insert(expressClientRequest.getGatewayConnection());

        if (expressClientRequest.getSandbox()) {
            gatewayConnectionMapper.insert(gatewayUtil.getControlpadSandboxGateway());
        } else {
            gatewayConnectionMapper.insert(gatewayUtil.getControlpadGateway());
        }
    }
}