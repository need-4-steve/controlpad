/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.util;

import org.apache.commons.lang3.ArrayUtils;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.crypto.BadPaddingException;
import javax.crypto.Cipher;
import javax.crypto.IllegalBlockSizeException;
import javax.crypto.NoSuchPaddingException;
import javax.crypto.spec.SecretKeySpec;
import java.nio.charset.Charset;
import java.nio.charset.StandardCharsets;
import java.security.InvalidKeyException;
import java.security.NoSuchAlgorithmException;
import java.util.Random;

public class EncryptUtil {

    private static EncryptUtil instance;

    private static final int SALT_LENGTH = 8;

    private static final Logger logger = LoggerFactory.getLogger(EncryptUtil.class);

    private Random random;

    public static EncryptUtil getInstance() {
        if (instance == null) {
            synchronized (EncryptUtil.class) {
                if (instance == null)
                    instance = new EncryptUtil();
            }
        }
        return instance;
    }

    private EncryptUtil() {
        random = new Random();
    }

    public byte[] encryptString(byte[] key, String value) {
        try {
            SecretKeySpec secretKeySpec = new SecretKeySpec(key, "Blowfish");
            Cipher cipher = Cipher.getInstance("Blowfish");
            cipher.init(Cipher.ENCRYPT_MODE, secretKeySpec);
            byte[] data = value.getBytes(Charset.forName("UTF8"));
            byte[] salt = new byte[SALT_LENGTH];
            random.nextBytes(salt);
            byte[] combined = new byte[data.length + salt.length];
            System.arraycopy(data, 0, combined, 0, data.length);
            System.arraycopy(salt, 0, combined, data.length, salt.length);
            return cipher.doFinal(combined);
        } catch (BadPaddingException |IllegalBlockSizeException |NoSuchAlgorithmException|NoSuchPaddingException |InvalidKeyException e) {
            logger.error(e.getMessage(), e);
            return null;
        }
    }

    public String decryptString(byte[] key, byte[] data) {
        try {
            SecretKeySpec secretKeySpec = new SecretKeySpec(key, "Blowfish");
            Cipher cipher = Cipher.getInstance("Blowfish");
            cipher.init(Cipher.DECRYPT_MODE, secretKeySpec);
            byte[] decrypted = cipher.doFinal(data);
            return new String(ArrayUtils.subarray(decrypted, 0, decrypted.length - SALT_LENGTH), StandardCharsets.UTF_8);
        } catch (BadPaddingException|IllegalBlockSizeException|NoSuchAlgorithmException|NoSuchPaddingException|InvalidKeyException e) {
            logger.error(e.getMessage(), e);
            return null;
        }
    }
}
