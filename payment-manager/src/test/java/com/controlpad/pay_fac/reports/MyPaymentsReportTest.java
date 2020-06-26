package com.controlpad.pay_fac.reports;

import com.controlpad.pay_fac.ControllerTest;
import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import com.controlpad.pay_fac.report.custom.MyPayment;
import com.controlpad.pay_fac.test.TimeUtil;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.payment_file.PaymentFile;
import com.controlpad.payman_common.payment_file.PaymentFileMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.google.common.reflect.TypeToken;
import org.apache.ibatis.session.SqlSession;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

import static com.controlpad.payman_common.transaction.TransactionType.*;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

public class MyPaymentsReportTest extends ControllerTest {
    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    TimeUtil timeUtil;

    @Test
    public void myPaymentReportTest(){
        TypeToken<PaginatedResponse<MyPayment>> typeToken = new TypeToken<PaginatedResponse<MyPayment>>(){};
        List<Transaction> transactionList = new ArrayList<>();
        PaginatedResponse<MyPayment> response = new PaginatedResponse<>();
        long count, page;

        try{
            /**
             @RequestParam(value = "startDate") String startDate,
             @RequestParam(value = "endDate") String endDate,
             @RequestParam(value = "userId") String userId,
             @RequestParam(value = "page") Long page,
             @RequestParam(value = "count") Integer count
             */
            String startDate = timeUtil.getStartTime();
            loadData(transactionList);
            String endDate = timeUtil.getEndTimeAfterOneSecond();
            for(int i=0; i<4; ++i) {
                for(int j=0; j<20; ++j){
                    count = (1L + j);
                    page = (1L + i);
                    response = getGson().fromJson(getMockMvc().perform(get("/reports/my-payments/")
                            .param("startDate", startDate).param("endDate", endDate + "").param("userId", "MyPayeeID")
                            .param("page", page + "").param("count", count + "")
                            .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId()))
                            .andExpect(status().isOk())
                            .andReturn().getResponse().getContentAsString(), typeToken.getType());
                    assert response != null && response.getTotal() == transactionList.size();
                    response.getData().forEach(myPayment -> {
                        assert myPayment.getAmount().compareTo(new Money(31D)) == 0 ||
                                myPayment.getAmount().compareTo(new Money(37D)) == 0;
                    });

                    getTestUtil().checkPagination(response, page, count);
                }
            }
        }catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    private void loadData(List<Transaction> transactionList){
        SqlSession sqlSession = sqlSessionUtil.openSession(getTestUtil().getMockData().getTestClient().getId(), true);
        PaymentFileMapper paymentFileMapper = sqlSession.getMapper(PaymentFileMapper.class);
        TransactionBatchMapper transactionBatchMapper = sqlSession.getMapper(TransactionBatchMapper.class);
        TransactionMapper transactionMapper = sqlSession.getMapper(TransactionMapper.class);

        PaymentFile paymentFile = new PaymentFile(null, "MyTestPaymentFile", "TestDescription",
                null, null, new Money(100.00), BigDecimal.ZERO, BigDecimal.ZERO, new Money(50.00), 10, 10L, 10, getTeamTwoId());
        paymentFileMapper.insertPaymentFile(paymentFile);
        TransactionBatch transactionBatch = new TransactionBatch(2L, "110", 2, 0, null, paymentFile.getId());
        transactionBatchMapper.insert(transactionBatch);

        for(int i=0; i<5; i++){
            transactionList.add(new Transaction(getIdUtil().generateId(), "MyPayeeID", "PayerID_" + i,getTeamTwoId(), null,
                    CREDIT_CARD_SALE.slug,new Money(31.00), new Money(0.70D), new Money(0.50D), "S", 1, null,
                    null, null));
            transactionList.add(new Transaction(getIdUtil().generateId(), "MyPayeeID", "PayerID_" + i, getTeamTwoId(), null,
                    DEBIT_CARD_SALE.slug, new Money(31.00), new Money(0.70D), new Money(0.50D), "S", 1, null,
                    null, null));
            transactionList.add(new Transaction(getIdUtil().generateId(), "MyPayeeID", "PayerID_" + i, getTeamTwoId(), null,
                    CHECK_SALE.slug, new Money(37.00), new Money(0.70D), new Money(0.50D), "S", 1, null,
                    null, null));

        }

        for(int i=0; i<transactionList.size(); ++i){
            transactionList.get(i).setBatchId(transactionBatch.getId());
            transactionMapper.insert(transactionList.get(i));
        }
    }
}
