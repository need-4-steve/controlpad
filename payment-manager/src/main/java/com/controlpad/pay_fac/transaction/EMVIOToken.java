package com.controlpad.pay_fac.transaction;

public class EMVIOToken {

    private String token;
    private Long expire;

    public EMVIOToken(String token, Long expire) {
        this.token = token;
        this.expire = expire;
    }

    public String getToken() {
        return token;
    }

    public Long getExpire() {
        return expire;
    }
}
