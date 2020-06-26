/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.datasource;

import java.nio.charset.Charset;

/**
 * For encrypting GatewayConnection.sourceKey and GatewayConnection.pin
 */
public class GCKeyTypeHandler extends EncryptedStringTypeHandler {

    private static final byte[] key = "&sSp5e9XryY@acpQ".getBytes(Charset.forName("UTF8"));

    @Override
    public byte[] getKey() {
        return key;
    }
}
