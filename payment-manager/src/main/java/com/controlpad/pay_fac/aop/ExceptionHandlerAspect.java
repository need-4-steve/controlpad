package com.controlpad.pay_fac.aop;

import org.aspectj.lang.ProceedingJoinPoint;
import org.aspectj.lang.annotation.Around;
import org.aspectj.lang.annotation.Aspect;
import org.aspectj.lang.annotation.Pointcut;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Component;

@Aspect
@Component
public class ExceptionHandlerAspect {
    private static final Logger logger = LoggerFactory.getLogger(ExceptionHandlerAspect.class);

    @Pointcut("execution(* com.controlpad.pay_fac.*.*.scheduledRefresh*())")
    public void pointcut(){}

    @Around("pointcut()")
    public void exceptionHandlerAsp(ProceedingJoinPoint p){
        try{
            p.proceed();
        }catch(Throwable t){
            logger.error(t.getMessage(), t);
        }
    }

}
