package com.controlpad.pay_fac;

import com.controlpad.pay_fac.api_key.APIKeyUtil;
import com.controlpad.pay_fac.auth.AuthUtil;
import com.controlpad.pay_fac.client.ClientConfigUtil;
import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import com.controlpad.pay_fac.interceptor.AuthInterceptor;
import com.controlpad.payman_common.serialization.DateTimeGsonAdapter;
import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import com.google.gson.reflect.TypeToken;
import org.joda.time.DateTime;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.ComponentScan;
import org.springframework.context.annotation.Configuration;
import org.springframework.context.annotation.EnableAspectJAutoProxy;
import org.springframework.core.task.SimpleAsyncTaskExecutor;
import org.springframework.format.FormatterRegistry;
import org.springframework.http.converter.HttpMessageConverter;
import org.springframework.http.converter.json.GsonHttpMessageConverter;
import org.springframework.scheduling.TaskScheduler;
import org.springframework.scheduling.annotation.EnableAsync;
import org.springframework.scheduling.annotation.EnableScheduling;
import org.springframework.scheduling.concurrent.ThreadPoolTaskScheduler;
import org.springframework.web.servlet.config.annotation.*;

import java.util.List;
import java.util.concurrent.Executor;


@Configuration
@ComponentScan("com.controlpad.pay_fac")
@EnableWebMvc
@EnableAspectJAutoProxy
@EnableAsync
@EnableScheduling
public class AppConfig extends WebMvcConfigurerAdapter {

    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    ClientConfigUtil clientConfigUtil;
    @Autowired
    APIKeyUtil apiKeyUtil;
    @Autowired
    AuthUtil authUtil;

    @Override
    public void configureMessageConverters(List<HttpMessageConverter<?>> converters) {
        GsonHttpMessageConverter msgConverter = new GsonHttpMessageConverter();
        Gson gson = new GsonBuilder()
                .registerTypeAdapter(new TypeToken<DateTime>(){}.getType(), new DateTimeGsonAdapter())
                .serializeNulls().create();
        msgConverter.setGson(gson);
        converters.add(msgConverter);
    }

    @Override
    public void addInterceptors(InterceptorRegistry registry) {
        registry.addInterceptor(new AuthInterceptor(sqlSessionUtil, clientConfigUtil, authUtil, apiKeyUtil));
    }

    @Override
    public void addResourceHandlers(ResourceHandlerRegistry registry) {
        registry.addResourceHandler("/resources/**").addResourceLocations("/resources/");
    }

    @Override
    public void configurePathMatch(PathMatchConfigurer configurer) {
        super.configurePathMatch(configurer);
    }

    @Override
    public void configureDefaultServletHandling(DefaultServletHandlerConfigurer configurer) {
        configurer.enable();
    }

    @Override
    public void addFormatters(FormatterRegistry registry) {
        registry.addConverter(new DateTimeConverter());
    }

    @Bean
    public Executor taskExecutor() {
        return new SimpleAsyncTaskExecutor();
    }

    @Bean
    public TaskScheduler taskScheduler() {
        return new ThreadPoolTaskScheduler();
    }

}
