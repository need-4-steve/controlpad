package com.controlpad.payman_processor.test.payout_processing;

import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.payment_provider.PaymentProvider;
import com.controlpad.payman_common.payment_provider.PaymentProviderMapper;
import com.controlpad.payman_common.team.PayoutMethod;
import com.controlpad.payman_common.team.Team;
import org.junit.Before;
import org.junit.Test;

public class PaymentProviderProcessingTest extends PayoutProcessingTestBase {

    private static boolean setup = false;

    private static final String team1 = "ppptt1";
    private static final String team2 = "ppptt2";
    private static GatewayConnection t1Connection;
    private static GatewayConnection t2Connection;
    private static PaymentProvider paymentProvider;

    @Test
    public void paymentProviderProcessingTaskTest(){
        runNormalTest();
    }

    @Test
    public void paymentProviderProcessingTaskAutoPayTest() {
        runAutoPayTest();
    }

    @Before
    public void createConnections() {
        if (setup) {
            return;
        }
        setup = true;

        paymentProvider = new PaymentProvider("Mock provider", "mock", null);
        getClientSqlSession().getMapper(PaymentProviderMapper.class).insert(paymentProvider);

        t1Connection = new GatewayConnection(getTestDataPrefix() + "1", null, "Mock", null,
                null, null, "Fake key", "mock", true, true,
                true, false, false, null, false, true);
        t2Connection = new GatewayConnection(getTestDataPrefix() + "2", null, "Mock", null,
                null, null, "Fake key", "mock", true, true,
                true, false, false, null, false, true);

        GatewayConnectionMapper gatewayConnectionMapper = getClientSqlSession().getMapper(GatewayConnectionMapper.class);
        gatewayConnectionMapper.insert(t1Connection);
        gatewayConnectionMapper.insert(t2Connection);
    }

    @Override
    boolean checkReferenceId(PaymentType paymentType) {
        switch (paymentType) {
            case WITHDRAW:
                return true;
            case FEE:
            case SALES_TAX:
            case CONSIGNMENT:
            default:
                return false;
        }
    }

    @Override
    protected String getTestDataPrefix() {
        return "ppptt";
    }

    @Override
    protected boolean supportFees() {
        return true;
    }

    @Override
    protected GatewayConnection getConnectionForUser(String userId) {
        if (userId.contains("_1") || userId.contains("_2")) {
            return t1Connection;
        } else {
            return t2Connection;
        }
    }

    @Override
    protected String getMerchantPayoutType() {
        return PayoutMethod.PAYMENT_PROVIDER.getSlug();
    }

    @Override
    protected String getCompanyPayoutType() {
        return PayoutMethod.FILE.getSlug();
    }

    @Override
    protected boolean shouldCreateMerchants() {
        return true;
    }

    @Override
    protected void overrideTeamSetting(Team team) {
        team.setPaymentProviderId(paymentProvider.getId());
    }
}
