/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.transaction;

import com.controlpad.pay_fac.gateway.GatewayUtil;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction.TransactionResult;
import com.controlpad.payman_common.transaction.TransactionType;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;

import java.math.BigDecimal;
import java.math.RoundingMode;
import java.util.Locale;

import static org.hamcrest.Matchers.is;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.jsonPath;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

public class RefundPaymentTest extends PaymentControllerTest {

    private Transaction cashTransaction;
    private Transaction voidCardTransaction;
    private Transaction pendingCardTransaction;
    private Transaction ewalletSaleTransaction;
    private Transaction settledPendingCardTransaction;
    private Transaction settledTransaction;
    private Transaction brokeEwalletTransaction;
    private Transaction missingEwalletTransaction;

    private Transaction invalidTransaction;

    private UserBalances sellerBalances;
    private UserBalances buyerBalances;
    private UserBalances brokeBalances;

    private GatewayConnection gatewayConnection;

    @Autowired
    GatewayUtil gatewayUtil;

    @Override
    public boolean isAutoCommit() {
        return true;
    }

    @Test
    public void refundCheck() throws Exception {
        String pathFormat = "/transactions/%s/%s";
        loadDummyData();

        // Check amount null
        TransactionRefund badRefund = new TransactionRefund(null);
        performBadPostRequest(getPath(cashTransaction, "refund"), badRefund);
        // Check amount to low
        badRefund = new TransactionRefund(new Money(0));
        performBadPostRequest(getPath(cashTransaction, "refund"), badRefund);
        // Check bad transaction id
        badRefund = new TransactionRefund(new Money(10D));
        performBadPostRequest(getPath(invalidTransaction, "refund"), badRefund);
        // Check refund type
        performBadPostRequest(getPath(cashTransaction, "not-a-refund"), badRefund);

        // Test refund void on pending transaction
        TransactionRefund goodRefund = new TransactionRefund(voidCardTransaction.getAmount());
        goodRefund.setType("refund");
        assertCodeForRequest(voidCardTransaction, goodRefund, TransactionResult.Success.getResultCode(), true);

        // Test refund partial on pending transaction
        goodRefund = new TransactionRefund(pendingCardTransaction.getAmount().divide(new Money(2), 2, RoundingMode.HALF_UP));
        goodRefund.setType("refund");
        assertCodeForRequest(pendingCardTransaction, goodRefund, TransactionResult.Success.getResultCode(), true);

        // Transaction is settled but still listed as pending locally, should work
        goodRefund = new TransactionRefund(settledPendingCardTransaction.getAmount());
        goodRefund.setType("refund");
        assertCodeForRequest(settledPendingCardTransaction, goodRefund, TransactionResult.Success.getResultCode(), true);

        // Cash type works
        goodRefund = new TransactionRefund(cashTransaction.getAmount().divide(new Money(2), 2, RoundingMode.HALF_UP));
        goodRefund.setType("cash-refund");
        assertCodeForRequest(cashTransaction, goodRefund, TransactionResult.Success.getResultCode(), true);
        // TODO assert that charges were created

        // Check refund amount to high from partial
        badRefund = new TransactionRefund(cashTransaction.getAmount());
        badRefund.setType("cash-refund");
        assertCodeForRequest(cashTransaction, badRefund, TransactionResult.Maximum_Limit.getResultCode(), false);

        // Settled transaction works, half twice
        goodRefund = new TransactionRefund(settledTransaction.getAmount().divide(new Money(2), 2, RoundingMode.HALF_UP));
        goodRefund.setType("refund");
        assertCodeForRequest(settledTransaction, goodRefund, TransactionResult.Success.getResultCode(), true);
        assertCodeForRequest(settledTransaction, goodRefund, TransactionResult.Success.getResultCode(), true);

        // Ewallet sale refund
        goodRefund = new TransactionRefund(ewalletSaleTransaction.getAmount());
        goodRefund.setType("refund");
        assertCodeForRequest(ewalletSaleTransaction, goodRefund, TransactionResult.Success.getResultCode(), true);
        assertThatEwalletDecremented(sellerBalances, goodRefund.getTotal());
        assertThatEwalletIncremented(buyerBalances, goodRefund.getTotal());
    }

    private void loadDummyData() {
        TransactionMapper transactionMapper = getSqlSession().getMapper(TransactionMapper.class);
        UserBalancesMapper userBalancesMapper = getSqlSession().getMapper(UserBalancesMapper.class);

        sellerBalances = new UserBalances("RefundSeller", getTeamTwoId(), BigDecimal.ZERO, new Money(200D), BigDecimal.ZERO);
        buyerBalances = new UserBalances("RefundBuyer", getTeamTwoId(), BigDecimal.ZERO, new Money(200D), BigDecimal.ZERO);
        brokeBalances = new UserBalances("BrokeSeller", getTeamTwoId(), BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO);
        userBalancesMapper.insert(sellerBalances);
        userBalancesMapper.insert(buyerBalances);
        userBalancesMapper.insert(brokeBalances);

        cashTransaction = new Transaction(getIdUtil().generateId(), sellerBalances.getUserId(), null, getTeamTwoId(), null,
                TransactionType.CASH_SALE.slug, new Money(16.23), new Money(1.23), null, "S", 1, null,
                null, null);
        TransactionUtil.insertTransaction(getSqlSession(), cashTransaction, getIdUtil(), null);


        gatewayConnection = gatewayUtil.selectGatewayConnection(getSqlSession(), null, getTeamTwoId(), null, null, null, null, null);

        voidCardTransaction = new Transaction(getIdUtil().generateId(), sellerBalances.getUserId(), null, getTeamTwoId(), null, TransactionType.CREDIT_CARD_SALE.slug,
                new Money(10.60), new Money(0.60), null, "P", 1, gatewayConnection.getId(), null, null);
        TransactionUtil.insertTransaction(getSqlSession(), voidCardTransaction, getIdUtil(), null);
        pendingCardTransaction = new Transaction(getIdUtil().generateId(), sellerBalances.getUserId(), null, getTeamTwoId(), null, TransactionType.CREDIT_CARD_SALE.slug,
                new Money(16.2), new Money(1.2), null, "P", 1, gatewayConnection.getId(), null, null);
        TransactionUtil.insertTransaction(getSqlSession(), pendingCardTransaction, getIdUtil(), null);
        settledPendingCardTransaction = new Transaction(getIdUtil().generateId(), sellerBalances.getUserId(), null, getTeamTwoId(), null, TransactionType.CREDIT_CARD_SALE.slug,
                new Money(21.8), new Money(1.8), null, "S", 1, gatewayConnection.getId(), null, null);
        TransactionUtil.insertTransaction(getSqlSession(), settledPendingCardTransaction, getIdUtil(), null);
        settledTransaction = new Transaction(getIdUtil().generateId(), sellerBalances.getUserId(), null, getTeamTwoId(), null, TransactionType.CREDIT_CARD_SALE.slug,
                new Money(27.24), new Money(2.4), null, "S", 1, gatewayConnection.getId(), null, null);
        TransactionUtil.insertTransaction(getSqlSession(), settledTransaction, getIdUtil(), null);

        ewalletSaleTransaction = new Transaction(getIdUtil().generateId(), sellerBalances.getUserId(), buyerBalances.getUserId(), getTeamTwoId(), null, TransactionType.E_WALLET_SALE.slug,
                new Money(12.64), new Money(0.8), null, "S", 1, null, null, null);
        TransactionUtil.insertTransaction(getSqlSession(), ewalletSaleTransaction, getIdUtil(), null);
        brokeEwalletTransaction = new Transaction(getIdUtil().generateId(), "BrokeSeller", "RefundBuyer", getTeamTwoId(), null, TransactionType.E_WALLET_SALE.slug,
                new Money(106.00), new Money(6.00), null, "S", 1, null, null, null);
        TransactionUtil.insertTransaction(getSqlSession(), brokeEwalletTransaction, getIdUtil(), null);
        missingEwalletTransaction = new Transaction(getIdUtil().generateId(), "InvalidRefundUser", "RefundBuyer", getTeamTwoId(), null, TransactionType.E_WALLET_SALE.slug,
                new Money(34.98), new Money(1.98), null, "S", 1, null, null, null);
        TransactionUtil.insertTransaction(getSqlSession(), missingEwalletTransaction, getIdUtil(), null);

        invalidTransaction = new Transaction();
        invalidTransaction.setId("derpderp");

        settledTransaction.setStatusCode("S");
        transactionMapper.updateTransactionStatus(settledTransaction);
    }

    private String getPath(Transaction transaction, String refundType) {
        return String.format(Locale.US, "/transactions/%s/%s", transaction.getId(), refundType);
    }

    private void assertCodeForRequest(Transaction transaction, TransactionRefund refund, int code, boolean shouldSuccess) {
        try {
        getMockMvc().perform(post(getPath(transaction, refund.getType()))
                .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId())
                .contentType(getJsonContentType())
                .content(getGson().toJson(refund)))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$['success']", is(shouldSuccess)))
                .andExpect(jsonPath("$['statusCode']", is(code)));
        } catch (Exception e) {
            e.printStackTrace();
            throw new RuntimeException(e);
        }
    }
}