package com.controlpad.pay_fac.payment_info;

import com.controlpad.pay_fac.transaction.DecodeUtil;
import com.controlpad.pay_fac.validation.CardPaymentValidate;
import com.controlpad.payman_common.address.Address;
import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.transaction.Card;
import com.controlpad.payman_common.transaction.CardType;
import com.controlpad.payman_common.transaction.Payment;
import com.controlpad.payman_common.util.GsonUtil;
import com.controlpad.payman_common.validation.AlwaysCheck;

import javax.validation.Valid;
import java.math.BigDecimal;
import java.util.List;

@CardPaymentValidate(groups = AlwaysCheck.class)
public class CardPayment extends Payment {

    private String cardNumber;
    private String cardExpiration;
    private String cardCode;
    private String cardAddress;
    private String cardZip;

    @Valid
    private Card card;
    private Long gatewayConnectionId;

    private String cardMagstripe;
    private String cardEncMagstripe;

    private String gatewayCustomerId;
    private String cardToken;
    private String cardNonce;

    public CardPayment() {}

    public CardPayment(String payerUserId, String payeeUserId, String teamId, String name,
                       BigDecimal tax, BigDecimal shipping, BigDecimal total, String description,
                       String cardNumber, String cardCode, int cardYear, int cardMonth) {
        super(payerUserId, payeeUserId, teamId, name, tax, shipping, total, description);
        this.card = new Card();
        this.card.setNumber(cardNumber);
        this.card.setCode(cardCode);
        this.card.setYear(cardYear);
        this.card.setMonth(cardMonth);
    }

    public CardPayment(String payerUserId, String payeeUserId, String teamId, String name,
                       BigDecimal tax, BigDecimal subtotal, String poNumber, String description,
                       Card card, Address billingAddress) {
        super(payerUserId, payeeUserId, teamId, name, tax, subtotal, poNumber, description);
        this.card = card;
        this.setBillingAddress(billingAddress);
    }

    public CardPayment(String payerUserId, String payeeUserId, String teamId, String name,
                       BigDecimal tax, BigDecimal subtotal, String poNumber, String description,
                       String cardZip, String cardCode, String cardAddress, String cardMagstripe) {
        super(payerUserId, payeeUserId, teamId, name, tax, subtotal, poNumber, description);
        this.cardZip = cardZip;
        this.cardCode = cardCode;
        this.cardAddress = cardAddress;
        this.cardMagstripe = cardMagstripe;
    }

    public CardPayment(String payerUserId, String payeeUserId, String teamId, String name,
                       BigDecimal tax, BigDecimal subtotal, String poNumber, String description,
                       String cardZip, String cardCode, String cardNumber, String cardAddress, String cardExpiration) {
        super(payerUserId, payeeUserId, teamId, name, tax, subtotal, poNumber, description);
        this.cardZip = cardZip;
        this.cardCode = cardCode;
        this.cardNumber = cardNumber;
        this.cardAddress = cardAddress;
        this.cardExpiration = cardExpiration;
    }

    public CardPayment(String payerUserId, String payeeUserId, String teamId, String firstName, String lastName,
                       BigDecimal tax, BigDecimal shipping, BigDecimal subtotal, String poNumber, String description,
                       List<AffiliateCharge> affiliatePayouts, String cardZip, String cardNumber, String cardExpiration) {
        super(payerUserId, payeeUserId, teamId, firstName, lastName, tax, shipping, subtotal, poNumber, description, affiliatePayouts);
        this.cardZip = cardZip;
        this.cardNumber = cardNumber;
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

    public String getCardMagstripe() {
        return cardMagstripe;
    }

    public String getCardEncMagstripe() {
        return cardEncMagstripe;
    }

    @Override
    public String getName() {
        return getCardHolder();
    }

    public String getCardHolder() {
        if (card != null && card.getName() != null) {
            return card.getName();
        } else if (cardMagstripe != null || cardEncMagstripe != null) {
            String name = DecodeUtil.getName((cardMagstripe != null ? cardMagstripe : cardEncMagstripe));
            if(name == null){
                return super.getName();
            }else{
                return name;
            }
        } else {
            return super.getName();
        }
    }

    public String getGatewayCustomerId() { return gatewayCustomerId; }

    public Long getGatewayConnectionId() {
        return gatewayConnectionId;
    }

    public String getCardToken() {
        return cardToken;
    }

    public CardType getCardType() {
        if (card != null && card.getNumber() != null) {
            return CardType.detect(card.getNumber());
        } else if (cardNumber != null) {
            return CardType.detect(cardNumber);
        }
        return CardType.UNKNOWN;
    }

    public String getCardNonce(){
        return cardNonce;
    }

    public Card getCard(){
        return card;
    }

    @Override
    public String toString(){
        return GsonUtil.getGson().toJson(this);
    }
}
