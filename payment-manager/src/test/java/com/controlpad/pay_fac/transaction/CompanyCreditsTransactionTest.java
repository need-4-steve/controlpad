/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.transaction;

import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.credits.CompanyCredit;
import com.controlpad.payman_common.credits.CreditsMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.transaction.TransferPayment;
import org.junit.Test;

import java.math.BigDecimal;

public class CompanyCreditsTransactionTest extends PaymentControllerTest {

    @Test
    public void companyCreditsSaleTest() {
        String path = "/transactions/sale/company-credits";
        String payerUserId = "CCredits User 1";
        CompanyCredit payerCredits = createCompanyCredit(payerUserId, new Money(200));

        TransactionResponse transactionResponse = internalPaymentTest(path, new TransferPayment(getSubtotal(),
                null, "Company", payerUserId, getTeamOneId()),
                TransactionType.COMPANY_CREDITS_SALE, false);

        assertThatCompanyCreditDecremented(payerCredits, transactionResponse.getTransaction());

        testTransferBalanceRejection(path);
    }

    @Test
    public void companyCreditsTransferTest() {
        String path = "/transactions/transfer/company-credits";
        String payeeUserId = "CCredits User 3";
        String payerUserId = "CCredits User 2";
        CompanyCredit payerCredits = createCompanyCredit(payerUserId, new Money(200));
        CompanyCredit payeeCredits = createCompanyCredit(payeeUserId, BigDecimal.ZERO);

        TransactionResponse transactionResponse = internalPaymentTest(path, new TransferPayment(getSubtotal(),
                null, payeeUserId, payerUserId, getTeamTwoId()),
                TransactionType.COMPANY_CREDITS_TRANSFER, false);

        assertThatCompanyCreditDecremented(payerCredits, transactionResponse.getTransaction());
        assertThatCompanyCreditIncremented(payeeCredits, transactionResponse.getTransaction());

        testTransferBalanceRejection(path);
    }

    @Test
    public void companyCreditsCreditTest() {
        String path = "/transactions/credit/company-credits";
        String payeeUserId = "CCredits User 4";

        CompanyCredit payeeCredits = createCompanyCredit(payeeUserId, BigDecimal.ZERO);

        TransactionResponse transactionResponse = internalPaymentTest(path, new TransferPayment(getSubtotal(),
                null, payeeUserId, "Company", getTeamOneId()),
                TransactionType.COMPANY_CREDITS_CREDIT, true);
        assertThatCompanyCreditIncremented(payeeCredits, transactionResponse.getTransaction());
    }

    private CompanyCredit createCompanyCredit(String userId, BigDecimal balance) {
        CompanyCredit companyCredit = new CompanyCredit(userId, balance);
        CreditsMapper creditsMapper = getSqlSession().getMapper(CreditsMapper.class);
        creditsMapper.insertCompanyCredit(companyCredit);
        getSqlSession().commit();
        return companyCredit;
    }

    private void assertThatCompanyCreditDecremented(CompanyCredit companyCredit, Transaction transaction) {
        BigDecimal balance = getSqlSession().getMapper(CreditsMapper.class).getCompanyCreditsBalance(companyCredit.getUserId());
        assert balance != null;
        assert companyCredit.getBalance().subtract(transaction.getAmount()).compareTo(balance) == 0;
        companyCredit.setBalance(balance);
    }

    private void assertThatCompanyCreditIncremented(CompanyCredit companyCredit, Transaction transaction) {
        BigDecimal balance = getSqlSession().getMapper(CreditsMapper.class).getCompanyCreditsBalance(companyCredit.getUserId());
        assert balance != null;
        assert balance.compareTo(companyCredit.getBalance().add(transaction.getAmount())) == 0;
        companyCredit.setBalance(balance);
    }

    private void testTransferBalanceRejection(String path) {
        performUnsuccessfulRequest(path, new TransferPayment(getSubtotal(), null, "1", "Invalid User", getTeamTwoId()));
    }
}