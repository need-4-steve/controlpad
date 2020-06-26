package com.controlpad.payman_processor.cron;

import com.controlpad.payman_processor.payout.file.PayoutFileWriter;
import org.slf4j.Logger;
import org.springframework.stereotype.Component;

import java.io.IOException;

@Component
public class CronExceptionHandler {
    public void handle(Logger logger, PayoutFileWriter writer, String clientId, Exception e, String functionName){
        logger.error(String.format("Fail to process " + functionName + " for client: %s\n", clientId), e);
        if(writer != null){
            try{
                writer.close();
            }catch (IOException closeException){
                logger.error("Payout file for" + functionName + "close error!", closeException);
            }
            writer.delete();
        }
    }
}