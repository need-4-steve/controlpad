package com.controlpad.payman_common.util;


import java.math.BigDecimal;
import java.math.RoundingMode;

public class MoneyUtil {

    //TODO make not ugly
    //TODO research allowed account number formats
    /****************************************************************************
     * Check the specified account number for invalid data
     * Use first 17 characters of account number
     * Omit spaces
     *
     *
     * @param number
     *            The account number to be checked
     *
     * @return The cleaned string or null if string is invalid
     ***************************************************************************/
    public static String formatAccountNumber(String number) {
        String reply = "";
        Integer length = 17;

        try {
            number = number.replaceAll("\\s+", "");
            if (number.length() < length)
                length = number.length();

            number = number.substring(0, length);
            for (int i = 0; i < number.length(); i++) {
                Character temp = number.charAt(i);
                if (!Character.isLetterOrDigit(temp) && temp.compareTo('-') != 0)
                    return null;

                reply += number.charAt(i);
            }
        } catch (Exception ex) {
            return null;
        }

        return reply;
    }

    /****************************************************************************
     * Check the specified routing number for correctness
     *
     * @param number The routing number to be validated
     *
     * The following conditions must hold:
     * 3 (d_1 + d_4 + d_7) + 7 (d_2 + d_5 + d_8) + (d_3 + d_6 + d_9) % mod 10 = 0
     *
     * The following formula can be used to generate the ninth digit in the checksum:
     * d_9 = 7 (d_1 + d_4 + d_7) + 3 (d_2 + d_5 + d_8) + 9 (d_3 + d_6) % mod 10.
     *
     * @return The true if the given number is valid, false otherwise
     * **************************************************************************/
    public static Boolean isRoutingNumberValid(String number) {
        boolean isValid = false;
        int calculation;
        int[] nArray = new int[9];

        try {
            for (int i = 0; i < 9; i++) {
                char temp = number.charAt(i);
                if (!Character.isDigit(temp))
                    return false;

                nArray[i] = Character.getNumericValue(temp);
            }

            if (number.length() == 9) {
                calculation = (3 * (nArray[0] + nArray[3] + nArray[6]) + 7 * (nArray[1] + nArray[4] + nArray[7]) + (nArray[2] + nArray[5] + nArray[8])) % 10;
                if (calculation == 0) {
                    calculation = (7 * (nArray[0] + nArray[3] + nArray[6]) + 3 * (nArray[1] + nArray[4] + nArray[7]) + 9 * (nArray[2] + nArray[5])) % 10;
                    if (calculation == nArray[8]) {
                        isValid = true;
                    }
                }
            }
        } catch (Exception ex) {
            isValid = false;
        }

        return isValid;
    }

    public static int convertToCents(BigDecimal money) {
        return money.multiply(BigDecimal.valueOf(100D)).toBigInteger().intValue();
    }

    public static BigDecimal convertFromCents(long cents) {
        return convertFromCents((double)cents);
    }

    public static BigDecimal convertFromCents(double cents) {
        return BigDecimal.valueOf(cents).divide(BigDecimal.valueOf(100D), 5 , RoundingMode.HALF_UP);
    }

    public static String formatMoney(double money) {
        return String.format("%.2f", money);
    }

}