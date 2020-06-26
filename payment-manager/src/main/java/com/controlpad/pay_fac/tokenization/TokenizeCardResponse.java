package com.controlpad.pay_fac.tokenization;

import com.controlpad.pay_fac.common.CommonResponse;
import com.paypal.api.payments.CreditCard;
import com.usaepay.api.jaxws.CreditCardToken;
import org.apache.commons.lang3.StringUtils;

public class TokenizeCardResponse extends CommonResponse<TokenizeCardResponse.CardToken> {

    public TokenizeCardResponse(CardToken cardToken) {
        super(true, 0, "Save the TokenRequest Successfully");
        setData(cardToken);
    }

    public TokenizeCardResponse(CreditCardToken usaepayToken){
        super(true, 0, "Save the TokenRequest Successfully");
        setData(
                new CardToken(
                        usaepayToken.getCardRef(),
                        usaepayToken.getCardExpiration().substring(5,7) + usaepayToken.getCardExpiration().substring(2,4),
                        usaepayToken.getCardNumber(),
                        usaepayToken.getCardType()
                )
        );
    }

    public TokenizeCardResponse(CreditCard paypalToken){
        super(true, 0, "Save the TokenRequest Successfully");
        setData(
                new CardToken(
                        paypalToken.getId(),
                        // leading zero if needed
                        String.format("%02d%s", paypalToken.getExpireMonth(), String.valueOf(paypalToken.getExpireYear()).substring(2, 4)),
                        paypalToken.getNumber(),
                        paypalToken.getType())
        );
    }

    public TokenizeCardResponse(Integer statusCode, String description) {
        super(false, statusCode, description);
    }

    public static class CardToken {
        private String cardToken;
        private String gatewayTokenId;
        private String cardExpiration;
        private String cardNumber;
        private String cardType;
        private String gatewayCustomerId;

        public CardToken(String cardToken, String cardExpiration, String cardNumber, String cardType) {
            this.cardToken = cardToken;
            this.cardExpiration = cardExpiration;
            if (cardNumber != null) {
                if (cardNumber.length() > 4) {
                    // Obscure card number
                    this.cardNumber = StringUtils.leftPad(cardNumber.substring(cardNumber.length() - 4, cardNumber.length()), cardNumber.length(), "*");
                } else {
                    this.cardNumber = cardNumber;
                }
            }
            this.cardType = cardType;
        }

        public CardToken(String cardToken, String gatewayTokenId, String cardExpiration, String cardNumber, String cardType, String gatewayCustomerId) {
            this.cardToken = cardToken;
            this.gatewayTokenId = gatewayTokenId;
            this.cardExpiration = cardExpiration;
            if (cardNumber != null) {
                if (cardNumber.length() > 4) {
                    // Obscure card number
                    this.cardNumber = StringUtils.leftPad(cardNumber.substring(cardNumber.length() - 4, cardNumber.length()), cardNumber.length(), "*");
                } else {
                    this.cardNumber = cardNumber;
                }
            }
            this.cardType = cardType;
            this.gatewayCustomerId = gatewayCustomerId;
        }
    }
}
