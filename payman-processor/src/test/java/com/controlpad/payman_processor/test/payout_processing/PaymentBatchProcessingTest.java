package com.controlpad.payman_processor.test.payout_processing;

import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.team.PayoutMethod;
import org.junit.Before;
import org.junit.Test;

public class PaymentBatchProcessingTest extends PayoutProcessingTestBase {

    private static GatewayConnection team1Connection;
    private static GatewayConnection team2Connection;
    private static boolean setup = false;

    @Test
    public void paymentBatchProcessingTaskTest(){
        runNormalTest();

        // TODO check that batch was created
        // TODO check that file was created
    }

    @Test
    public void paymentBatchProcessingTaskAutoPayTest() {
        runAutoPayTest();

        // TODO check batch
        // TODO check file
    }

    @Override
    boolean checkReferenceId(PaymentType paymentType) {
        return false;
    }

    @Override
    protected String getTestDataPrefix() {
        return "pbpt";
    }

    @Override
    protected boolean supportFees() {
        return true;
    }

    @Before
    public void createConnections() {
        if (setup) {
            return;
        }
        setup = true;
        team1Connection = new GatewayConnection("pbpt1", null, "Mock", null,
                null, null, "Fake key", "mock", true, true,
                true, false, false, null, false, true);
        team2Connection = new GatewayConnection("pbpt2", null, "Mock", null,
                null, null, "Fake key", "mock", true, true,
                true, false, false, null, false, true);
        GatewayConnectionMapper gatewayConnectionMapper = getClientSqlSession().getMapper(GatewayConnectionMapper.class);
        gatewayConnectionMapper.insert(team1Connection);
        gatewayConnectionMapper.insert(team2Connection);
    }

    @Override
    protected GatewayConnection getConnectionForUser(String userId) {
        if (userId.contains("_1") || userId.contains("_2")) {
            return team1Connection;
        } else {
            return team2Connection;
        }
    }

    @Override
    protected String getMerchantPayoutType() {
        return PayoutMethod.PAYMENT_BATCH.getSlug();
    }

    @Override
    protected String getCompanyPayoutType() {
        return PayoutMethod.FILE.getSlug();
    }

    @Override
    protected boolean shouldCreateMerchants() {
        return true;
    }
}
