package com.controlpad.payman_processor.transaction_processing;

import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import com.controlpad.payman_processor.gateway.Gateway;
import com.controlpad.payman_processor.gateway.GatewayUtil;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;

import java.util.List;
import java.util.Map;

import static com.controlpad.payman_processor.transaction_processing.TransactionUpdateResult.*;

public class TransactionUpdateTask implements Runnable {

    private static final Logger logger = LoggerFactory.getLogger(TransactionUpdateTask.class);

    private SqlSessionUtil sqlSessionUtil;
    private GatewayUtil gatewayUtil;

    private String clientId;
    private Map<String, Boolean> processMap;

    public TransactionUpdateTask(SqlSessionUtil sqlSessionUtil, GatewayUtil gatewayUtil, String clientId, Map<String, Boolean> processMap) {
        this.sqlSessionUtil = sqlSessionUtil;
        this.gatewayUtil = gatewayUtil;
        this.clientId = clientId;
        this.processMap = processMap;
    }

    @Override
    public void run() {
        MDC.put("clientId", clientId);
        logger.info("TransactionUpdateTask starting for client: {}", clientId);
        long startTime = System.currentTimeMillis();
        int transactionCount = 0;
        // Catching exceptions because otherwise it could fail silently
        try (SqlSession session = sqlSessionUtil.openSession(clientId, false)) {
            TransactionMapper transactionMapper = session.getMapper(TransactionMapper.class);
            GatewayConnectionMapper gatewayConnectionMapper = session.getMapper(GatewayConnectionMapper.class);

            List<Long> gatewayConnectionIds = gatewayConnectionMapper.listAllIds();

            GatewayConnection gatewayConnection;
            Gateway gateway;
            for (Long gcId : gatewayConnectionIds) {
                gatewayConnection = gatewayConnectionMapper.findById(gcId);
                gateway = gatewayUtil.getGatewayApi(gatewayConnection);
                Transaction transaction = transactionMapper.findFirstTransactionForUpdate(gatewayConnection.getId());
                int result;
                while (transaction != null) {
                    result = gateway.updateTransactionStatus(session, gatewayConnection, transaction, clientId);

                    if (result == STOP || (result & ERROR) == ERROR) {
                        break;
                    } else if ((result & UPDATED) == UPDATED) {
                        transactionCount++;
                    }
                    session.commit();
                    transaction = transactionMapper.findNextTransactionForUpdate(gatewayConnection.getId(), transaction.getId());
                }
            }
        } catch (Exception e) {
            logger.error(String.format("Transaction updates task failed for client: %s", clientId), e);
        }
        processMap.put(clientId, false);
        MDC.put("transactionCount", String.valueOf(transactionCount));
        MDC.put("timeElapsed", String.valueOf(System.currentTimeMillis() - startTime));
        logger.info("TransactionUpdateTask finished.");
        MDC.remove("transactionCount");
        MDC.remove("timeElapsed");
    }

    public String getClientId() {
        return clientId;
    }

}