package com.controlpad.payman_processor.test.payout_processing;


import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.team.PayoutMethod;
import org.junit.Before;
import org.junit.Test;

public class SubAccountProcessingTest extends PayoutProcessingTestBase {

    private static GatewayConnection user1Connection;
    private static GatewayConnection user2Connection;
    private static GatewayConnection user3Connection;
    private static GatewayConnection user4Connection;

    private static boolean setup = false;

    @Test
    public void subAccountProcessingTaskTest(){
        // TODO set team config
        runNormalTest();
        // TODO verify that reference id is set
    }

    @Test
    public void subAccountProcessingTaskAutoPayTest() {
        // TODO set team config
        runAutoPayTest();
        // TODO verify that reference id is set
    }

    @Before
    public void createConnections() {
        if (setup) {
            return;
        }
        setup = true;
        user1Connection = new GatewayConnection(getTestDataPrefix() + "1", getTestDataPrefix() + "_1", "Mock", null,
                null, null, "Fake key", "mock", true, false,
                true, false, true, null, false, true);
        user2Connection = new GatewayConnection(getTestDataPrefix() + "1", getTestDataPrefix() + "_2", "Mock", null,
                null, null, "Fake key", "mock", true, false,
                true, false, true, null, false, true);
        user3Connection = new GatewayConnection(getTestDataPrefix() + "2", getTestDataPrefix() + "_3", "Mock", null,
                null, null, "Fake key", "mock", true, false,
                true, false, true, null, false, true);
        user4Connection = new GatewayConnection(getTestDataPrefix() + "2", getTestDataPrefix() + "_4", "Mock", null,
                null, null, "Fake key", "mock", true, false,
                true, false, true, null, false, true);

        GatewayConnectionMapper gatewayConnectionMapper = getClientSqlSession().getMapper(GatewayConnectionMapper.class);
        gatewayConnectionMapper.insert(user1Connection);
        gatewayConnectionMapper.insert(user2Connection);
        gatewayConnectionMapper.insert(user3Connection);
        gatewayConnectionMapper.insert(user4Connection);
    }

    @Override
    protected String getTestDataPrefix() {
        return "sapt";
    }

    @Override
    protected boolean supportFees() {
        return false;
    }

    @Override
    protected GatewayConnection getConnectionForUser(String userId) {
        if (userId.contains("_1")) {
            return user1Connection;
        } else if (userId.contains("_2")) {
            return user2Connection;
        } else if (userId.contains("_3")) {
            return user3Connection;
        } else if (userId.contains("_4")) {
            return user4Connection;
        }
        System.out.println("Failed to getConnectionForUser: " + userId);
        return null;
    }

    @Override
    protected String getMerchantPayoutType() {
        return PayoutMethod.SUB_ACCOUNT.getSlug();
    }

    @Override
    protected String getCompanyPayoutType() {
        return PayoutMethod.SUB_ACCOUNT.getSlug();
    }

    @Override
    protected boolean shouldCreateMerchants() {
        return false;
    }

    @Override
    boolean checkReferenceId(PaymentType paymentType) {
        return true;
    }
}
