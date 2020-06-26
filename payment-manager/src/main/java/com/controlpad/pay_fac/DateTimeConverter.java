package com.controlpad.pay_fac;

import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.springframework.core.convert.converter.Converter;

public class DateTimeConverter implements Converter<String, DateTime> {

    private DateTimeFormatter dateTimeFormatter;

    public DateTimeConverter() {
        dateTimeFormatter = DateTimeFormat.forPattern("yyyy-MM-dd HH:mm:ss");
    }

    @Override
    public DateTime convert(String dateTime) {
        return dateTimeFormatter.parseDateTime(dateTime);
    }
}
