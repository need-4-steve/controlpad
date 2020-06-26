package com.controlpad.pay_fac.gateway;

import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.payment_info.CardPayment;
import com.controlpad.pay_fac.payment_info.TokenRequest;
import com.controlpad.pay_fac.transaction.CheckPayment;
import com.controlpad.pay_fac.transaction.CheckTransfer;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.pay_fac.util.ResponseUtil;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionType;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.util.GsonUtil;
import org.apache.commons.lang3.BooleanUtils;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Component;

import java.io.File;
import java.io.FileReader;
import java.util.List;
import java.util.Random;

@Component
public class GatewayUtil {

    private static final Logger logger = LoggerFactory.getLogger(GatewayUtil.class);

    private Random random;

    USAEpay usaEpay;
    AuthorizeNet authorizeNet;
    PayPal payPal;
    MockGateway mockGateway;
    Stripe stripe;
    SplashPayments splashPayments;

    private GatewayConnection cpSandboxConnection;
    private GatewayConnection cpConnection = null;

    @Autowired
    public GatewayUtil(IDUtil idUtil) {
        random = new Random();
        stripe = new Stripe(idUtil);
        usaEpay = new USAEpay(idUtil);
        payPal = new PayPal(idUtil);
        authorizeNet = new AuthorizeNet(idUtil);
        mockGateway = new MockGateway(idUtil);
        splashPayments = new SplashPayments(idUtil);
        cpSandboxConnection = new GatewayConnection("controlpad", null, "Controlpad Services", null,
                "_Jw9x02GvCMmyHW5pUzq0wd1811w7b5E", null, "password", "usaepay", true, false, true, false, false, true);
        try {
            File sqlConfigFile = new File("cp_gateway.json");
            if (!sqlConfigFile.exists()) {
                sqlConfigFile = new File("/etc/opt/payman/cp_gateway.json");
            }
            cpConnection = GsonUtil.getGson().fromJson(new FileReader(sqlConfigFile), GatewayConnection.class);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public GatewayConnectionType getAppName(String appName) {
            return GatewayConnectionType.findByName(appName);
    }

    public Gateway getGatewayApi(GatewayConnection gatewayConnection) {
        if (gatewayConnection == null)
            throw new ResponseException(HttpStatus.FAILED_DEPENDENCY, "Seller has no active payments account");
        return getGatewayApi(gatewayConnection.getType());
    }

    public Gateway getGatewayApi(String appName) {
        switch (getAppName(appName)) {
            case USAEPAY:
                return usaEpay;
            case AUTHORIZE_NET:
                return authorizeNet;
            case PAYPAL:
                return payPal;
            case MOCK:
                return mockGateway;
            case STRIPE:
                return stripe;
            case SPLASH_PAYMENTS:
                return splashPayments;
            case NEXIO_PAY:
                return null;
            case NMI:
            case BRAINTREE:
            default:
                throw ResponseUtil.getInvalidApplication();
        }
    }

    public SplashPayments getSplashPayments() {
        return splashPayments;
    }

    public PayPal getPayPal() {
        return payPal;
    }

    public GatewayConnection getGatewayConnection(SqlSession clientSession, Transaction transaction,
                                                  Boolean processCards, Boolean processChecks, Boolean processInternal) {
        return selectGatewayConnection(clientSession, transaction.getGatewayConnectionId(), transaction.getTeamId(),
                transaction.getPayeeUserId(), processCards, processChecks, processInternal, null);
    }

    public GatewayConnection getGatewayConnection(SqlSession clientSession, CardPayment cardPayment) {
        return selectGatewayConnection(clientSession, cardPayment.getGatewayConnectionId(), cardPayment.getTeamId(), cardPayment.getPayeeUserId(),
                true, null, null, null);
    }

    public GatewayConnection getGatewayConnection(SqlSession clientSession, CheckPayment checkPayment) {
        return selectGatewayConnection(clientSession, checkPayment.getGatewayConnectionId(), checkPayment.getTeamId(), checkPayment.getPayeeUserId(),
                null, true, null, null);
    }

    public GatewayConnection getGatewayConnection(SqlSession clientSession, CheckTransfer checkTransfer) {
        return selectGatewayConnection(clientSession, checkTransfer.getGatewayConnectionId(), checkTransfer.getTeamId(), checkTransfer.getPayeeUserId(),
                null, true, null, null);
    }

    public GatewayConnection getGatewayConnection(SqlSession clientSession, TokenRequest tokenRequest, String teamId) {
        return selectGatewayConnection(clientSession, tokenRequest.getGatewayConnectionId(), teamId, null, true, null, null, null);
    }

    // TODO paypal

    public GatewayConnection selectGatewayConnection(SqlSession clientSession, Long gatewayConnectionId, String teamId, String userId,
                                                     Boolean processCards, Boolean processChecks, Boolean processInternal, String type) {

        GatewayConnectionMapper gatewayConnectionMapper = clientSession.getMapper(GatewayConnectionMapper.class);

        if (gatewayConnectionId != null) {
            return gatewayConnectionMapper.findById(gatewayConnectionId);
        }

        Boolean findUserGatewayConnection = clientSession.getMapper(TeamMapper.class).configByTeam(teamId).getUserGatewayConnections();
        // List connections that can be used for
        List<Long> availableConnections;
        if (BooleanUtils.isTrue(findUserGatewayConnection)) {
            availableConnections = gatewayConnectionMapper.searchId(teamId, userId, processCards, processChecks, processInternal,
                    type, true);
        } else {
            availableConnections = gatewayConnectionMapper.searchId(teamId, null, processCards, processChecks, processInternal,
                    type, true);
        }

        if (availableConnections.isEmpty()) {
            return null;
        }
        if (availableConnections.size() == 1) {
            GatewayConnection gatewayConnection = gatewayConnectionMapper.findById(availableConnections.get(0));
            // Add to context for logs
            MDC.put("GatewayConnectionId", gatewayConnection.getId().toString());
            MDC.put("GatewayConnectionType", gatewayConnection.getType());
            return gatewayConnection;
        }
        int index = random.nextInt(availableConnections.size());
        GatewayConnection gatewayConnection = gatewayConnectionMapper.findById(availableConnections.get(index));
        // Add to context for logs
        MDC.put("GatewayConnectionId", gatewayConnection.getId().toString());
        MDC.put("GatewayConnectionType", gatewayConnection.getType());
        return gatewayConnection;
    }

    public GatewayConnection getControlpadSandboxGateway() {
        return cpSandboxConnection;
    }

    public GatewayConnection getControlpadGateway() {
        return cpConnection;
    }
}
