package com.controlpad.payman_common.util;


import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.UnsupportedEncodingException;
import java.util.Base64;

public class DecodeUtil {
    private static final Logger logger = LoggerFactory.getLogger(DecodeUtil.class);

    public static String getName(String code){
        String name = "";
        if(code == null){
            logger.error("Encrypted data is null. TokenRequest Magstripe: " + code);
            return null;
        }
        if(code.length() < 6){
            logger.error("Encrypted data is too short. TokenRequest Magstripe: " + code);
            return null;
        }
        if(code.substring(0,6).equals("enc://")){
            code = code.substring(6);
            try {
                String decode = new String(Base64.getDecoder().decode(code), "UTF-8");
                int start = decode.indexOf('^');
                int end = decode.indexOf('^', start+1);
                if(start>0 && end>start){
                    name = decode.substring(start+1, end);
                }else{
                    logger.error("Cannot find card holder's name. TokenRequest Magstripe: " + code);
                    return null;
                }
            } catch (UnsupportedEncodingException e) {
                logger.error(e.getMessage(), e);
            }
            return name;
        }else{
            int start = code.indexOf('^');
            int end = code.indexOf('^', start+1);
            if(start>0 && end>start){
                name = code.substring(start+1, end);
            }else{
                logger.error("Cannot find card holder's name. TokenRequest Magstripe: " + code);
                return null;
            }
        }
        return name;
    }
}
