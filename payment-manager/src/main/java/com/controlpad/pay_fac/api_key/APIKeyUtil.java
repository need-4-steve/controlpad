package com.controlpad.pay_fac.api_key;

import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import org.apache.ibatis.session.SqlSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;

import java.util.List;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

@Component
public class APIKeyUtil {
    private Map<String, APIKey> apiKeyMap = new ConcurrentHashMap<>();
    private SqlSessionUtil sqlSessionUtil;

    @Autowired
    public APIKeyUtil(SqlSessionUtil sessionUtil) {
        this.sqlSessionUtil = sessionUtil;
        scheduledRefreshAPIKey();
    }


    @Scheduled(fixedDelay = 900000L, initialDelay = 900000L)
    public void scheduledRefreshAPIKey(){
        SqlSession session = sqlSessionUtil.openPaymanSession(true);
        List<APIKey> apiKeyList = session.getMapper(APIKeyMapper.class).listValidatedAPIKeys();
        session.close();
        for (APIKey apiKey: apiKeyList) {
            apiKeyMap.put(apiKey.getId(), apiKey);
        }
        apiKeyMap.forEach((key, apiKey) -> {
            if (!apiKeyList.contains(apiKey)) {
                apiKeyMap.remove(key);
            }
        });
    }

    public Map<String, APIKey> getApiKeyMap(){
        return apiKeyMap;
    }
}
