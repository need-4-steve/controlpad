package com.controlpad.pay_fac.test;


import org.springframework.stereotype.Component;

import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.concurrent.TimeUnit;

@Component
public class TimeUtil {
    private SimpleDateFormat simpleDateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");

    /**
     * Salt Lake City: "MST"
     */
    public String getStartTime(){
        return simpleDateFormat.format(new Date(System.currentTimeMillis()));
    }

    public String getEndTimeAfterOneSecond(){
        try{
            TimeUnit.SECONDS.sleep(1);
            return simpleDateFormat.format(new Date(System.currentTimeMillis()));
        } catch(Exception e){
            throw new RuntimeException(e);
        }

    }
}
