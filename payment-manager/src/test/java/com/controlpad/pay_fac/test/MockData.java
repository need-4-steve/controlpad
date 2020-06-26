package com.controlpad.pay_fac.test;

import com.controlpad.pay_fac.api_key.APIKey;
import com.controlpad.pay_fac.auth.AuthUtil;
import com.controlpad.pay_fac.auth.Session;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.ach.ACH;
import com.controlpad.payman_common.client.ClientConfig;
import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.client.Features;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.datasource.SqlConfig;
import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.fee.FeeIds;
import com.controlpad.payman_common.fee.TeamFeeSet;
import com.controlpad.payman_common.fee.TeamFeeSetMap;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionType;
import com.controlpad.payman_common.migration.DatabaseType;
import com.controlpad.payman_common.payman_user.PayManUser;
import com.controlpad.payman_common.payman_user.Privilege;
import com.controlpad.payman_common.team.PayoutScheme;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamConfig;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import static com.controlpad.payman_common.transaction.TransactionType.*;

public class MockData {

    private static final Logger logger = LoggerFactory.getLogger(MockData.class);

    private PayManUser adminUser;
    private Session adminSession;
    private Session adminClientSession;
    private String passwordHash;
    private ControlPadClient testClient;
    private APIKey testApiKey;

    private Team teamOne;
    private Team teamTwo;
    private Team teamThree;
    private Team teamFour;
    private List<Fee> fees;
    private TeamFeeSetMap teamFeeSetMap;

    private GatewayConnection repGatewayConnection;

    private ACH ach;

    private Fee controlpadFee;

    private SqlConfig paymanSqlConfig;

    public MockData(AuthUtil authUtil, IDUtil idUtil) {
        try{
            passwordHash = authUtil.encodePassword("password");

            testClient = createTestClient(idUtil);

            adminUser = new PayManUser(idUtil.generateId(), null, "admin", "password", null, new Privilege(true, false, 0, 0, 0));

        } catch (Exception e) {
            logger.error(e.getMessage(), e);
            assert false;
        }
        createTeams();
        createFees();
        ach = new ACH(1L, "123456789", "123456789", "Some Bank", "Some Bank", "Some Company", "CompanyId");
        repGatewayConnection = new GatewayConnection(getTeamFour().getId(), "repGatewayUser", "Rep Connection", "Some User", "Some key", "Public Key",
                        "Some pin", GatewayConnectionType.MOCK.slug, true, false, true, true, false, true);
    }

    private ControlPadClient createTestClient(IDUtil idUtil) {
        SqlConfig sqlConfig = new SqlConfig("jdbc:mysql://localhost:3306/payman_test_client",
                TestUtil.PAYMAN_TESTER_USERNAME,
                TestUtil.PAYMAN_TESTER_PASSWORD,
                DatabaseType.PAYMAN_CLIENT.getSlug());
        Features features = new Features(true, true, true, true, true, false);
        ClientConfig clientConfig = new ClientConfig(features);
        return new ControlPadClient(idUtil.generateId(), "Test Client", clientConfig, sqlConfig, true);
    }

    private void createTeams() {
        teamOne = new Team("company", "Internal Team", new TeamConfig(false, false, false, false, false, new Money(3000D), PayoutScheme.AUTO_SCHEDULE.getSlug()));
        teamTwo = new Team("rep", "Field Team", new TeamConfig(true, false, true, false, false, new Money(3000D), PayoutScheme.AUTO_SCHEDULE.getSlug()));
        teamThree = new Team("3", "EWallet Override Team", new TeamConfig(true, false, true, false, false, new Money(3000D), PayoutScheme.AUTO_SCHEDULE.getSlug()));
        teamFour = new Team("user-team", "User gateway team", new TeamConfig(false, false, false, false, true, new Money(3000D), null));
    }

    private void createFees() {
        fees = new ArrayList<>();
        teamFeeSetMap = new TeamFeeSetMap();

        controlpadFee = new Fee(new Money(0.15), false, "Flat Transaction Fee", new Account("ControlPad", "123456789", "555555", "checking", null));
        fees.add(new Fee(new Money(3.00), true, "Credit TokenRequest Sale", null));
        fees.add(new Fee(new Money(2.50), true, "Debit TokenRequest Sale", null));
        fees.add(controlpadFee);
        fees.add(new Fee(new Money(0.25), true, "Revenue Share", new Account("Client Account", "987654321", "777777", "checking", null)));
        fees.add(new Fee(new Money(50.00), true, "Subscription Fee", new Account("ControlPad Subscriptions", "123456789", "6666666", "checking", null)));
        fees.add(new Fee(new Money(0.30), false, "ACH flat fee", null));

        HashMap<String, TeamFeeSet> teamOneFeeSetTypeMap = new HashMap<>();
        teamOneFeeSetTypeMap.put(CREDIT_CARD_SUB.slug,
                new TeamFeeSet(teamOne.getId(), CREDIT_CARD_SUB.slug, "Credit TokenRequest Subscription Sale", new FeeIds(1, 5)));
        teamOneFeeSetTypeMap.put(DEBIT_CARD_SUB.slug,
                new TeamFeeSet(teamOne.getId(), DEBIT_CARD_SUB.slug, "Debit TokenRequest Subscription Sale", new FeeIds(2, 5)));
        teamOneFeeSetTypeMap.put(CHECK_SUB.slug,
                new TeamFeeSet(teamOne.getId(), CHECK_SUB.slug, "E-Check Subscription sale", new FeeIds(5)));
        teamOneFeeSetTypeMap.put(E_WALLET_SUB.slug,
                new TeamFeeSet(teamOne.getId(), E_WALLET_SUB.slug, "E-Wallet Subscription Sale", new FeeIds(5)));
        teamFeeSetMap.put(teamOne.getId(), teamOneFeeSetTypeMap);

        HashMap<String, TeamFeeSet> teamTwoFeeSetTypeMap = new HashMap<>();
        teamTwoFeeSetTypeMap.put(CASH_SALE.slug,
                new TeamFeeSet(teamTwo.getId(), CASH_SALE.slug, "Field Cash Sale Fees", new FeeIds(3)));
        teamTwoFeeSetTypeMap.put(CREDIT_CARD_SALE.slug,
                new TeamFeeSet(teamTwo.getId(), CREDIT_CARD_SALE.slug, "Field Credit TokenRequest Sale Fees", new FeeIds(1, 4, 3)));
        teamTwoFeeSetTypeMap.put(DEBIT_CARD_SALE.slug,
                new TeamFeeSet(teamTwo.getId(), DEBIT_CARD_SALE.slug, "Field Debit TokenRequest Sale Fees", new FeeIds(2, 4, 3)));
        teamTwoFeeSetTypeMap.put(CHECK_SALE.slug,
                new TeamFeeSet(teamTwo.getId(), CHECK_SALE.slug, "Field E-Check Sale Fees", new FeeIds(4, 3)));
        teamTwoFeeSetTypeMap.put(ACH_PAYMENT_TAX.slug,
                new TeamFeeSet(teamTwo.getId(), ACH_PAYMENT_TAX.slug, "Field ACH Payment Tax Balance Fees", new FeeIds(6)));

        teamFeeSetMap.put(teamTwo.getId(), teamTwoFeeSetTypeMap);

        //TODO team 3? or just add e wallet override in before running another try on team 2?
    }

    public PayManUser getAdminUser() {
        return adminUser;
    }

    public Session getAdminSession() {
        return adminSession;
    }

    public Session getAdminClientSession() {
        return adminClientSession;
    }

    public ControlPadClient getTestClient() {
        return testClient;
    }

    public APIKey getTestApiKey() {
        return testApiKey;
    }

    public Team getTeamOne() {
        return teamOne;
    }

    public Team getTeamTwo() {
        return teamTwo;
    }

    public Team getTeamThree() {
        return teamThree;
    }

    public Team getTeamFour() {
        return teamFour;
    }

    public List<Fee> getFees() {
        return fees;
    }

    public ACH getAch() {
        return ach;
    }

    public List<Fee> getFeesForTeamAndType(String team, String type) {
        List<Fee> teamTypeFeeList = new ArrayList<>();
        if (!teamFeeSetMap.containsKey(team) || !teamFeeSetMap.get(team).containsKey(type)) {
            return teamTypeFeeList;
        }

        FeeIds feeIds = teamFeeSetMap.get(team).get(type).getFeeIds();
        for (Long feeId : feeIds) {
            for (Fee fee : fees) {
                if (fee.getId().equals(feeId)) {
                    teamTypeFeeList.add(fee);
                }
            }
        }
        return teamTypeFeeList;
    }

    public Fee getFeeForId(Long id) {
        for (Fee fee : fees) {
            if (fee.getId().equals(id))
                return fee;
        }
        return null;
    }

    public Fee getControlpadFee() {
        return controlpadFee;
    }

    public TeamFeeSetMap getTeamFeeSetMap() {
        return teamFeeSetMap;
    }

    public void setTestApiKey(APIKey testApiKey) {
        this.testApiKey = testApiKey;
    }

    public void setAdminSession(Session adminSession) {
        this.adminSession = adminSession;
    }

    public void setAdminClientSession(Session adminClientSession) {
        this.adminClientSession = adminClientSession;
    }

    public GatewayConnection getRepGatewayConnection() {
        return repGatewayConnection;
    }
}