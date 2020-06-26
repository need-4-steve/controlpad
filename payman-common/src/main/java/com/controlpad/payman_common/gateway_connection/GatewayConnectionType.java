package com.controlpad.payman_common.gateway_connection;

import org.apache.commons.lang3.StringUtils;

public enum GatewayConnectionType {
    USAEPAY("usaepay"),
    AUTHORIZE_NET("authorizenet"),
    MOCK("mock"),
    PAYPAL("paypal"),
    STRIPE("stripe"),
    SQUARE("square"),
    BRAINTREE("braintree"),
    NMI("nmi"),
    SPLASH_PAYMENTS("splashpayments"),
    PAY_HUB("payhub"),
    NEXIO_PAY("nexiopay"),
    UNKNOWN("");


    public final String slug;

    GatewayConnectionType(String slug) {
        this.slug = slug;
    }

    public static GatewayConnectionType findByName(String name) {
        for (GatewayConnectionType gatewayConnectionType : GatewayConnectionType.values()) {
            if (StringUtils.equalsIgnoreCase(name, gatewayConnectionType.slug)) {
                return gatewayConnectionType;
            }
        }
        return UNKNOWN;
    }
}
