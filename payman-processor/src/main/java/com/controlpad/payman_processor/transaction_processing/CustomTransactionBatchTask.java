package com.controlpad.payman_processor.transaction_processing;


import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionType;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.joda.time.DateTimeZone;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.List;
import java.util.Map;

public class CustomTransactionBatchTask implements Runnable {

    private static final Logger logger = LoggerFactory.getLogger(CustomTransactionBatchTask.class);
    private static final long BATCH_TIME_BUFFER = 1000 * 60 * 60 * 6; // 6 hours

    private SqlSessionUtil sqlSessionUtil;

    private String clientId;
    private String endDateTime;
    private GatewayConnectionType gatewayConnectionType;
    private Map<String, Boolean> processMap;

    public CustomTransactionBatchTask(SqlSessionUtil sqlSessionUtil, GatewayConnectionType gatewayConnectionType, String clientId, Map<String, Boolean> processMap) {
        this.sqlSessionUtil = sqlSessionUtil;
        this.clientId = clientId;
        this.endDateTime = DateTime.now(DateTimeZone.UTC).minus(BATCH_TIME_BUFFER).toString("YYYY-MM-dd HH:mm:ss");
        this.processMap = processMap;
        this.gatewayConnectionType = gatewayConnectionType;
    }

    @Override
    public void run() {
        logger.info("CustomTransactionBatchTask starting for client: {}", clientId);
        try (SqlSession session = sqlSessionUtil.openSession(clientId, false)) {
            // List all gateways by type for client
            TransactionMapper transactionMapper = session.getMapper(TransactionMapper.class);
            GatewayConnectionMapper gatewayConnectionMapper = session.getMapper(GatewayConnectionMapper.class);
            TransactionBatchMapper transactionBatchMapper = session.getMapper(TransactionBatchMapper.class);
            List<Long> customIdList = gatewayConnectionMapper.searchId(null, null, null, null, null, gatewayConnectionType.slug, null);
            GatewayConnection gatewayConnection;
            for (Long gcId : customIdList) {
                gatewayConnection = gatewayConnectionMapper.findById(gcId);
                TransactionBatch transactionBatch = new TransactionBatch(gatewayConnection.getId(), null, null, 0, null, null);
                transactionBatch.setSettledAt(DateTime.now());
                transactionBatchMapper.insert(transactionBatch);
                transactionMapper.setCustomTransactionsBatch(transactionBatch.getId(), gatewayConnection.getId(), endDateTime);
                session.commit();
            }
        } catch (Exception e) {
            logger.error(String.format("CustomTransactionBatchTask failed for client: %s", clientId), e);
        }
        processMap.put(clientId, false);
    }
}
