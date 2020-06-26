package com.controlpad.pay_fac.gateway.splash_payments;


public class SplashTransactionResult {
    private String id;
    private String created;
    private String txn;
    private Integer type;
    private String message;
    private Integer code;

    public String getId() {
        return id;
    }

    public String getCreated() {
        return created;
    }

    public String getTxn() {
        return txn;
    }

    public Integer getType() {
        return type;
    }

    public String getMessage() {
        return message;
    }

    public Integer getCode() {
        return code;
    }
}
