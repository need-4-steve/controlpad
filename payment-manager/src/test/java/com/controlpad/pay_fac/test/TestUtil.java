package com.controlpad.pay_fac.test;

import com.controlpad.pay_fac.api_key.APIKeyConfig;
import com.controlpad.pay_fac.api_key.APIKeyMapper;
import com.controlpad.pay_fac.auth.AuthMapper;
import com.controlpad.pay_fac.auth.AuthUtil;
import com.controlpad.pay_fac.client.ClientConfigUtil;
import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.payman_common.ach.ACHMapper;
import com.controlpad.payman_common.client.ClientMapper;
import com.controlpad.payman_common.datasource.SqlConfig;
import com.controlpad.payman_common.fee.FeeMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionType;
import com.controlpad.payman_common.migration.DatabaseType;
import com.controlpad.payman_common.migration.MigrationUtil;
import com.controlpad.payman_common.payman_user.PayManUserMapper;
import com.controlpad.payman_common.team.TeamMapper;
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

    public static final String PAYMAN_TESTER_USERNAME = "payman_tester";
    public static final String PAYMAN_TESTER_PASSWORD = "password";

    private static final Logger logger = LoggerFactory.getLogger(TestUtil.class);

    private MockData mockData;
    private MigrationUtil migrationUtil;
    private IDUtil idUtil;

    @Autowired
    public TestUtil(SqlSessionUtil sqlSessionUtil, AuthUtil authUtil, ClientConfigUtil clientConfigUtil) throws Exception {
        System.out.println("Creating testUtil");
        System.setProperty("DEBUG", "true");
        System.setProperty("LOCAL_STORAGE", "true");
        idUtil = new IDUtil();
        mockData = new MockData(authUtil, idUtil);
        migrationUtil = new MigrationUtil();

        sqlSessionUtil.getPaymanDatasource().setSqlConfig(
                new SqlConfig("jdbc:mysql://localhost:3306/payman_test",
                        PAYMAN_TESTER_USERNAME,
                        PAYMAN_TESTER_PASSWORD,
                        DatabaseType.PAYMAN.getSlug()));

        createDatabases(migrationUtil, sqlSessionUtil);


        SqlSession apiSession = sqlSessionUtil.openPaymanSession(true);
        loadAPIDatabase(apiSession);
        createTestApiKey(authUtil, apiSession);
        createAdminSession(authUtil, apiSession);
        apiSession.close();

        sqlSessionUtil.addClientDatasource(mockData.getTestClient());
        SqlSession clientSession = sqlSessionUtil.openSession(mockData.getTestClient().getId(), true);
        loadClientDatabase(authUtil, clientSession);
        clientSession.close();

        // Refresh this util
        clientConfigUtil.scheduledRefreshClientMap();
    }

    public void checkPagination(PaginatedResponse response, long page, long count){
        if(response.getTotal()%count != 0){
            assert response.getTotalPage().equals(response.getTotal() / count + 1);
        }else{
            assert response.getTotalPage().equals(response.getTotal() /count);
        }

        if (page < response.getTotalPage()) {
            assert response.getData().size() == count;
        }
        else if(page == response.getTotalPage()){
            assert response.getData().size() == response.getTotal() - (page - 1) * count;
        }
        else{
            assert response.getData().isEmpty();
        }
    }

    private void createDatabases(MigrationUtil migrationUtil, SqlSessionUtil sqlSessionUtil) {
        System.out.println("createDatabases");

        // Clear databases
        try (Connection connection = DriverManager.getConnection("jdbc:mysql://localhost:3306/", PAYMAN_TESTER_USERNAME, PAYMAN_TESTER_PASSWORD);
             Statement statement = connection.createStatement()){
            statement.execute("DROP DATABASE IF EXISTS payman_test");
            statement.execute("DROP DATABASE IF EXISTS payman_test_client");
            statement.execute("DROP DATABASE IF EXISTS payman_test_express_client");
            statement.execute("CREATE DATABASE IF NOT EXISTS payman_test");
            statement.execute("CREATE DATABASE IF NOT EXISTS payman_test_client");
            statement.execute("CREATE DATABASE IF NOT EXISTS payman_test_express_client");
        } catch(Exception e){
            e.printStackTrace();
            System.out.println("Other errors: " + e);
        }

        if (!migrationUtil.migrate(sqlSessionUtil.getPaymanDatasource().getSqlConfig())) {
            throw new RuntimeException("Failed to migrate payman");
        }
        if (!migrationUtil.migrate(mockData.getTestClient().getSqlConfigWrite())) {
            throw new RuntimeException("Failed to migrate payman_client");
        }
    }

    private void loadAPIDatabase(SqlSession session) {
        System.out.println("loadAPIDatabase");
        ClientMapper clientMapper = session.getMapper(ClientMapper.class);
        clientMapper.insertClient(mockData.getTestClient());
        PayManUserMapper payManUserMapper = session.getMapper(PayManUserMapper.class);
        payManUserMapper.insertPayManUser(mockData.getAdminUser());
    }

    private void createTestApiKey(AuthUtil authUtil, SqlSession session) throws Exception {
        System.out.println("createTestApiKey");
        mockData.setTestApiKey(authUtil.buildNewApiKey(mockData.getTestClient().getId(), new APIKeyConfig(true, true, true)));
        session.getMapper(APIKeyMapper.class).insertAPIKey(mockData.getTestApiKey());
    }

    private void createAdminSession(AuthUtil authUtil, SqlSession session) throws Exception {
        System.out.println("createAdminSession");
        mockData.setAdminSession(authUtil.generateNewSession(mockData.getAdminUser().getId(), null));
        mockData.setAdminClientSession(authUtil.generateNewSession(mockData.getAdminUser().getId(), mockData.getTestClient().getId()));
        session.getMapper(AuthMapper.class).insertSession(mockData.getAdminSession());
        session.getMapper(AuthMapper.class).insertSession(mockData.getAdminClientSession());
    }

    private void loadClientDatabase(AuthUtil authUtil, SqlSession session) {
        GatewayConnectionMapper gatewayConnectionMapper = session.getMapper(GatewayConnectionMapper.class);

        // Insert teams with different settings
        TeamMapper teamMapper = session.getMapper(TeamMapper.class);
        teamMapper.insert(mockData.getTeamOne());
        teamMapper.insert(mockData.getTeamTwo());
        teamMapper.insert(mockData.getTeamThree());
        teamMapper.insert(mockData.getTeamFour());

        GatewayConnection gatewayConnection = new GatewayConnection(null, null, "Default Connection", "Some User",
                "Some complicated key", "Public key", "Some pin", GatewayConnectionType.MOCK.slug, true, true, true, true, true, true);
        // Add new gateway record for team 1
        gatewayConnection.setTeamId(mockData.getTeamOne().getId());
        gatewayConnectionMapper.insert(gatewayConnection);
        // Add new gateway record for same gateway for team 2
        gatewayConnection.setTeamId(mockData.getTeamTwo().getId());
        gatewayConnectionMapper.insert(gatewayConnection);
        // Add new gateway record for same gateway for team 3
        gatewayConnection.setTeamId(mockData.getTeamThree().getId());
        gatewayConnectionMapper.insert(gatewayConnection);
        // Add new gateway for a rep
        GatewayConnection userGatewayConnection = mockData.getRepGatewayConnection();
        gatewayConnectionMapper.insert(userGatewayConnection);

        FeeMapper feeMapper = session.getMapper(FeeMapper.class);
        mockData.getFees().forEach(feeMapper::insertFee);

        mockData.getTeamFeeSetMap().forEach((aLong, stringTeamFeeSetHashMap)
                -> stringTeamFeeSetHashMap.forEach((s, teamFeeSet) -> {
            feeMapper.insertTeamFeeSet(teamFeeSet);
        }));

        session.getMapper(ACHMapper.class).insert(mockData.getAch());
    }

    public MockData getMockData() {
        return mockData;
    }

    public IDUtil getIdUtil() {
        return idUtil;
    }
}