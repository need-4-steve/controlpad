package com.controlpad.pay_fac.datasource;

import com.controlpad.pay_fac.api_key.APIKeyMapper;
import com.controlpad.pay_fac.auth.AuthMapper;
import com.controlpad.pay_fac.report.ReportsMapper;
import com.controlpad.pay_fac.report.gateway.GatewayReportsMapper;
import com.controlpad.payman_common.account.AccountMapper;
import com.controlpad.payman_common.ach.ACHMapper;
import com.controlpad.payman_common.affiliate_charge.AffiliateChargeMapper;
import com.controlpad.payman_common.client.ClientMapper;
import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.consignment.ConsignmentMapper;
import com.controlpad.payman_common.credits.CreditsMapper;
import com.controlpad.payman_common.datasource.*;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.ewallet.EWalletMapper;
import com.controlpad.payman_common.fee.FeeMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.merchant.MerchantMapper;
import com.controlpad.payman_common.payman_user.PayManUserMapper;
import com.controlpad.payman_common.payment.PaymentMapper;
import com.controlpad.payman_common.payment_batch.PaymentBatchMapper;
import com.controlpad.payman_common.payment_file.PaymentFileMapper;
import com.controlpad.payman_common.payment_provider.PaymentProviderMapper;
import com.controlpad.payman_common.payout_job.PayoutJobMapper;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.controlpad.payman_common.transaction_charge.TransactionChargeMapper;
import com.controlpad.payman_common.transaction_debit.TransactionDebitMapper;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.controlpad.payman_common.util.GsonUtil;
import org.apache.ibatis.exceptions.ExceptionFactory;
import org.apache.ibatis.executor.ErrorContext;
import org.apache.ibatis.executor.Executor;
import org.apache.ibatis.session.Configuration;
import org.apache.ibatis.session.ExecutorType;
import org.apache.ibatis.session.SqlSession;
import org.apache.ibatis.session.TransactionIsolationLevel;
import org.apache.ibatis.session.defaults.DefaultSqlSession;
import org.apache.ibatis.transaction.Transaction;
import org.apache.ibatis.transaction.TransactionFactory;
import org.apache.ibatis.transaction.jdbc.JdbcTransactionFactory;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Component;

import java.io.File;
import java.io.FileReader;
import java.sql.SQLException;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

@Component
public class SqlSessionUtil {

    private final Logger logger = LoggerFactory.getLogger(SqlSessionUtil.class);

    private Configuration configuration;
    private TransactionFactory transactionFactory;

    private DBDataSource paymanDatasource = null;
    private Map<String, DBDataSource> clientDatasourceMap;

    public SqlSessionUtil() {
        clientDatasourceMap = new ConcurrentHashMap<>();

        try {
            Class.forName("com.mysql.jdbc.Driver");
            File sqlConfigFile = new File("sql-config.json");
            if (!sqlConfigFile.exists()) {
                sqlConfigFile = new File("/etc/opt/payman/sql-config.json");
            }
            paymanDatasource = new DBDataSource(GsonUtil.getGson().fromJson(new FileReader(sqlConfigFile), SqlConfig.class));
        } catch (Exception e) {
            logger.error("Failed to load SqlSessionUtil.", e);
            throw new RuntimeException(e);
        }

        transactionFactory = new JdbcTransactionFactory();
        configuration = new Configuration();
        configuration.setMapUnderscoreToCamelCase(true);
        configuration.getTypeHandlerRegistry().register(APIKeyConfigTypeHandler.class);
        configuration.getTypeHandlerRegistry().register(ClientConfigTypeHandler.class);
        configuration.getTypeHandlerRegistry().register(DateTimeTypeHandler.class);
        configuration.getTypeHandlerRegistry().register(FeeIdsTypeHandler.class);
        configuration.getTypeHandlerRegistry().register(TeamFeeSetMapTypeHandler.class);
        configuration.getTypeHandlerRegistry().register(PayoutScheduleTypeHandler.class);
        configuration.getTypeHandlerRegistry().register(PPKeyTypeHandler.class);
        configuration.getTypeHandlerRegistry().register(SqlConfigTypeHandler.class);
        configuration.getTypeHandlerRegistry().register(TeamConfigTypeHandler.class);
        configuration.getTypeHandlerRegistry().register(UserConfigTypeHandler.class);
        configuration.addMapper(AccountMapper.class);
        configuration.addMapper(ACHMapper.class);
        configuration.addMapper(AffiliateChargeMapper.class);
        configuration.addMapper(APIKeyMapper.class);
        configuration.addMapper(AuthMapper.class);
        configuration.addMapper(TransactionChargeMapper.class);
        configuration.addMapper(ClientMapper.class);
        configuration.addMapper(ConsignmentMapper.class);
        configuration.addMapper(CreditsMapper.class);
        configuration.addMapper(EntryMapper.class);
        configuration.addMapper(EWalletMapper.class);
        configuration.addMapper(FeeMapper.class);
        configuration.addMapper(GatewayConnectionMapper.class);
        configuration.addMapper(GatewayReportsMapper.class);
        configuration.addMapper(MerchantMapper.class);
        configuration.addMapper(PayManUserMapper.class);
        configuration.addMapper(PaymentMapper.class);
        configuration.addMapper(PaymentBatchMapper.class);
        configuration.addMapper(PaymentFileMapper.class);
        configuration.addMapper(PaymentProviderMapper.class);
        configuration.addMapper(PayoutJobMapper.class);
        configuration.addMapper(ReportsMapper.class);
        configuration.addMapper(TeamMapper.class);
        configuration.addMapper(TransactionMapper.class);
        configuration.addMapper(TransactionBatchMapper.class);
        configuration.addMapper(TransactionDebitMapper.class);
        configuration.addMapper(UserAccountMapper.class);
        configuration.addMapper(UserBalancesMapper.class);
    }

    public boolean existsDatasourceForClient(String clientId) {
        return clientDatasourceMap.containsKey(clientId);
    }

    public void addClientDatasource(ControlPadClient controlPadClient) {
        clientDatasourceMap.put(controlPadClient.getId(), new DBDataSource(controlPadClient.getSqlConfigWrite()));
    }

    public void removeClientDatasource(ControlPadClient controlPadClient) {
        clientDatasourceMap.remove(controlPadClient.getId());
    }

    public SqlSession openSession(String clientId, boolean autoCommit) {
        return openSessionFromDataSource(clientDatasourceMap.get(clientId), configuration.getDefaultExecutorType(), null, autoCommit);
    }

    public SqlSession openSession(DBDataSource dbDataSource, boolean autoCommit) {
        return openSessionFromDataSource(dbDataSource, configuration.getDefaultExecutorType(), null, autoCommit);
    }

    public SqlSession openPaymanSession(boolean autoCommit) {
        return openSessionFromDataSource(paymanDatasource, configuration.getDefaultExecutorType(), null, autoCommit);
    }

    public DBDataSource getClientDatasource(String clientId) {
        return clientDatasourceMap.get(clientId);
    }

    private SqlSession openSessionFromDataSource(DBDataSource dbDataSource, ExecutorType execType, TransactionIsolationLevel level, boolean autoCommit) {

        Transaction tx = null;
        try {
            tx = transactionFactory.newTransaction(dbDataSource, level, autoCommit);
            final Executor executor = configuration.newExecutor(tx, execType);
            return new DefaultSqlSession(configuration, executor, autoCommit);
        } catch (Exception e) {
            logger.error(e.getMessage(), e);
            closeTransaction(tx); // may have fetched a connection so lets call close()
            throw ExceptionFactory.wrapException("Error opening session.  Cause: " + e, e);
        } finally {
            ErrorContext.instance().reset();
        }
    }

    private void closeTransaction(Transaction tx) {
        if (tx != null) {
            try {
                tx.close();
            } catch (SQLException ignore) {
                // Intentionally ignore. Prefer previous error.
            }
        }
    }

    public Configuration getConfiguration() {
        return configuration;
    }

    public DBDataSource getPaymanDatasource() {
        return paymanDatasource;
    }

}