package com.controlpad.pay_fac.express;

import com.controlpad.pay_fac.ControllerTest;
import com.controlpad.pay_fac.api_key.APIKey;
import com.controlpad.pay_fac.api_key.APIKeyMapper;
import com.controlpad.pay_fac.auth.LoginObject;
import com.controlpad.pay_fac.gateway.GatewayUtil;
import com.controlpad.pay_fac.payment_info.CardPayment;
import com.controlpad.pay_fac.test.TestUtil;
import com.controlpad.pay_fac.transaction.TransactionResponse;
import com.controlpad.payman_common.client.ClientMapper;
import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.datasource.SqlConfig;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_common.util.GsonUtil;
import com.google.gson.reflect.TypeToken;
import org.apache.ibatis.session.SqlSession;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;

import java.math.BigDecimal;
import java.util.List;

public class ExpressClientSignupTest extends ControllerTest {

    @Autowired
    GatewayUtil gatewayUtil;

    @Override
    public boolean isAutoCommit() {
        return true;
    }

    @Test
    public void testSandboxExpressClient() {
        String database = "payman_test_express_client";
        String path = "/express/default-client-model";

        GatewayConnection requestConnection = new GatewayConnection("company", null, "Company Gateway", "Some Id",
                "Private Key", "Public Key", "pin", "mock", true,
                true, true, false, false, true);
        ExpressClientRequest expressClientRequest = new ExpressClientRequest();
        expressClientRequest.setUser(new LoginObject("expressuseradmin", "password"));
        expressClientRequest.setClientName("Express Client");
        expressClientRequest.setSandbox(true);
        expressClientRequest.setSqlConfig(
                new SqlConfig("jdbc:mysql://localhost:3306/" + database,
                TestUtil.PAYMAN_TESTER_USERNAME,
                        TestUtil.PAYMAN_TESTER_PASSWORD,
                        null));
        expressClientRequest.setGatewayConnection(requestConnection);

        ExpressClientResponse response = performPost(path, ExpressSignupController.EXPRESS_KEY, expressClientRequest, new TypeToken<ExpressClientResponse>(){});
        assert response.getSuccess();
        assert response.getStatusCode() == 1;

        System.out.println("Response:");
        System.out.println(GsonUtil.getGson().toJson(response));

        String apiKey = response.getData().getApiKey();
        String clientId = null;

        try (SqlSession apiSession = getSqlSessionUtil().openPaymanSession(true)){
            ClientMapper clientMapper = apiSession.getMapper(ClientMapper.class);
            APIKeyMapper apiKeyMapper = apiSession.getMapper(APIKeyMapper.class);

            APIKey key = apiKeyMapper.findAPIKeyForId(apiKey);
            assert key != null;
            assert !key.getDisabled();
            assert key.getClientId() != null;
            assert key.getConfig() != null;
            assert key.getConfig().getProcessSales();
            assert key.getConfig().getUpdateAccounts();

            ControlPadClient client = clientMapper.findClientForId(key.getClientId());
            // assert client
            assert client != null;
            assert expressClientRequest.getSandbox().equals(client.getSandbox());
            assert client.getName().equals(expressClientRequest.getClientName());
            // assert sql config
            assert client.getSqlConfigWrite() != null;
            assert expressClientRequest.getSqlConfig().getUrl().equals(client.getSqlConfigWrite().getUrl());
            assert expressClientRequest.getSqlConfig().getPassword().equals(client.getSqlConfigWrite().getPassword());
            assert expressClientRequest.getSqlConfig().getUsername().equals(client.getSqlConfigWrite().getUsername());
            // assert config
            assert client.getConfig() != null;
            assert client.getConfig().getFeatures() != null;
            assert client.getConfig().getFeatures().getAccountValidation();
            assert client.getConfig().getFeatures().getRefund();
            assert client.getConfig().getFeatures().getEWallet();

            clientId = client.getId();
        } catch (Exception e) {
            e.printStackTrace();
            assert false;
        }

        try (SqlSession clientSession = getSqlSessionUtil().openSession(clientId, true)){
            List<Team> teams = clientSession.getMapper(TeamMapper.class).list();
            Team currentTeam;
            assert teams.size() == 3;
            // Team 1
            currentTeam = teams.get(0);
            assert currentTeam.getId().equals("company");
            assert currentTeam.getConfig() != null;
            assert !currentTeam.getConfig().getUserGatewayConnections();
            assert !currentTeam.getConfig().getMerchantPayouts();
            assert !currentTeam.getConfig().getCollectSalesTax();
            assert currentTeam.getConfig().getPayoutScheme() == null;
            assert currentTeam.getConfig().geteWalletLimit() != null;
            // Team 2
            currentTeam = teams.get(1);
            assert currentTeam.getId().equals("controlpad");
            assert !currentTeam.getConfig().getUserGatewayConnections();
            assert !currentTeam.getConfig().getCollectSalesTax();
            assert !currentTeam.getConfig().getMerchantPayouts();
            assert currentTeam.getConfig().getPayoutScheme() == null;
            assert currentTeam.getConfig().geteWalletLimit() != null;

            // Team 3
            currentTeam = teams.get(2);
            assert currentTeam.getId().equals("rep");
            assert currentTeam.getConfig().getUserGatewayConnections();
            assert !currentTeam.getConfig().getMerchantPayouts();
            assert !currentTeam.getConfig().getCollectSalesTax();
            assert currentTeam.getConfig().geteWalletLimit() != null;
            assert currentTeam.getConfig().getPayoutScheme() == null;

            List<GatewayConnection> gatewayConnections = clientSession.getMapper(GatewayConnectionMapper.class)
                    .search(null, null, true, null, null, null, true, 10, 0);

            assert gatewayConnections.size() == 2;
            GatewayConnection companyConnection = gatewayConnections.get(0);
            assert companyConnection.getActive();
            assert companyConnection.getUsername().equals(requestConnection.getUsername());
            assert companyConnection.getIsSandbox().equals(requestConnection.getIsSandbox());
            assert companyConnection.getPin().equals(requestConnection.getPin());
            assert companyConnection.getType().equals(requestConnection.getType());
            assert companyConnection.getUserId() == null;
            assert companyConnection.getTeamId().equals(requestConnection.getTeamId());
            assert companyConnection.getPrivateKey().equals(requestConnection.getPrivateKey());
            assert companyConnection.getPublicKey().equals(requestConnection.getPublicKey());
            assert companyConnection.getName().equals(requestConnection.getName());

            GatewayConnection cpGateway = gatewayUtil.getControlpadSandboxGateway();
            GatewayConnection clientCPGateway = gatewayConnections.get(1);

            assert clientCPGateway.getIsSandbox().equals(expressClientRequest.getSandbox());
            assert clientCPGateway.getName().equals(cpGateway.getName());
            assert clientCPGateway.getPrivateKey().equals(cpGateway.getPrivateKey());
            assert clientCPGateway.getActive();
            assert clientCPGateway.getTeamId().equals(cpGateway.getTeamId());
            assert clientCPGateway.getType().equals(cpGateway.getType());
            assert clientCPGateway.getUserId() == null;
            assert clientCPGateway.getPin().equals(cpGateway.getPin());
            assert clientCPGateway.getPublicKey() == null;
            assert clientCPGateway.getUsername() == null;
        } catch (Exception e) {
            e.printStackTrace();
            assert false;
        }

        // Verify api key works for a sale
        CardPayment cardPayment = new CardPayment("Payer ID", "Payee ID", "company",
                "Customer", new Money(0.60), BigDecimal.ZERO, new Money(10.00), "Some description",
                "4111111111111111", "555", 2100, 9);
        TransactionResponse transactionResponse = performPost("/transactions/sale/credit-card", "APIKey " + apiKey,cardPayment, new TypeToken<TransactionResponse>(){});
        assert transactionResponse.getSuccess();

        // TODO test validations
    }
}