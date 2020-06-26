/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.transaction;

import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.transaction.Payment;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.transaction.TransferPayment;
import com.controlpad.payman_common.user_account.UserAccount;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import org.junit.Test;

import java.math.BigDecimal;

public class EWalletTransactionTest extends PaymentControllerTest {

    @Override
    public boolean isAutoCommit() {
        return true;
    }

    @Test
    public void eWalletSaleTest() {
        String path = "/transactions/sale/e-wallet";
        String payerUserId = "EWallet user 61";

        UserBalances userBalances = createEwalletForUser(payerUserId, getTeamTwoId(), new Money(1000D));
        TransactionResponse transactionResponse = paymentTest(path, new Payment(payerUserId, "62", getTeamTwoId(), "Customer",
                getTax(), BigDecimal.ZERO, getSubtotal(), null), TransactionType.E_WALLET_SALE);

        assertThatEwalletDecremented(userBalances, transactionResponse.getTransaction());

        testSaleBalanceRejection(path);
    }

    @Test
    public void eWalletSubTest() {
        String path = "/transactions/sub/e-wallet";
        String payerUserId = "EWallet user 62";

        UserBalances userBalances = createEwalletForUser(payerUserId, getTeamOneId(), new Money(1000D));
        TransactionResponse transactionResponse = paymentTest(path, new Payment(payerUserId, "Company", getTeamOneId(),
                "Customer", BigDecimal.ZERO, BigDecimal.ZERO, getSubtotal(), null), TransactionType.E_WALLET_SUB);

        assertThatEwalletDecremented(userBalances, transactionResponse.getTransaction());

        testSaleBalanceRejection(path);
    }

    @Test
    public void eWalletTransfer() {
        String path = "/transactions/transfer/e-wallet";
        String payerUserId = "EWallet transfer payer";
        String payeeUserId = "EWallet transfer payee";

        UserBalances payeeBalances = createEwalletForUser(payeeUserId, getTeamTwoId(), BigDecimal.ZERO);
        UserBalances payerBalances = createEwalletForUser(payerUserId, getTeamTwoId(), new Money(1000D));
        TransactionResponse transactionResponse = paymentTest(path, new Payment(payerUserId, payeeUserId, getTeamTwoId(),
                null, BigDecimal.ZERO, BigDecimal.ZERO, getSubtotal(), "Transfer money"), TransactionType.E_WALLET_TRANSFER);

        assertThatEwalletDecremented(payerBalances, transactionResponse.getTransaction());
        assertThatEwalletIncremented(payeeBalances, transactionResponse.getTransaction());

        testTransferBalanceRejection(path);
    }

    @Test
    public void eWalletCredit() {
        String path = "/transactions/credit/e-wallet";
        String payeeUserId = "eWalletCreditUser";

        UserBalances payeeBalances = createEwalletForUser(payeeUserId, getTeamTwoId(), BigDecimal.ZERO);
        TransactionResponse transactionResponse = internalPaymentTest(path,
                new TransferPayment(getSubtotal(), "Credit e-wallet", payeeUserId, "Company", getTeamTwoId()),
                TransactionType.E_WALLET_CREDIT, true);

        assertThatEwalletIncremented(payeeBalances, transactionResponse.getTransaction());
    }

    @Test
    public void eWalletDebit() {
        String path = "/transactions/debit/e-wallet";
        String payeeUserId = "eWalletDebitUser";

        UserBalances payeeBalances = createEwalletForUser(payeeUserId, getTeamTwoId(), new Money(1000D));
        TransactionResponse transactionResponse = internalPaymentTest(path,
                new TransferPayment(getSubtotal(), "Check sent as payment", payeeUserId, "Company", getTeamTwoId()),
                TransactionType.E_WALLET_DEBIT, true);

        assertThatEwalletDecremented(payeeBalances, transactionResponse.getTransaction());
    }

    @Test
    public void eWalletWithdraw() {
        String path = "/transactions/withdraw/e-wallet";
        String userId = "EWallet user 66";

        // requires a valid account to create withdraw
        getSqlSession().getMapper(UserAccountMapper.class).insert(new UserAccount(userId, "User 66", "666666666", "123456789", "checking", "Some Bank", true));

        UserBalances balances = createEwalletForUser(userId, getTeamTwoId(), new Money(1000D));

        TransactionResponse transactionResponse = paymentTest(path,
                new Payment(userId, userId, getTeamTwoId(), null, BigDecimal.ZERO, BigDecimal.ZERO, getSubtotal(), "Withdraw e-wallet"),
                TransactionType.E_WALLET_WITHDRAW);

        assertThatEwalletDecremented(balances, transactionResponse.getTransaction());

        performUnsuccessfulRequest(path, new Payment("Invalid User", "Invalid User", getTeamTwoId(), null, BigDecimal.ZERO,
                BigDecimal.ZERO, getSubtotal(), null));
    }

    private TransactionResponse paymentTest(String path, Payment payment, TransactionType transactionType) {
        TransactionResponse transactionResponse = performPostRequest(path, payment, false);

        assertTransactionValidForSale(transactionResponse.getTransaction(), payment, transactionType);

        return transactionResponse;
    }

    private UserBalances createEwalletForUser(String userId, String teamId, BigDecimal balance) {
        UserBalancesMapper userBalancesMapper = getSqlSession().getMapper(UserBalancesMapper.class);
        UserBalances balances = new UserBalances(userId, teamId, BigDecimal.ZERO, balance, balance);
        userBalancesMapper.insert(balances);
        userBalancesMapper.add(balances.getId(), balances.getSalesTax(), balances.getEWallet(), balances.getTransaction());
        return balances;
    }

    private void testSaleBalanceRejection(String path) {
        performUnsuccessfulRequest(path, new Payment("Invalid User", "1", getTeamTwoId(), "Customer",
                getTax(), BigDecimal.ZERO, getSubtotal().add(getTax()), null));
    }

    private void testTransferBalanceRejection(String path) {
        performUnsuccessfulRequest(path, new Payment("Invalid User", "1", getTeamTwoId(),
                null, BigDecimal.ZERO, BigDecimal.ZERO, getSubtotal(), null));
    }

    private void testTransferBalanceRejection(String path, TransferPayment transferPayment) {
        performUnsuccessfulRequest(path, transferPayment);
    }


}
