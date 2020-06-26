package com.controlpad.payman_common.transaction;

import com.controlpad.payman_common.util.DecodeUtil;
import com.controlpad.payman_common.validation.AlwaysCheck;
import com.controlpad.payman_common.validation.CardValidate;


@CardValidate(groups = AlwaysCheck.class)
public class Card {
    private String number;
    private Integer month;
    private Integer year;
    private String code;
    private CardType type;

    private String name;

    private String magstripe;
    private String encMagstripe;

    private String gatewayCustomerId;
    private String token;
    private String nonce;

    public Card() {
    }

    public Card(String number, int month, int year, String code) {
        this.number = number;
        this.month = month;
        this.year = year;
        this.code = code;
    }

    public Card setNumber(String number) {
        this.number = number;
        return this;
    }

    public Card setMonth(int month) {
        this.month = month;
        return this;
    }

    public Card setYear(int year) {
        this.year = year;
        return this;
    }

    public Card setCode(String code) {
        this.code = code;
        return this;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getNumber() {
        return number;
    }

    public Integer getMonth() {
        return month;
    }

    public Integer getYear() {
        return year;
    }

    public String getCode() {
        return code;
    }

    public CardType getType() {
        if(type == null) {
            if (number != null) {
                type = CardType.detect(this.number);
            }
            // TODO can we figure type from a magstripe?
        }
        return type;
    }

    public String getMagstripe() {
        return magstripe;
    }

    public String getEncMagstripe() {
        return encMagstripe;
    }

    public String getGatewayCustomerId() {
        return gatewayCustomerId;
    }

    public String getToken() {
        return token;
    }

    public String getNonce() {
        return nonce;
    }

    public String getName() {
        return name;
    }

    public String getExpirationDate(){
        return String.format("%02d%s", month, String.valueOf(year).substring(2, 4));
    }

    public String getCardHolder() {
        if (magstripe != null || encMagstripe != null) {
            String name = DecodeUtil.getName((magstripe != null ? magstripe : encMagstripe));
            if (name != null) {
                return name;
            }
        }

        return getName();
    }

    public void setMonth(Integer month) {
        this.month = month;
    }

    public void setYear(Integer year) {
        this.year = year;
    }

    public void setType(CardType type) {
        this.type = type;
    }

    public void setMagstripe(String magstripe) {
        this.magstripe = magstripe;
    }

    public void setEncMagstripe(String encMagstripe) {
        this.encMagstripe = encMagstripe;
    }

    public void setGatewayCustomerId(String gatewayCustomerId) {
        this.gatewayCustomerId = gatewayCustomerId;
    }

    public void setToken(String token) {
        this.token = token;
    }

    public void setNonce(String nonce) {
        this.nonce = nonce;
    }
}