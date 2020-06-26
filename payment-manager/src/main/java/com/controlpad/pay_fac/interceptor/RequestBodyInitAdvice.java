package com.controlpad.pay_fac.interceptor;

import com.controlpad.payman_common.common.RequestBodyInit;
import org.springframework.core.MethodParameter;
import org.springframework.http.HttpInputMessage;
import org.springframework.http.converter.HttpMessageConverter;
import org.springframework.web.bind.annotation.RestControllerAdvice;
import org.springframework.web.servlet.mvc.method.annotation.RequestBodyAdviceAdapter;

import java.lang.reflect.Type;

@RestControllerAdvice
public class RequestBodyInitAdvice extends RequestBodyAdviceAdapter {

    @Override
    public boolean supports(MethodParameter methodParameter, Type targetType, Class<? extends HttpMessageConverter<?>> converterType) {
        // Check that request body class implements RequestBodyInit interface
        return RequestBodyInit.class.isAssignableFrom(methodParameter.getParameterType());
    }

    @Override
    public Object afterBodyRead(Object body, HttpInputMessage inputMessage, MethodParameter parameter, Type targetType, Class<? extends HttpMessageConverter<?>> converterType) {
        ((RequestBodyInit)body).initRequestBody();
        return body;
    }
}
