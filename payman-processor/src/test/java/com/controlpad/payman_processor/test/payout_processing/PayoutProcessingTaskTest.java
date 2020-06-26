package com.controlpad.payman_processor.test.payout_processing;


import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.team.PayoutMethod;
import com.controlpad.payman_processor.payout_processing.PayoutProcessingTask;
import org.junit.Before;
import org.junit.Test;



public class PayoutProcessingTaskTest extends PayoutProcessingTestBase {

    // TODO track entries then check them for payment_id and processed

    private static GatewayConnection team1Connection;
    private static GatewayConnection team2Connection;
    private static boolean setup = false;

    @Test
    public void payoutProcessingTaskTest(){
        runNormalTest();


        // TODO possibly check file
    }

    @Test
    public void payoutProcessingTaskAutoPayTest() {
        runAutoPayTest();
        // TODO possibly check file
    }

    @Before
    public void createConnections() {
        if (setup) {
            return;
        }
        setup = true;
        team1Connection = new GatewayConnection("pfptt1", null, "Mock", null,
                null, null, "Fake key", "mock", true, true,
                true, false, false, null, false, true);
        team2Connection = new GatewayConnection("pfptt2", null, "Mock", null,
                null, null, "Fake key", "mock", true, true,
                true, false, false, null, false, true);
        GatewayConnectionMapper gatewayConnectionMapper = getClientSqlSession().getMapper(GatewayConnectionMapper.class);
        gatewayConnectionMapper.insert(team1Connection);
        gatewayConnectionMapper.insert(team2Connection);
    }

    @Override
    protected String getTestDataPrefix() {
        return "pfptt";
    }

    @Override
    protected boolean supportFees() {
        return true;
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
        return PayoutMethod.FILE.getSlug();
    }

    @Override
    protected String getCompanyPayoutType() {
        return PayoutMethod.FILE.getSlug();
    }

    @Override
    protected boolean shouldCreateMerchants() {
        return false;
    }

    @Override
    boolean checkReferenceId(PaymentType paymentType) {
        return false;
    }
}