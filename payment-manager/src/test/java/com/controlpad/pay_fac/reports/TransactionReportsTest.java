/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.reports;

import com.controlpad.pay_fac.ControllerTest;
import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import com.controlpad.pay_fac.test.TimeUtil;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.payment_file.PaymentFile;
import com.controlpad.payman_common.payment_file.PaymentFileMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.google.gson.reflect.TypeToken;
import org.apache.ibatis.session.SqlSession;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

import static com.controlpad.payman_common.transaction.TransactionType.*;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

public class TransactionReportsTest extends ControllerTest {

    @Autowired
    ReportDummyDataUtil reportDummyDataUtil;
    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    TimeUtil timeUtil;

    @Test
    public void transactionReportTest() {
        Transaction dummyTransaction = reportDummyDataUtil.getFilteredTransactions().get(1);
        Transaction resultTransaction;
        try {
            resultTransaction = getGson().fromJson(getMockMvc().perform(get("/reports/transactions/" + dummyTransaction.getId())
                    .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId()))
                    .andExpect(status().isOk())
                    .andReturn().getResponse().getContentAsString(), Transaction.class);
        } catch (Exception e) {
            throw new RuntimeException(e);
        }

        assert dummyTransaction.getAmount().equals(resultTransaction.getAmount());
        assert dummyTransaction.getEntries().size() == resultTransaction.getEntries().size();
    }

    @Test
    public void transactionListReportTest() {
        TypeToken<PaginatedResponse<Transaction>> typeToken = new TypeToken<PaginatedResponse<Transaction>>(){};
        long count, page;
        List<Transaction> transactionList = new ArrayList<>();
        PaginatedResponse<Transaction> response;
        try {
            String startDate = timeUtil.getStartTime();
            loadData(transactionList);
            String endDate = timeUtil.getEndTimeAfterOneSecond();
            //System.out.println("Start Date: " + startDate);
            //System.out.println("End Date: " + endDate);
            for(int i=0; i<4; i++) {
                for(int j=0; j<50; j++){
                    count = (1L + j);
                    page = (1L + i);
                    response = getGson().fromJson(getMockMvc().perform(get("/reports/transactions/")
                            .param("startDate", startDate).param("endDate", endDate + "")
                            .param("page", page + "").param("count", count + "")
                            .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId()))
                            .andExpect(status().isOk())
                            .andReturn().getResponse().getContentAsString(), typeToken.getType());
                    System.out.println("Transaction List: " + response.toString());
                    assert response != null && response.getTotal() == transactionList.size();

                    getTestUtil().checkPagination(response, page, count);
                }
            }
        }catch (Exception e){
            throw new RuntimeException(e);
        }
    }

    private void loadData(List<Transaction> transactionList){
        List<Payment> payments = new ArrayList<>();

        SqlSession sqlSession = sqlSessionUtil.openSession(getTestUtil().getMockData().getTestClient().getId(), true);
        PaymentFileMapper paymentFileMapper = sqlSession.getMapper(PaymentFileMapper.class);
        TransactionMapper transactionMapper = sqlSession.getMapper(TransactionMapper.class);
        TransactionBatchMapper transactionBatchMapper = sqlSession.getMapper(TransactionBatchMapper.class);

        PaymentFile paymentFile = new PaymentFile(null, "TestPaymentFile", "TestDescription", null, null,
                new Money(140.00), BigDecimal.ZERO, BigDecimal.ZERO, new Money(70.00), 10, 10L, 30, getTeamTwoId());
        paymentFileMapper.insertPaymentFile(paymentFile);

        TransactionBatch transactionBatch = new TransactionBatch(2L, "110", 2, 0, null, paymentFile.getId());
        transactionBatchMapper.insert(transactionBatch);

        for(int i=0; i<10; i++){
            transactionList.add(new Transaction(getIdUtil().generateId(), "PayeeID_" + i, "PayerID_" + i, getTeamTwoId(), null,
                    CHECK_SALE.slug, new Money(3.00), new Money(0.70D), new Money(0.50D), "S", 1, null, null, null));
            transactionList.add(new Transaction(getIdUtil().generateId(), "PayeeID_" + i, "PayerID_" + i, getTeamTwoId(), null,
                    CREDIT_CARD_SALE.slug, new Money(7.00), new Money(0.70D), new Money(0.50D), "S", 1, null, null, null));
            transactionList.add(new Transaction(getIdUtil().generateId(), "PayeeID_" + i, "PayerID_" + i, getTeamTwoId(), null,
                    DEBIT_CARD_SALE.slug, new Money(11.00), new Money(0.70D), new Money(0.50D), "S", 1, null, null, null));
        }

        transactionList.forEach((transaction) -> {
            transaction.setBatchId(transactionBatch.getId());
            transactionMapper.insert(transaction);
            //System.out.println("New Transaction: " + transaction);
        });
    }
}
