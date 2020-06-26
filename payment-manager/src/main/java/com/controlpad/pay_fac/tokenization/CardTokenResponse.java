package com.controlpad.pay_fac.tokenization;

import com.usaepay.api.jaxws.CreditCardToken;

public class CardTokenResponse {

    private String cardToken;
    private String cardExpiration;
    private String cardNumber;
    private String cardType;

    public CardTokenResponse() {}

    public CardTokenResponse(CreditCardToken creditCardToken) {
        this.cardToken = creditCardToken.getCardRef();
        this.cardExpiration = creditCardToken.getCardExpiration();
        this.cardNumber = creditCardToken.getCardNumber();
        this.cardType = creditCardToken.getCardType();
    }

    public String getCardToken() {
        return cardToken;
    }

    public String getCardExpiration() {
        return cardExpiration;
    }

    public String getCardNumber() {
        return cardNumber;
    }

    public String getCardType() {
        return cardType;
    }
}
