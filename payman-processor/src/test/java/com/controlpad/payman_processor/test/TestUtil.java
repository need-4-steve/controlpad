package com.controlpad.payman_processor.test;

import com.controlpad.payman_common.account.AccountMapper;
import com.controlpad.payman_common.client.ClientMapper;
import com.controlpad.payman_common.datasource.SqlConfig;
import com.controlpad.payman_common.fee.FeeMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionType;
import com.controlpad.payman_common.merchant.Merchant;
import com.controlpad.payman_common.merchant.MerchantMapper;
import com.controlpad.payman_common.migration.DatabaseType;
import com.controlpad.payman_common.migration.MigrationUtil;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import com.controlpad.payman_processor.util.IDUtil;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.Statement;

@Component
public class TestUtil {

    static final String PAYMAN_TESTER_USERNAME = "payman_tester";
    static final String PAYMAN_TESTER_PASSWORD = "password";

    private static final Logger logger = LoggerFactory.getLogger(TestUtil.class);

    private MockData mockData;

    private SqlConfig paymanSqlConfig;
    private MigrationUtil migrationUtil;

    @Autowired
    public TestUtil(SqlSessionUtil sqlSessionUtil, IDUtil idUtil) throws Exception {
        System.setProperty("DEBUG", "true");
        System.setProperty("LOCAL_STORAGE", "true");
        mockData = new MockData(idUtil);
        paymanSqlConfig = new SqlConfig("jdbc:mysql://localhost:3306/payman_test_processor", PAYMAN_TESTER_USERNAME, PAYMAN_TESTER_PASSWORD, DatabaseType.PAYMAN.getSlug());
        migrationUtil = new MigrationUtil();

        createDatabases();

        sqlSessionUtil.getPaymanDatasource().setSqlConfig(paymanSqlConfig);

        SqlSession apiSession = sqlSessionUtil.openPaymanSession(true);

        loadAPIDatabase(apiSession);
        apiSession.close();

        sqlSessionUtil.addClientDatasource(mockData.getTestClient());
        SqlSession clientSession = sqlSessionUtil.openSession(mockData.getTestClient().getId(), true);
        loadClientDatabase(clientSession);
        clientSession.close();
    }

    private void createDatabases() {
        System.out.println("createDatabases");

        // Clear databases
        try (Connection connection = DriverManager.getConnection("jdbc:mysql://localhost:3306/", PAYMAN_TESTER_USERNAME, PAYMAN_TESTER_PASSWORD);
             Statement statement = connection.createStatement()){
            statement.execute("DROP DATABASE IF EXISTS payman_test_processor");
            statement.execute("DROP DATABASE IF EXISTS payman_test_processor_client");
            statement.execute("CREATE DATABASE IF NOT EXISTS payman_test_processor");
            statement.execute("CREATE DATABASE IF NOT EXISTS payman_test_processor_client");
        } catch(Exception e){
            e.printStackTrace();
            System.out.println("Other errors: " + e);
        }

        if (!migrationUtil.migrate(paymanSqlConfig)) {
            throw new RuntimeException("Failed to migrate payman");
        }
        if (!migrationUtil.migrate(mockData.getTestClient().getSqlConfigWrite())) {
            throw new RuntimeException("Failed to migrate payman_client");
        }
    }

    private void loadAPIDatabase(SqlSession session) {
        ClientMapper clientMapper = session.getMapper(ClientMapper.class);
        clientMapper.insertClient(mockData.getTestClient());
    }

    private void loadClientDatabase(SqlSession session) {
        GatewayConnectionMapper gatewayConnectionMapper = session.getMapper(GatewayConnectionMapper.class);

        AccountMapper accountMapper = session.getMapper(AccountMapper.class);
        mockData.getAccounts().forEach((account) -> accountMapper.insert(account));

        // Insert teams with different settings
        TeamMapper teamMapper = session.getMapper(TeamMapper.class);
        teamMapper.insert(mockData.getTeamOne());
        teamMapper.insert(mockData.getTeamTwo());
        teamMapper.insert(mockData.getTeamThree());
        teamMapper.insert(mockData.getTeamFour());

        session.getMapper(MerchantMapper.class).insert(new Merchant("Company", "company"));

        GatewayConnection gatewayConnection = new GatewayConnection("Default Connection", null, "Some User",
                "Some Key", "Some Pin", "Public key", null, GatewayConnectionType.MOCK.slug, true, true, true, true, false, true);
        // Add new gateway record for team 1
        gatewayConnection.setTeamId(mockData.getTeamOne().getId());
        gatewayConnectionMapper.insert(gatewayConnection);
        // Add new gateway record for same gateway for team 2
        gatewayConnection.setTeamId(mockData.getTeamTwo().getId());
        gatewayConnectionMapper.insert(gatewayConnection);
        // Add new gateway record for same gateway for team 3
        gatewayConnection.setTeamId(mockData.getTeamThree().getId());
        gatewayConnectionMapper.insert(gatewayConnection);

        FeeMapper feeMapper = session.getMapper(FeeMapper.class);
        mockData.getFees().forEach(feeMapper::insertFee);

        mockData.getTeamFeeSetMap().forEach((aLong, stringTeamFeeSetHashMap)
                -> stringTeamFeeSetHashMap.forEach((s, teamFeeSet) -> {
            feeMapper.insertTeamFeeSet(teamFeeSet);
        }));
    }

    public MockData getMockData() {
        return mockData;
    }
}