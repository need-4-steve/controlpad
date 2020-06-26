package com.controlpad.pay_fac.util;

import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.payment_info.TokenRequest;
import com.controlpad.pay_fac.tokenization.TokenizeCardResponse;
import com.controlpad.pay_fac.transaction.TransactionResponse;
import com.controlpad.payman_common.transaction.Card;
import com.controlpad.payman_common.transaction.CardType;
import com.controlpad.payman_common.transaction.TransactionResult;
import org.apache.commons.lang3.StringUtils;

import java.util.Calendar;

public class CardValidationUtil {

    /**
     * Check:
     * TokenRequest Number: (1) all numbers (2) number of digits between 13 to 16
     * Expiration Date: (1) all numbers
     *                  (2) four digits? -> validate? True: 1019->10-2019   False:14190>14-2019
     *                  (3) three digits? -> validate? True:119->01-2019    False:019->00-2019
     *                  (4) expired? compare with current month/year.
     */
    public static TokenizeCardResponse cardValidator(TokenRequest tokenRequest){
        String cardNum = tokenRequest.getCard().getNumber();

        if(cardNum.length() > 16 || cardNum.length() < 13){
            return new TokenizeCardResponse(2, "Error: TokenRequest Number is not not between 13 and 16 digits");
        }

        if(!isValidCardNumber(cardNum)){
            return new TokenizeCardResponse(3, "Error: Invalid TokenRequest Number");
        }

        int expMonth = tokenRequest.getCard().getMonth(), expYear = tokenRequest.getCard().getYear();

        if(expMonth == -1 || expYear == -1){
            return new TokenizeCardResponse(5, "Error: Invalid Expiration Date");
        }

        if(expMonth < 1 || expMonth > 12){
            return new TokenizeCardResponse(6, "Error: Invalid Expiration Date");
        }
        Calendar calendar = Calendar.getInstance();
        Integer currentMonth = calendar.get(Calendar.MONTH) + 1; //Calendar month start from 0 to 11
        Integer currentYear = calendar.get(Calendar.YEAR);

        if(currentYear > expYear || (currentYear.equals(expYear) && currentMonth > expMonth)){
            return new TokenizeCardResponse(7, "Error: TokenRequest expired");
        }

        return null;
    }

    public static void validateCard(Card card){
        if(!CardValidationUtil.isValidCardNumber(card.getNumber())){
            throw new ResponseException(new TransactionResponse(TransactionResult.Invalid_Card_Number));
        }
        if(!CardValidationUtil.isValidExpireDate(card.getMonth(), card.getYear())){
            throw new ResponseException(new TransactionResponse(TransactionResult.Invalid_Expiration_Date));
        }
        if (CardValidationUtil.isCardExpired(card.getMonth(), card.getYear())) {
            throw new ResponseException(new TransactionResponse(TransactionResult.Card_Expired));
        }
        if(!CardValidationUtil.isValidType(card.getType())){
            throw new ResponseException(new TransactionResponse(TransactionResult.Card_Not_Supported));
        }
        if(!CardValidationUtil.isValidCode(card.getCode())){
            throw new ResponseException(new TransactionResponse(TransactionResult.Invalid_Code));
        }
    }

    public static boolean isValidCardNumber(String cardNum){
        if(cardNum == null || cardNum.contains(" ") || !StringUtils.isNumeric(cardNum) || cardNum.length() < 13 || cardNum.length() > 16){
            return false;
        }
        int length = cardNum.length();
        int checkSum = Integer.valueOf(cardNum.substring(length- 1, length));
        int sum = 0;
        boolean isOdd = true;
        for(int i = length - 2; i >= 0; --i){
            if(isOdd){
                int res = 2 * Integer.valueOf(cardNum.substring(i,i+1));
                sum += res > 9 ? res - 9 : res;
            }else{
                sum += Integer.valueOf(cardNum.substring(i,i+1));
            }
            isOdd = !isOdd;
        }
        return (sum + checkSum)%10 == 0;
    }

    public static boolean isValidExpireDate(int expMonth, int expYear){
        if(expMonth < 1 || expMonth > 12){
            return false;
        }
        return true;
    }

    public static boolean isCardExpired(int month, int year) {
        Calendar calendar = Calendar.getInstance();
        Integer currentMonth = calendar.get(Calendar.MONTH) + 1; //Calendar month start from 0 to 11
        Integer currentYear = calendar.get(Calendar.YEAR);
        return currentYear > year || (currentYear.equals(year) && currentMonth > month);
    }

    public static boolean isValidType(CardType cardType){
        return cardType != CardType.UNKNOWN;
    }

    public static boolean isValidCode(String code){
        return !StringUtils.isBlank(code) && code.length() >=3 && code.length() <= 4;
    }
}
