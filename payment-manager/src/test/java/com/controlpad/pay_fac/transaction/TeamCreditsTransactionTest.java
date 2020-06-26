/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.transaction;

import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.credits.CreditsMapper;
import com.controlpad.payman_common.credits.TeamCredit;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.transaction.TransferPayment;
import org.junit.Test;

import java.math.BigDecimal;

public class TeamCreditsTransactionTest extends PaymentControllerTest {

    @Test
    public void teamCreditsSaleTest() {
        String path = "/transactions/sale/team-credits";
        String payerUserId = "TCredits User 1";
        TeamCredit payerCredits = createTeamCredit(payerUserId, getTeamTwoId(), new Money(200));

        TransactionResponse transactionResponse = internalPaymentTest(path, new TransferPayment(getSubtotal(),
                null, "Company", payerUserId, getTeamTwoId()),
                TransactionType.TEAM_CREDITS_SALE, false);

        assertThatTeamCreditDecremented(payerCredits, transactionResponse.getTransaction());

        testTransferBalanceRejection(path);
    }

    @Test
    public void teamCreditsTransferTest() {
        String path = "/transactions/transfer/team-credits";
        String payeeUserId = "TCredits User 2";
        String payerUserId = "TCredits User 3";

        TeamCredit payeeCredits = createTeamCredit(payeeUserId, getTeamTwoId(), BigDecimal.ZERO);
        TeamCredit payerCredits = createTeamCredit(payerUserId, getTeamTwoId(), new Money(200D));

        TransactionResponse transactionResponse = internalPaymentTest(path, new TransferPayment(getSubtotal(),
                null, payeeUserId, payerUserId, getTeamTwoId()),
                TransactionType.TEAM_CREDITS_TRANSFER, false);

        assertThatTeamCreditDecremented(payerCredits, transactionResponse.getTransaction());
        assertThatTeamCreditIncremented(payeeCredits, transactionResponse.getTransaction());

        testTransferBalanceRejection(path);
    }

    @Test
    public void teamCreditsCreditTest() {
        String path = "/transactions/credit/team-credits";
        String payeeUserId = "TCredits User 4";

        TeamCredit payeeCredits = createTeamCredit(payeeUserId, getTeamTwoId(), BigDecimal.ZERO);

        TransactionResponse transactionResponse = internalPaymentTest(path, new TransferPayment(getSubtotal(),
                null, payeeUserId, "Compnay", getTeamTwoId()),
                TransactionType.TEAM_CREDITS_CREDIT, true);

        assertThatTeamCreditIncremented(payeeCredits, transactionResponse.getTransaction());
    }

    private TeamCredit createTeamCredit(String userId, String teamId, BigDecimal balance) {
        TeamCredit teamCredit = new TeamCredit(userId, teamId, balance);
        CreditsMapper creditsMapper = getSqlSession().getMapper(CreditsMapper.class);
        creditsMapper.insertTeamCredit(teamCredit);
        getSqlSession().commit();
        return teamCredit;
    }

    private void assertThatTeamCreditDecremented(TeamCredit teamCredit, Transaction transaction) {
        BigDecimal balance = getSqlSession().getMapper(CreditsMapper.class).getTeamCreditsBalance(teamCredit.getUserId(), teamCredit.getTeamId());
        assert balance != null;
        BigDecimal assumedBalance = teamCredit.getBalance().subtract(transaction.getAmount());
        assert balance.compareTo(assumedBalance) == 0;
        teamCredit.setBalance(balance);
    }

    private void assertThatTeamCreditIncremented(TeamCredit teamCredit, Transaction transaction) {
        BigDecimal balance = getSqlSession().getMapper(CreditsMapper.class).getTeamCreditsBalance(teamCredit.getUserId(), teamCredit.getTeamId());
        assert balance != null;
        assert balance.compareTo(teamCredit.getBalance().add(transaction.getAmount())) == 0;
        teamCredit.setBalance(balance);
    }

    private void testTransferBalanceRejection(String path) {
        performUnsuccessfulRequest(path, new TransferPayment(getSubtotal(), null, "1", "Invalid User", getTeamTwoId()));
    }
}