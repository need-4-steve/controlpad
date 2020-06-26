package com.controlpad.payman_processor.test;

import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.client.ClientConfig;
import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.client.Features;
import com.controlpad.payman_common.common.Charge;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.datasource.SqlConfig;
import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.fee.FeeIds;
import com.controlpad.payman_common.fee.TeamFeeSet;
import com.controlpad.payman_common.fee.TeamFeeSetMap;
import com.controlpad.payman_common.migration.DatabaseType;
import com.controlpad.payman_common.team.PayoutScheme;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamConfig;
import com.controlpad.payman_processor.util.IDUtil;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import static com.controlpad.payman_common.transaction.TransactionType.*;

public class MockData {

    private static final Logger logger = LoggerFactory.getLogger(MockData.class);

    private ControlPadClient testClient;
    private Team teamOne;
    private Team teamTwo;
    private Team teamThree;
    private Team teamFour;
    private List<Fee> fees;
    private TeamFeeSetMap teamFeeSetMap;
    private List<Account> accounts;
    private Fee controlpadFee;

    private IDUtil idUtil;

    MockData(IDUtil idUtil) {
        this.idUtil = idUtil;
        createTestClient();
        createTeams();
        createFees();
        createAccount();
    }

    private void createTestClient() {
        SqlConfig sqlConfig = new SqlConfig("jdbc:mysql://localhost:3306/payman_test_processor_client", TestUtil.PAYMAN_TESTER_USERNAME, TestUtil.PAYMAN_TESTER_PASSWORD, DatabaseType.PAYMAN_CLIENT.getSlug());
        Features features = new Features(true, true, true, true, true, true);
        ClientConfig clientConfig = new ClientConfig(features);
        testClient = new ControlPadClient(idUtil.generateId(), "Test Client", clientConfig, sqlConfig, true);
    }

    private void createTeams() {
        teamOne = new Team("company", "Internal Team", new TeamConfig(false, false, false, true, false, new Money(3000D), PayoutScheme.MANUAL_SCHEDULE.getSlug()));
        teamTwo = new Team("rep", "Field Team", new TeamConfig(true, true, false, false, false, new Money(3000D), PayoutScheme.MANUAL_SCHEDULE.getSlug()));
        teamTwo.setTaxAccountId(1L);
        teamThree = new Team("ewallet", "EWallet Override Team", new TeamConfig(true, true, true, false, false, new Money(3000D), PayoutScheme.MANUAL_SCHEDULE.getSlug()));
        teamFour = new Team("task", "Task Processing Team", new TeamConfig(true, true, true, false, false, new Money(3000D), PayoutScheme.MANUAL_SCHEDULE.getSlug()));
    }

    private void createFees() {
        fees = new ArrayList<>();
        teamFeeSetMap = new TeamFeeSetMap();

        controlpadFee = new Fee(new Money(0.15), false, "Flat Transaction Fee", new Account("ControlPad", "123456789", "555555", "checking"));
        fees.add(new Fee(new Money(3.00), true, "Credit Card Sale", null));
        fees.add(new Fee(new Money(2.50), true, "Debit Card Sale", null));
        fees.add(controlpadFee);
        fees.add(new Fee(new Money(0.25), true, "Revenue Share", new Account("Client Account", "987654321", "777777", "checking")));
        fees.add(new Fee(new Money(50.00), true, "Subscription Fee", new Account("ControlPad Subscriptions", "123456789", "6666666", "checking")));

        HashMap<String, TeamFeeSet> teamOneFeeSetTypeMap = new HashMap<>();
        teamOneFeeSetTypeMap.put(CREDIT_CARD_SUB.slug,
                new TeamFeeSet(teamOne.getId(), CREDIT_CARD_SUB.slug, "Credit Card Subscription Sale", new FeeIds(1, 5)));
        teamOneFeeSetTypeMap.put(DEBIT_CARD_SUB.slug,
                new TeamFeeSet(teamOne.getId(), DEBIT_CARD_SUB.slug, "Debit Card Subscription Sale", new FeeIds(2, 5)));
        teamOneFeeSetTypeMap.put(CHECK_SUB.slug,
                new TeamFeeSet(teamOne.getId(), CHECK_SUB.slug, "E-Check Subscription sale", new FeeIds(5)));
        teamOneFeeSetTypeMap.put(E_WALLET_SUB.slug,
                new TeamFeeSet(teamOne.getId(), E_WALLET_SUB.slug, "E-Wallet Subscription Sale", new FeeIds(5)));
        teamFeeSetMap.put(teamOne.getId(), teamOneFeeSetTypeMap);

        HashMap<String, TeamFeeSet> teamTwoFeeSetTypeMap = new HashMap<>();
        teamTwoFeeSetTypeMap.put(CASH_SALE.slug,
                new TeamFeeSet(teamTwo.getId(), CASH_SALE.slug, "Field Cash Sale Fees", new FeeIds(3)));
        teamTwoFeeSetTypeMap.put(CREDIT_CARD_SALE.slug,
                new TeamFeeSet(teamTwo.getId(), CREDIT_CARD_SALE.slug, "Field Credit Card Sale Fees", new FeeIds(1, 4, 3)));
        teamTwoFeeSetTypeMap.put(DEBIT_CARD_SALE.slug,
                new TeamFeeSet(teamTwo.getId(), DEBIT_CARD_SALE.slug, "Field Debit Card Sale Fees", new FeeIds(2, 4, 3)));
        teamTwoFeeSetTypeMap.put(CHECK_SALE.slug,
                new TeamFeeSet(teamTwo.getId(), CHECK_SALE.slug, "Field E-Check Sale Fees", new FeeIds(4, 3)));

        teamFeeSetMap.put(teamTwo.getId(), teamTwoFeeSetTypeMap);

        //TODO team 3? or just add e wallet override in before running another try on team 2?
    }

    private void createAccount(){
        accounts = new ArrayList<>();
        accounts.add(new Account("Tax", "123456789", "123456789", "checking", "Chase Bank"));
        accounts.add(new Account("Fee", "987654321", "987654321", "checking", "Chase Bank"));
    }

    public ControlPadClient getTestClient() {
        return testClient;
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

    public List<Fee> getFeesForTeamAndType(String teamId, String type) {
        List<Fee> teamTypeFeeList = new ArrayList<>();
        if (!teamFeeSetMap.containsKey(teamId) || !teamFeeSetMap.get(teamId).containsKey(type)) {
            return teamTypeFeeList;
        }

        FeeIds feeIds = teamFeeSetMap.get(teamId).get(type).getFeeIds();
        for (Long feeId : feeIds) {
            for (Fee fee : fees) {
                if (fee.getId().equals(feeId)) {
                    teamTypeFeeList.add(fee);
                }
            }
        }
        return teamTypeFeeList;
    }

    public Fee getControlpadFee() {
        return controlpadFee;
    }

    public TeamFeeSetMap getTeamFeeSetMap() {
        return teamFeeSetMap;
    }

    public List<Account> getAccounts(){
        return accounts;
    }
}