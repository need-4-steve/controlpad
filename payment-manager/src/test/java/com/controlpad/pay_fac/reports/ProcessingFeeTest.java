package com.controlpad.pay_fac.reports;


import com.controlpad.pay_fac.ControllerTest;
import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import com.controlpad.pay_fac.report.custom.ProcessingFeeInfo;
import com.controlpad.pay_fac.test.TimeUtil;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.payment_file.PaymentFile;
import com.controlpad.payman_common.payment_file.PaymentFileMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.google.common.reflect.TypeToken;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

import static com.controlpad.payman_common.transaction.TransactionType.*;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

public class ProcessingFeeTest extends ControllerTest{
    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    TimeUtil timeUtil;

    @Override
    public boolean isAutoCommit() {
        return true;
    }

    @Test
    public void processingFeeTest(){
        String payeeUserId = "processingFeeUser";
        TypeToken<PaginatedResponse<ProcessingFeeInfo>> typeToken = new TypeToken<PaginatedResponse<ProcessingFeeInfo>>(){};
        List<Transaction> transactionList = new ArrayList<>();
        PaginatedResponse<ProcessingFeeInfo> response = new PaginatedResponse<>();
        PaymentFile paymentFile = new PaymentFile();
        TransactionBatch transactionBatch = new TransactionBatch();
        long count, page;

        try {
            /**
             @RequestParam(value = "startDate", required = false) String startDate,
             @RequestParam(value = "endDate", required = false) String endDate,
             @RequestParam(value = "q", required = false) String q,
             @RequestParam(value = "paymentFileId", required = false) Long paymentFileId,
             @RequestParam(value = "payerUserId", required = false) String payerUserId,
             @RequestParam(value = "payeeUserId", required = false) String payeeUserId,
             @RequestParam(value = "page") Long page,
             @RequestParam(value = "count") Integer count
             */
            String startDate = timeUtil.getStartTime();
            loadData(transactionList, payeeUserId);
            String endDate = timeUtil.getEndTimeAfterOneSecond();

            for(int i=0; i<4; ++i){
                for(int j=0; j<20; ++j){
                    count = (1L + j);
                    page = (1L + i);
                    response = getGson().fromJson(getMockMvc().perform(get("/reports/processing-fees")
                            .param("startDate", startDate).param("endDate", endDate + "")
                            .param("payeeUserId", payeeUserId)
                            .param("page", page + "").param("count", count + "")
                            .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId()))
                            .andExpect(status().isOk())
                            .andReturn().getResponse().getContentAsString(), typeToken.getType());
                    assert response != null && response.getTotal() == transactionList.size();
                    System.out.println("Response: " + response);
                    response.getData().forEach(processingFeeInfo -> {
                        assert processingFeeInfo.getAmount().compareTo(new Money(71D)) == 0 ||
                                processingFeeInfo.getAmount().compareTo(new Money(73D)) == 0;
                        assert processingFeeInfo.getConsignment().compareTo(new Money(-1.11D)) == 0;
                        assert processingFeeInfo.getProcessing().compareTo(new Money(-3.33D)) == 0;
                        assert processingFeeInfo.getNetAmount().compareTo(new Money(65.86D)) == 0;
                        assert processingFeeInfo.getSalesTax().compareTo(new Money(-0.7D)) == 0;
                    });

                    getTestUtil().checkPagination(response, page, count);
                }
            }

        }catch (Exception e){
            throw new RuntimeException(e);
        }

    }

    private void loadData(List<Transaction> transactionList, String payeeUserId){
        PaymentFileMapper paymentFileMapper = getSqlSession().getMapper(PaymentFileMapper.class);
        TransactionBatchMapper transactionBatchMapper = getSqlSession().getMapper(TransactionBatchMapper.class);
        TransactionMapper transactionMapper = getSqlSession().getMapper(TransactionMapper.class);
        EntryMapper entryMapper = getSqlSession().getMapper(EntryMapper.class);
        UserBalancesMapper userBalancesMapper = getSqlSession().getMapper(UserBalancesMapper.class);

        PaymentFile paymentFile = new PaymentFile(null, "Processing", "TestDescription",
                null, null, new Money(100.00), BigDecimal.ZERO, BigDecimal.ZERO, new Money(50.00),
                10, 10L, 10, getTeamTwoId());
        paymentFileMapper.insertPaymentFile(paymentFile);
        TransactionBatch transactionBatch = new TransactionBatch(2L, "110", 2, 0, null, paymentFile.getId());
        transactionBatchMapper.insert(transactionBatch);

        transactionList.add(new Transaction(getIdUtil().generateId(), payeeUserId, getTeamTwoId(), CREDIT_CARD_SALE.slug,
                new Money(71.00), new Money(0.70D), "S", 1, transactionBatch.getId(), 2L));
        transactionList.add(new Transaction(getIdUtil().generateId(), payeeUserId, getTeamTwoId(), DEBIT_CARD_SALE.slug,
                new Money(71.00), new Money(0.70D), "S", 1, transactionBatch.getId(), 2L));
        transactionList.add(new Transaction(getIdUtil().generateId(), payeeUserId, getTeamTwoId(), CHECK_SALE.slug,
                new Money(71.00), new Money(0.70D),  "S", 1, transactionBatch.getId(), 2L));

        transactionList.forEach(transaction -> {
            transactionMapper.insert(transaction);

            UserBalances balances = userBalancesMapper.find(transaction.getPayeeUserId(), transaction.getTeamId());
            if (balances == null) {
                balances = new UserBalances(transaction.getPayeeUserId(), transaction.getTeamId());
                userBalancesMapper.insert(balances);
            }

            entryMapper.insert(new Entry(balances.getId(), transaction.getAmount(), transaction.getId(), null,
                    null, PaymentType.MERCHANT.slug, true));
            entryMapper.insert(new Entry(balances.getId(), new Money(-1.11D), transaction.getId(), null,
                    null, PaymentType.FEE.slug, false));
            entryMapper.insert(new Entry(balances.getId(), new Money(-1.11D), transaction.getId(), 3L,
                    null, PaymentType.FEE.slug, false));
            entryMapper.insert(new Entry(balances.getId(), new Money(-0.7D), transaction.getId(), null,
                    null, PaymentType.SALES_TAX.slug, false));
            entryMapper.insert(new Entry(balances.getId(), new Money(-1.11D), transaction.getId(), null,
                    null, PaymentType.FEE.slug, false));
            entryMapper.insert(new Entry(balances.getId(), new Money(-1.11D), transaction.getId(), null,
                    null, PaymentType.CONSIGNMENT.slug, false));
        });
        getSqlSession().close();
    }
}
