package com.controlpad.payman_common.validation;

import com.controlpad.payman_common.transaction.Card;
import com.controlpad.payman_common.transaction.CardType;
import org.apache.commons.lang3.StringUtils;

import javax.validation.ConstraintValidator;
import javax.validation.ConstraintValidatorContext;
import java.util.Calendar;

public class CardValidator implements ConstraintValidator<CardValidate, Card> {
    @Override
    public void initialize(CardValidate constraintAnnotation) {

    }

    @Override
    public boolean isValid(Card card, ConstraintValidatorContext context) {
        boolean valid = true;
        if (card.getToken() != null || card.getNonce() != null || card.getMagstripe() != null
                || card.getEncMagstripe() != null) {
            return true;
        }
        if (StringUtils.isBlank(card.getNumber())) {
            context.buildConstraintViolationWithTemplate("card.number required").addConstraintViolation();
            valid = false;
        } else if(!isValidCardNumber(card.getNumber())) {
            context.buildConstraintViolationWithTemplate("card.number invalid").addConstraintViolation();
            return false;
        }
        if (card.getYear() == null) {
            context.buildConstraintViolationWithTemplate("card.year required").addConstraintViolation();
            valid = false;
        }
        if (card.getMonth() == null) {
            context.buildConstraintViolationWithTemplate("card.month required").addConstraintViolation();
            valid = false;
        } else if (!isValidExpireMonth(card.getMonth())) {
            context.buildConstraintViolationWithTemplate("card.month should be 1-12").addConstraintViolation();
            valid = false;
        } else if (card.getYear() != null && isCardExpired(card.getMonth(), card.getYear())) {
            context.buildConstraintViolationWithTemplate("card expired").addConstraintViolation();
            valid = false;
        }
        if (!isValidCode(card.getCode())) {
            context.buildConstraintViolationWithTemplate("card.code invalid length").addConstraintViolation();
            valid = false;
        }

        return valid;
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

    public static boolean isValidExpireMonth(int expMonth){
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

    public static boolean isValidCode(String code){
        return StringUtils.isBlank(code) || code.length() >= 3 && code.length() <= 4;
    }
}
