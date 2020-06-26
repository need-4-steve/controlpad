package com.controlpad.payman_processor.transaction_processing;

import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import com.controlpad.payman_processor.gateway.GatewayUtil;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.Map;

public class TransactionProcessingTask implements Runnable {

    private static final Logger logger = LoggerFactory.getLogger(TransactionUpdateTask.class);

    private SqlSessionUtil sqlSessionUtil;
    private GatewayUtil gatewayUtil;
    private String clientId;
    private Map<String, Boolean> processMap;

    public TransactionProcessingTask(SqlSessionUtil sqlSessionUtil, GatewayUtil gatewayUtil, String clientId, Map<String, Boolean> processMap) {
        this.sqlSessionUtil = sqlSessionUtil;
        this.gatewayUtil = gatewayUtil;
        this.clientId = clientId;
        this.processMap = processMap;
    }

    @Override
    public void run() {
        // Catching exceptions otherwise it will fail silently
        try (SqlSession session = sqlSessionUtil.openSession(clientId, false)) {
            new TransactionProcessHelper(session, gatewayUtil).process();
            session.commit();
        } catch (Exception e) {
            logger.error(String.format("Client: %s", clientId), e);
        }
        if (processMap != null) {
            processMap.put(clientId, false);
        }
    }

}