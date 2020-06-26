package com.controlpad.pay_fac.util;


import com.controlpad.pay_fac.exceptions.ResponseException;
import org.springframework.http.HttpStatus;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;

public class ParamValidations {

    private static final int MAX_COUNT = 100;


    public static void checkPageCount(Integer count, Long page) {
        if (count < 1) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "count must be greater than 0");
        }
        if (page < 1) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "page must be greater than 0");
        }
        if (count > MAX_COUNT) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Max count is " + MAX_COUNT);
        }
    }

    public static void validatePastBirthDate(String dateString, String paramName) {
        try {
            Calendar today = Calendar.getInstance();
            today.set(Calendar.HOUR_OF_DAY, 0);
            today.set(Calendar.MINUTE, 0);
            today.set(Calendar.SECOND, 0);
            today.set(Calendar.MILLISECOND, 0);
            today.add(Calendar.DAY_OF_MONTH, -1); // Timezone cheap workaround

            SimpleDateFormat sdf = new SimpleDateFormat("yyyyMMdd");
            sdf.setLenient(false);
            Date date = sdf.parse(dateString);

            if (!date.before(today.getTime())) {
                throw new ResponseException(HttpStatus.BAD_REQUEST, String.format("%s should be a valid date in the past", paramName));
            }
        } catch (ParseException | IllegalArgumentException e) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, String.format("%s should be in the format yyyyMMdd", paramName));
        }

    }
}