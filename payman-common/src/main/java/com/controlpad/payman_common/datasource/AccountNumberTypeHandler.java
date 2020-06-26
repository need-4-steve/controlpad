/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.datasource;


import java.nio.charset.Charset;

/**
 * For encrypting account.number and userAccount.number
 */
public class AccountNumberTypeHandler extends EncryptedStringTypeHandler {

    private static final byte[] key = "a9c8}3VEW1*q8doI".getBytes(Charset.forName("UTF8"));

    @Override
    public byte[] getKey() {
        return key;
    }
}