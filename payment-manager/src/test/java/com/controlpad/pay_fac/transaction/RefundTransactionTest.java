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
import org.springframework.test.web.servlet.ResultActions;

import java.math.BigDecimal;
import java.math.RoundingMode;

import static org.hamcrest.Matchers.is;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.jsonPath;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

public class RefundTransactionTest extends TransactionControllerTest {

    private final String path = "/transactions";
    private final String payerId = "RefundPayer";
    private final String payeeId = "RefundPayee";
    private final String brokeUserId = "BrokeUser";

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

    public void validationTest() throws Exception {
        // Check amount null
        Transaction badRefund = new Transaction(payeeId, payerId, getTeamTwoId(), TransactionType.REFUND.slug,
                null, null, null, null);
        badRefund.setForTxnId(cashTransaction.getId());
        performBadPostRequest(path, badRefund);

        // Check amount to low
        badRefund = new Transaction(payeeId, payerId, getTeamTwoId(), TransactionType.REFUND.slug,
                new Money(0), null, null, null);
        badRefund.setForTxnId(cashTransaction.getId());
        performBadPostRequest(path, badRefund);

        // Check bad transaction id
        badRefund = new Transaction(payeeId, payerId, getTeamTwoId(), TransactionType.REFUND.slug,
                new Money(5), null, null, null);
        badRefund.setForTxnId(invalidTransaction.getId());
        performBadPostRequest(path, badRefund);

        // Check refund type
        badRefund = new Transaction(payeeId, payerId, getTeamTwoId(), "not-a-refund",
                new Money(5), null, null, null);
        badRefund.setForTxnId(cashTransaction.getId());
        performBadPostRequest(path, badRefund);
    }

    @Test
    public void refundCheck() throws Exception {
        loadDummyData();
        validationTest();

        // Test refund void on pending transaction
        Transaction goodRefund = new Transaction(voidCardTransaction.getAmount(), voidCardTransaction.getSalesTax());
        goodRefund.setTransactionType(TransactionType.REFUND.slug);
        goodRefund.setForTxnId(voidCardTransaction.getId());
        assertCodeForRequest(goodRefund, TransactionResult.Success.getResultCode());

        // Test refund partial on pending transaction
        goodRefund = new Transaction(pendingCardTransaction.getAmount().divide(new Money(2), 2, RoundingMode.HALF_UP), null);
        goodRefund.setTransactionType("refund");
        goodRefund.setForTxnId(pendingCardTransaction.getId());
        assertCodeForRequest(goodRefund, TransactionResult.Success.getResultCode());

        // Transaction is settled but still listed as pending locally, should work
        goodRefund = new Transaction(settledPendingCardTransaction.getAmount(), null);
        goodRefund.setTransactionType("refund");
        goodRefund.setForTxnId(settledPendingCardTransaction.getId());
        assertCodeForRequest(goodRefund, TransactionResult.Success.getResultCode());

        // Cash type works
        goodRefund = new Transaction(cashTransaction.getAmount().divide(new Money(2), 2, RoundingMode.HALF_UP), null);
        goodRefund.setTransactionType("cash-refund");
        goodRefund.setForTxnId(cashTransaction.getId());
        assertCodeForRequest(goodRefund, TransactionResult.Success.getResultCode());
        // TODO assert that charges were created

        // Check refund amount to high from partial
        Transaction badRefund = new Transaction(cashTransaction.getAmount(), null);
        badRefund.setTransactionType("cash-refund");
        badRefund.setForTxnId(cashTransaction.getId());
        assertCodeForRequest(badRefund, TransactionResult.Maximum_Limit.getResultCode());

        // Settled transaction works, half twice
        goodRefund = new Transaction(settledTransaction.getAmount().divide(new Money(2), 2, RoundingMode.HALF_UP), null);
        goodRefund.setTransactionType("refund");
        goodRefund.setForTxnId(settledTransaction.getId());
        assertCodeForRequest(goodRefund, TransactionResult.Success.getResultCode());
        assertCodeForRequest(goodRefund, TransactionResult.Success.getResultCode());

        // Ewallet sale refund
        goodRefund = new Transaction(ewalletSaleTransaction.getAmount(), null);
        goodRefund.setTransactionType("refund");
        goodRefund.setForTxnId(ewalletSaleTransaction.getId());
        assertCodeForRequest(goodRefund, TransactionResult.Success.getResultCode());
        assertThatEwalletDecremented(sellerBalances, goodRefund.getAmount());
        assertThatEwalletIncremented(buyerBalances, goodRefund.getAmount());
    }

    private void loadDummyData() {
        TransactionMapper transactionMapper = getSqlSession().getMapper(TransactionMapper.class);
        UserBalancesMapper userBalancesMapper = getSqlSession().getMapper(UserBalancesMapper.class);

        sellerBalances = new UserBalances(payeeId, getTeamTwoId(), BigDecimal.ZERO, new Money(200D), BigDecimal.ZERO);
        buyerBalances = new UserBalances(payerId, getTeamTwoId(), BigDecimal.ZERO, new Money(200D), BigDecimal.ZERO);
        brokeBalances = new UserBalances(brokeUserId, getTeamTwoId(), BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO);
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
        brokeEwalletTransaction = new Transaction(getIdUtil().generateId(), brokeUserId, payerId, getTeamTwoId(), null, TransactionType.E_WALLET_SALE.slug,
                new Money(106.00), new Money(6.00), null, "S", 1, null, null, null);
        TransactionUtil.insertTransaction(getSqlSession(), brokeEwalletTransaction, getIdUtil(), null);
        missingEwalletTransaction = new Transaction(getIdUtil().generateId(), "InvalidRefundUser", payerId, getTeamTwoId(), null, TransactionType.E_WALLET_SALE.slug,
                new Money(34.98), new Money(1.98), null, "S", 1, null, null, null);
        TransactionUtil.insertTransaction(getSqlSession(), missingEwalletTransaction, getIdUtil(), null);

        invalidTransaction = new Transaction();
        invalidTransaction.setId("derpderp");

        settledTransaction.setStatusCode("S");
        transactionMapper.updateTransactionStatus(settledTransaction);
    }

    private void assertCodeForRequest(Transaction refund, int code) {
        try {
            ResultActions actions = getMockMvc().perform(post(path)
                    .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId())
                    .contentType(getJsonContentType())
                    .content(getGson().toJson(refund)))
                    .andExpect(status().isOk())
                    .andExpect(jsonPath("$['resultCode']", is(code)));
        } catch (Exception e) {
            e.printStackTrace();
            throw new RuntimeException(e);
        }
    }
}
