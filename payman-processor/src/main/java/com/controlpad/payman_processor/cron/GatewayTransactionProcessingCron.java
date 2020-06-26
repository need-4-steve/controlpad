package com.controlpad.payman_processor.cron;

import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionType;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.controlpad.payman_processor.client.ClientConfigUtil;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import com.controlpad.payman_processor.gateway.Gateway;
import com.controlpad.payman_processor.gateway.GatewayUtil;
import com.controlpad.payman_processor.transaction_processing.CustomTransactionBatchTask;
import com.controlpad.payman_processor.transaction_processing.TransactionProcessingTask;
import com.controlpad.payman_processor.transaction_processing.TransactionUpdateTask;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.scheduling.concurrent.ThreadPoolTaskExecutor;
import org.springframework.stereotype.Component;

import java.util.List;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;


@Component
public class GatewayTransactionProcessingCron {

    private static final Logger logger = LoggerFactory.getLogger(GatewayTransactionProcessingCron.class);

    @Autowired
    ClientConfigUtil clientConfigUtil;
    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    GatewayUtil gatewayUtil;

    @Autowired
    ThreadPoolTaskExecutor transactionUpdateExecutor;
    @Autowired
    ThreadPoolTaskExecutor transactionProcessExecutor;

    // Cheap hack to filter duplicate tasks
    private Map<String, Boolean> transactionUpdateClients = new ConcurrentHashMap<>();
    private Map<String, Boolean> transactionProcessClients = new ConcurrentHashMap<>();
    private Map<String, Boolean> paypalBatchProcessClients = new ConcurrentHashMap<>();
    private Map<String, Boolean> mockBatchProcessClients = new ConcurrentHashMap<>();

    @Scheduled(fixedRate = 900000L)
    public void runScheduledTransactionBatchCheckers(){
        launchTransactionBatchCheckers();
    }

    public void launchTransactionBatchCheckers() {
        logger.info("launchTransactionBatchCheckers called");
        clientConfigUtil.getClientMap().forEach((key, client) -> {
            if (!transactionUpdateClients.containsKey(client.getId()) || !transactionUpdateClients.get(client.getId())) {
                transactionUpdateExecutor.submit(new TransactionUpdateTask(sqlSessionUtil, gatewayUtil, client.getId(), transactionUpdateClients));
                transactionUpdateClients.put(client.getId(), true);
            }
        });
    }

    @Scheduled(fixedRate = 900000L)
    public void runScheduledTransactionProcesses(){
        launchTransactionProcesses();
    }

    public void launchTransactionProcesses() {
        logger.info("launchTransactionProcesses called");
        clientConfigUtil.getClientMap().forEach((key, client) -> {
            if (!transactionProcessClients.containsKey(client.getId()) || !transactionProcessClients.get(client.getId())) {
                transactionProcessExecutor.submit(new TransactionProcessingTask(sqlSessionUtil, gatewayUtil, client.getId(), transactionProcessClients));
                transactionProcessClients.put(client.getId(), true);
            }
        });
    }

    @Scheduled(cron = "0 0 0/12 * * ?")
    public void batchPaypalTransactions() {
        clientConfigUtil.getClientMap().forEach((key, client) -> {
            if (!paypalBatchProcessClients.containsKey(client.getId()) || !paypalBatchProcessClients.get(client.getId())) {
                transactionProcessExecutor.submit(new CustomTransactionBatchTask(sqlSessionUtil, GatewayConnectionType.PAYPAL, client.getId(), paypalBatchProcessClients));
                paypalBatchProcessClients.put(client.getId(), true);
            } else {
                logger.error("PaypalBatchProcess tried to execute while already running for client: " + client.getId());
            }
        });
    }

    @Scheduled(cron = "0 0 0/6 * * ?")
    public void batchMockTransactions() {
        clientConfigUtil.getClientMap().forEach((key, client) -> {
            if (!mockBatchProcessClients.containsKey(client.getId()) || !mockBatchProcessClients.get(client.getId())) {
                if (!client.getSandbox()) {
                    // Don't operate on non sandbox clients
                    return;
                }
                transactionProcessExecutor.submit(new CustomTransactionBatchTask(sqlSessionUtil, GatewayConnectionType.MOCK, client.getId(), mockBatchProcessClients));
                mockBatchProcessClients.put(client.getId(), true);
            } else {
                logger.error("MockBatchProcess tried to execute while already running for client: " + client.getId());
            }
        });
    }

    @Scheduled(fixedDelay = 900000L, initialDelay = 60000L)
    public void checkTransactionBatchStatus() {
        clientConfigUtil.getClientMap().forEach((key, client) -> {
            try(SqlSession clientSession = sqlSessionUtil.openSession(key, true)) {
                TransactionBatchMapper transactionBatchMapper = clientSession.getMapper(TransactionBatchMapper.class);
                GatewayConnectionMapper gatewayConnectionMapper = clientSession.getMapper(GatewayConnectionMapper.class);

                List<Long> gatewayConnectionIds = gatewayConnectionMapper.listAllIds();
                GatewayConnection gatewayConnection;
                List<TransactionBatch> batches;
                for(Long gatewayConnectionId : gatewayConnectionIds) {
                    gatewayConnection = gatewayConnectionMapper.findById(gatewayConnectionId);
                    Gateway gateway = gatewayUtil.getGatewayApi(gatewayConnection);
                    batches = transactionBatchMapper.listNotSettledForConnection(gatewayConnectionId);
                    for(TransactionBatch transactionBatch : batches) {
                        if (gateway.checkTransactionBatch(clientSession, gatewayConnection, transactionBatch)) {
                            TransactionBatch batchStats = transactionBatchMapper.calculateTransactionStats(transactionBatch.getId());
                            batchStats.setGatewayTransactionCount(transactionBatch.getGatewayTransactionCount());
                            batchStats.setGatewayNetAmount(transactionBatch.getGatewayNetAmount());
                            transactionBatchMapper.updateStats(batchStats);
                            transactionBatchMapper.markSettledForId(transactionBatch.getId(), transactionBatch.getSettledAt());
                        }
                    }
                }
            } catch (Exception e) {
                logger.error("Failed to check transaction batches for Client: " + key, e);
            }
        });
    }
}