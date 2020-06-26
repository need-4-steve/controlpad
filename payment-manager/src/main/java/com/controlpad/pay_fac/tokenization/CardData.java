package com.controlpad.pay_fac.tokenization;

import com.controlpad.payman_common.validation.AlwaysCheck;
import org.hibernate.validator.constraints.NotBlank;

public class CardData {

    @NotBlank(message = "cardZip required", groups = AlwaysCheck.class)
    private String cardZip;
    @NotBlank(message = "cardCode required", groups = AlwaysCheck.class)
    private String cardCode;
    @NotBlank(message = "cardNumber required", groups = AlwaysCheck.class)
    private String cardNumber;
    @NotBlank(message = "cardAddress required", groups = AlwaysCheck.class)
    private String cardAddress;
    @NotBlank(message = "cardExpiration required", groups = AlwaysCheck.class)
    private String cardExpiration;

    public CardData() {}

    public CardData(String cardZip, String cardCode, String cardNumber, String cardAddress, String cardExpiration){
        this.cardZip = cardZip;
        this.cardCode = cardCode;
        this.cardNumber = cardNumber;
        this.cardAddress = cardAddress;
        this.cardExpiration = cardExpiration;
    }

    public String getCardZip() {
        return cardZip;
    }

    public String getCardCode() {
        return cardCode;
    }

    public String getCardNumber() {
        return cardNumber;
    }

    public String getCardAddress() {
        return cardAddress;
    }

    public String getCardExpiration() {
        return cardExpiration;
    }
}
