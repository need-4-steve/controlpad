package com.controlpad.payman_processor.client;

import com.controlpad.payman_common.client.ClientMapper;
import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.client.Features;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import org.apache.ibatis.session.SqlSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;

import java.util.List;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

@Component
public class ClientConfigUtil {

    private SqlSessionUtil sqlSessionUtil;

    private Map<String, ControlPadClient> clientMap = new ConcurrentHashMap<>();

    @Autowired
    public ClientConfigUtil(SqlSessionUtil sqlSessionUtil) {
        this.sqlSessionUtil = sqlSessionUtil;
        updateClientList();
    }

    @Scheduled(fixedDelay = 900000L)
    public void updateClientList() {
        SqlSession session = sqlSessionUtil.openPaymanSession(true);
        List<ControlPadClient> clientList = session.getMapper(ClientMapper.class).findAllClients();
        for (ControlPadClient client: clientList) {
            clientMap.put(client.getId(), client);
        }
        clientMap.forEach((key, client) -> {
            if (!clientList.contains(client)) {
                clientMap.remove(key);
                sqlSessionUtil.removeClientDatasource(client);
            } else {
                sqlSessionUtil.addClientDatasource(client);
            }
        });
        session.close();
    }

    public Map<String, ControlPadClient> getClientMap() {
        return clientMap;
    }

    public Features getClientFeatures(String clientId) {
        return clientMap.get(clientId).getConfig().getFeatures();
    }

}