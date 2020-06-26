/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.client;

import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import com.controlpad.payman_common.client.ClientMapper;
import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.client.Features;
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
    private Map<String, ControlPadClient> clientOrgMap = new ConcurrentHashMap<>();

    @Autowired
    public ClientConfigUtil(SqlSessionUtil sessionUtil) {
        this.sqlSessionUtil = sessionUtil;
        scheduledRefreshClientMap();
    }

    @Scheduled(fixedDelay = 900000L, initialDelay = 900000L)
    public void scheduledRefreshClientMap(){
        SqlSession session = sqlSessionUtil.openPaymanSession(true);
        List<ControlPadClient> clientList = session.getMapper(ClientMapper.class).findAllClients();
        session.close();
        for (ControlPadClient client: clientList) {
            clientMap.put(client.getId(), client);
            if (client.getOrgId() != null) {
                clientOrgMap.put(client.getOrgId(), client);
            }
        }
        clientMap.forEach((key, client) -> {
            if (!clientList.contains(client)) {
                clientMap.remove(key);
                clientOrgMap.remove(client.getOrgId());
                sqlSessionUtil.removeClientDatasource(client);
            } else {
                sqlSessionUtil.addClientDatasource(client);
            }
        });
    }

    public Map<String, ControlPadClient> getClientMap() {
        return clientMap;
    }

    public ControlPadClient getClientByOrgId(String orgId) {
        if (clientOrgMap.containsKey(orgId)) {
            return clientOrgMap.get(orgId);
        }
        return null;
    }

    public String getClientName(String clientId) {
        if (clientId == null) {
            return null;
        }
        if (clientMap.containsKey(clientId)) {
            return clientMap.get(clientId).getName();
        }
        return null;
    }

    public Features getClientFeatures(String clientId) {
        return clientMap.get(clientId).getConfig().getFeatures();
    }
}