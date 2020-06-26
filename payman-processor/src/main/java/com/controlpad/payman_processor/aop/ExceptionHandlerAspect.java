package com.controlpad.payman_processor.aop;

import org.aspectj.lang.ProceedingJoinPoint;
import org.aspectj.lang.annotation.Around;
import org.aspectj.lang.annotation.Aspect;
import org.aspectj.lang.annotation.Pointcut;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Component;

@Component
@Aspect
public class ExceptionHandlerAspect {
    private static final Logger logger = LoggerFactory.getLogger(ExceptionHandlerAspect.class);

    @Pointcut("execution(* com.controlpad.payman_processor.cron.*.runScheduled*())")
    public void cronPointCut(){}

    @Around("cronPointCut()")
    public void exceptionHandlerAsp(ProceedingJoinPoint p){
        try{
            p.proceed();
        }catch(Throwable t){
            logger.error(t.getMessage(), t);
        }
    }
}