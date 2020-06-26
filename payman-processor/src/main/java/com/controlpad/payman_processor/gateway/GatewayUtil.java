package com.controlpad.payman_processor.gateway;


import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionType;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Component;

@Component
public class GatewayUtil {

    private static final Logger logger = LoggerFactory.getLogger(GatewayUtil.class);

    private USAEpay usaEpay;
    private AuthorizeNet authorizeNet;
    private MockGateway mockGateway;
    private Stripe stripe;
    private Paypal paypal;
    private SplashPayments splashPayments;
    private NexioPay nexioPay;

    public GatewayUtil() {
        stripe = new Stripe();
        paypal = new Paypal();
        usaEpay = new USAEpay();
        authorizeNet = new AuthorizeNet();
        mockGateway = new MockGateway();
        splashPayments = new SplashPayments();
        nexioPay = new NexioPay();
    }

    public GatewayConnectionType getAppName(GatewayConnection gatewayConnection) {
        if (gatewayConnection == null) {
            return GatewayConnectionType.UNKNOWN;
        } else {
            return GatewayConnectionType.findByName(gatewayConnection.getType());
        }
    }

    public Gateway getGatewayApi(GatewayConnection gatewayConnection) {
        switch (getAppName(gatewayConnection)) {
            case USAEPAY:
                return usaEpay;
            case AUTHORIZE_NET:
                return authorizeNet;
            case STRIPE:
                return stripe;
            case PAYPAL:
                return paypal;
            case SPLASH_PAYMENTS:
                return splashPayments;
            case NEXIO_PAY:
                return nexioPay;
            case MOCK:
                if (!gatewayConnection.getIsSandbox()) {
                    // TODO check client isSandbox and log this error
                    throw new RuntimeException("Attempting to use mock gateway without proper settings");
                }
                return mockGateway;
            default:
                // TODO log info
                throw new RuntimeException(String.format("Unknown gateway: %s", gatewayConnection.getType()));
        }
    }

}
