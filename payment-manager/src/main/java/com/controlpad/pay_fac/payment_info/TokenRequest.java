package com.controlpad.pay_fac.payment_info;

import com.controlpad.pay_fac.validation.CardTokenizationValidate;
import com.controlpad.payman_common.address.Address;
import com.controlpad.payman_common.common.RequestBodyInit;
import com.controlpad.payman_common.transaction.Card;
import com.controlpad.payman_common.transaction.CardType;
import com.controlpad.payman_common.util.NameParser;
import com.controlpad.payman_common.validation.AlwaysCheck;
import org.apache.commons.lang3.StringUtils;

import javax.validation.Valid;
import javax.validation.constraints.NotNull;


@CardTokenizationValidate(groups = AlwaysCheck.class)
public class TokenRequest implements RequestBodyInit {

    @NotNull
    @Valid
    private Card card;
    private Long gatewayConnectionId;
    private Address address;
    private String teamId;

    private String externalCustomerId;
    private String payerId;

    private String gatewayCustomerId;
    private String currentToken;
    private String gatewayTokenId;
    private String cardNonce;

    private String email;

    public TokenRequest(Card card, Address address){
        this.card = card;
        this.address = address;
    }

    public Card getCard(){
        return card;
    }

    public Address getAddress(){
        return address;
    }

    public CardType getType() {
        return CardType.detect(card.getNumber());
    }

    public String getExternalCustomerId() {
        return externalCustomerId;
    }

    public String getPayerId() {
        return payerId;
    }

    public String getExpireDate(){
        return String.format("%02d%s", card.getMonth(), String.valueOf(card.getYear()).substring(2, 4));
    }

    public String getGatewayCustomerId(){
        return gatewayCustomerId;
    }

    public String getCardNonce(){
        return cardNonce;
    }

    public String getCurrentToken(){
        return currentToken;
    }

    public Long getGatewayConnectionId() {
        return gatewayConnectionId;
    }

    public String getEmail(){
        return email;
    }

    public String getGatewayTokenId() {
        return gatewayTokenId;
    }

    public String getTeamId() {
        return teamId;
    }

    public void setGatewayCustomerId(String gatewayCustomerId) {
        this.gatewayCustomerId = gatewayCustomerId;
    }

    public void setCurrentToken(String currentToken) {
        this.currentToken = currentToken;
    }

    @Override
    public void initRequestBody() {
        // Allow putting full billing name in either first name or getName()
        if (address != null) {
            if (StringUtils.isBlank(address.getFirstName())) {
                if (StringUtils.isNotBlank(card.getName())) {
                    convertAddressName(address, card.getName());
                }
            } else if (StringUtils.isBlank(address.getLastName())) {
                convertAddressName(address, address.getFirstName());
            }
        }
    }

    private void convertAddressName(Address address, String name) {
        NameParser nameParser = new NameParser(name);
        address.setFirstName(nameParser.getFirstName());
        address.setLastName(nameParser.getLastName());
    }
}
