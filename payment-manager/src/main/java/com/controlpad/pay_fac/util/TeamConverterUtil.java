package com.controlpad.pay_fac.util;

import com.controlpad.payman_common.transaction.Payment;
import com.controlpad.payman_common.transaction.TransferPayment;

public class TeamConverterUtil {

    // TODO remove once php gets switched over on the servers
    public static String convert(String teamId) {
        if (teamId == null)
            return null;
        switch (teamId) {
            case "1":
                return "company";
            case "2":
                return "rep";
            default:
                return teamId;
        }
    }

    public static void convert(Payment payment) {
        payment.setTeamId(convert(payment.getTeamId()));
    }

    public static void convert(TransferPayment transferPayment) {
        transferPayment.setTeamId(convert(transferPayment.getTeamId()));
    }
}
