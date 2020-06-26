package com.controlpad.payman_common.transaction;


import java.util.regex.Pattern;

public enum CardType {
    UNKNOWN,
    VISA("^4[0-9]{12}(?:[0-9]{3})?$", "visa"),
    MASTERCARD("^5[1-5][0-9]{14}$", "mastercard"),
    AMERICAN_EXPRESS("^3[47][0-9]{13}$", "amex"),
    DINERS_CLUB("^3(?:0[0-5]|[68][0-9])[0-9]{11}$", "diners"),
    DISCOVER("^6(?:011|5[0-9]{2})[0-9]{12}$", "discover"),
    JCB("^(?:2131|1800|35\\d{3})\\d{11}$", "jcb");

    private Pattern pattern;
    private String slug;

    CardType() {
        this.pattern = null;
        this.slug = "";
    }

    CardType(String pattern, String slug) {
        this.pattern = Pattern.compile(pattern);
        this.slug = slug;
    }

    public static CardType detect(String cardNumber) {
        if (cardNumber == null) return UNKNOWN;

        for (CardType cardType : CardType.values()) {
            if (null == cardType.pattern) continue;
            if (cardType.pattern.matcher(cardNumber).matches()) return cardType;
        }

        return UNKNOWN;
    }

    public String getSlug() {
        return slug;
    }
}
