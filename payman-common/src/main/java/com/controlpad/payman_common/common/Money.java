package com.controlpad.payman_common.common;


import java.math.BigDecimal;
import java.math.RoundingMode;


public class Money extends BigDecimal {

    public Money(String amount) {
        super(new BigDecimal(amount).setScale(2, RoundingMode.HALF_UP).toString());
    }

    public Money(double amount) {
        super(new BigDecimal(amount).setScale(2, RoundingMode.HALF_UP).toString());
    }

}
