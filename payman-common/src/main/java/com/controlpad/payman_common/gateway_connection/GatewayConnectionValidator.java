package com.controlpad.payman_common.gateway_connection;

import org.apache.commons.lang3.StringUtils;

import javax.validation.ConstraintValidator;
import javax.validation.ConstraintValidatorContext;

public class GatewayConnectionValidator implements ConstraintValidator<GatewayTypeValidate, GatewayConnection> {

    private final String PARAM_ERROR = "%s required for type %s";

    @Override
    public void initialize(GatewayTypeValidate constraintAnnotation) {

    }

    @Override
    public boolean isValid(GatewayConnection gatewayConnection, ConstraintValidatorContext context) {
        GatewayConnectionType type = GatewayConnectionType.findByName(gatewayConnection.getType());
        boolean usernameRequired = false;
        boolean merchantIdRequired = false;
        boolean entityIdRequired = false;
        boolean privateKeyRequired = false;
        boolean publicKeyRequired = false;
        boolean pinRequired = false;
        boolean valid = true;

        switch (type) {
            case MOCK:
                break;
            case AUTHORIZE_NET:
                usernameRequired = true;
                privateKeyRequired = true;
                break;
            case PAYPAL:
                usernameRequired = true;
                privateKeyRequired = true;
                break;
            case SPLASH_PAYMENTS:
                usernameRequired = true;
                entityIdRequired = true;
                privateKeyRequired = true;
                if (gatewayConnection.processCards() || gatewayConnection.processChecks()) {
                    merchantIdRequired = true;
                }
                break;
            case SQUARE:
                usernameRequired = true;
                privateKeyRequired = true;
                // TODO oauth for solo users?
                break;
            case STRIPE:
                privateKeyRequired = true;
                break;
            case USAEPAY:
                privateKeyRequired = true;
                pinRequired = true;
                break;
            case PAY_HUB:
                usernameRequired = true;
                privateKeyRequired = true;
                gatewayConnection.setProcessChecks(false); // Checks are not supported by PayHub (04/06/2017)
                break;
            case NEXIO_PAY:
                usernameRequired = true;
                privateKeyRequired = true;
                merchantIdRequired = true;
                break;
            default:
            case BRAINTREE:
                usernameRequired = true;
                privateKeyRequired = true;
                publicKeyRequired = true;
                // For now this isn't supported as a type, in closed beta last checked 03/29/2017
            case NMI:
            case UNKNOWN:
                context.buildConstraintViolationWithTemplate("Gateway type unknown or unsupported").addConstraintViolation();
                return false;
        }
        if (usernameRequired && StringUtils.isBlank(gatewayConnection.getUsername())) {
            context.buildConstraintViolationWithTemplate(String.format(PARAM_ERROR, "username", type.slug)).addConstraintViolation();
            valid = false;
        }
        if (privateKeyRequired && StringUtils.isBlank(gatewayConnection.getPrivateKey())) {
            context.buildConstraintViolationWithTemplate(String.format(PARAM_ERROR, "privateKey", type.slug)).addConstraintViolation();
            if (valid)
                valid = false;
        }
        if (publicKeyRequired && StringUtils.isBlank(gatewayConnection.getPublicKey())) {
            context.buildConstraintViolationWithTemplate(String.format(PARAM_ERROR, "publicKey", type.slug)).addConstraintViolation();
            if (valid)
                valid = false;
        }
        if (merchantIdRequired && StringUtils.isBlank(gatewayConnection.getMerchantId())) {
            context.buildConstraintViolationWithTemplate(String.format(PARAM_ERROR, "merchantId", type.slug)).addConstraintViolation();
            if (valid)
                valid = false;
        }
        if (entityIdRequired && StringUtils.isBlank(gatewayConnection.getEntityId())) {
            context.buildConstraintViolationWithTemplate(String.format(PARAM_ERROR, "entityId", type.slug)).addConstraintViolation();
            if (valid)
                valid = false;
        }
        if (pinRequired && StringUtils.isBlank(gatewayConnection.getPin())) {
            context.buildConstraintViolationWithTemplate(String.format(PARAM_ERROR, "pin", type.slug)).addConstraintViolation();
            if (valid)
                valid = false;
        }
        return valid;
    }
}
