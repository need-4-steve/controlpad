package com.controlpad.pay_fac.reports;

import com.controlpad.pay_fac.ControllerTest;
import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import com.controlpad.pay_fac.report.custom.SalesTaxInfo;
import com.controlpad.pay_fac.test.TimeUtil;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.payment.PaymentMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.payment_file.PaymentFile;
import com.controlpad.payman_common.payment_file.PaymentFileMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.user_account.UserAccount;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.google.common.reflect.TypeToken;
import org.apache.ibatis.session.SqlSession;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import static com.controlpad.payman_common.transaction.TransactionType.*;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

public class SalesTaxTest extends ControllerTest{
    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    TimeUtil timeUtil;
    @Autowired
    IDUtil idUtil;

    @Test
    public void salesTaxTest(){
        TypeToken<PaginatedResponse<SalesTaxInfo>> typeToken = new TypeToken<PaginatedResponse<SalesTaxInfo>>() {};
        List<Transaction> transactionList = new ArrayList<>();
        Map<String, Payment> payoutMap = new HashMap<>();
        PaginatedResponse<SalesTaxInfo> response = new PaginatedResponse<>();
        TransactionBatch transactionBatch = new TransactionBatch();
        long count, page;

        try{
            /**
             @RequestParam(value = "startDate") String startDate,
             @RequestParam(value = "endDate") String endDate,
             @RequestParam(value = "q", required = false) String q,
             @RequestParam(value = "payerUserId", required = false) String payerUserId,
             @RequestParam(value = "payeeUserId", required = false) String payeeUserId,
             @RequestParam(value = "page") Long page,
             @RequestParam(value = "count") Integer count)
             */
            String startDate = timeUtil.getStartTime();
            loadData(transactionList, payoutMap, idUtil);
            String endDate = timeUtil.getEndTimeAfterOneSecond();

            for(int i=0; i<4; ++i){
                for(int j=0; j<20; ++j){
                    count = (1L + j);
                    page = (1L + i);
                    response = getGson().fromJson(getMockMvc().perform(get("/reports/sales-tax")
                            .param("startDate", startDate).param("endDate", endDate)
                            .param("page", page + "").param("count", count + "")
                            .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId()))
                            .andExpect(status().isOk())
                            .andReturn().getResponse().getContentAsString(), typeToken.getType());
                    System.out.println("Response: " + response);
                    assert response != null && response.getTotal() == transactionList.size();
                    response.getData().forEach(salesTaxInfo -> {
                        assert Double.valueOf(salesTaxInfo.getAmount()).equals(1.23D);
                    });
                    getTestUtil().checkPagination(response, page, count);
                }
            }
        }catch (Exception e){
            throw new RuntimeException(e);
        }
    }

    private void loadData(List<Transaction> transactionList, Map<String, Payment> payoutMap, IDUtil idUtil){
        SqlSession sqlSession = sqlSessionUtil.openSession(getTestUtil().getMockData().getTestClient().getId(), true);
        TransactionMapper transactionMapper = sqlSession.getMapper(TransactionMapper.class);
        EntryMapper entryMapper = sqlSession.getMapper(EntryMapper.class);
        PaymentMapper paymentMapper = sqlSession.getMapper(PaymentMapper.class);
        PaymentFileMapper paymentFileMapper = sqlSession.getMapper(PaymentFileMapper.class);
        UserAccountMapper userAccountMapper = sqlSession.getMapper(UserAccountMapper.class);
        UserBalancesMapper userBalancesMapper = sqlSession.getMapper(UserBalancesMapper.class);

        UserAccount userAccount = new UserAccount("MyPayeeID", "UserName", "324377516", "1234567", "checking", "Test Bank", true);
        userAccountMapper.insert(userAccount);

        PaymentFile paymentFile = new PaymentFile(null, "SaleTaxPayouFile", "TestDescription", null, null,
                new Money(100.00), BigDecimal.ZERO, BigDecimal.ZERO, new Money(50.00), 10, 10L, 10, getTeamTwoId());
        paymentFileMapper.insertPaymentFile(paymentFile);

        for(int i=0; i<3; ++i){
            transactionList.add(new Transaction(getIdUtil().generateId(), "MyPayeeID", "PayerID_" + i, getTeamTwoId(), null,
                    CREDIT_CARD_SALE.slug, new Money(3.00), new Money(1.23D), new Money(0.50D), "S", 1, null, null, null));
            transactionList.add(new Transaction(getIdUtil().generateId(), "MyPayeeID", "PayerID_" + i, getTeamTwoId(), null,
                    DEBIT_CARD_SALE.slug, new Money(3.00), new Money(1.23D), new Money(0.50D), "S", 1, null, null, null));
            transactionList.add(new Transaction(getIdUtil().generateId(), "MyPayeeID", "PayerID_" + i, getTeamTwoId(), null,
                    CHECK_SALE.slug, new Money(3.00), new Money(1.23D), new Money(0.50D), "S", 1, null, null, null));
            transactionList.add(new Transaction(getIdUtil().generateId(), "MyPayeeID", "PayerID_" + i, getTeamTwoId(), null,
                    CASH_SALE.slug, new Money(3.00), new Money(1.23D), new Money(0.50D), "S", 1, null, null, null));
        }

        transactionList.forEach(transaction -> {
            transactionMapper.insert(transaction);

            UserBalances balances = userBalancesMapper.find(transaction.getPayeeUserId(), transaction.getTeamId());
            if (balances == null) {
                balances = new UserBalances(transaction.getPayeeUserId(), transaction.getTeamId());
                userBalancesMapper.insert(balances);
            }

            Payment payment = new Payment(idUtil.generateId(), getTeamTwoId(), "MyPayeeID", null,
                    new Money(1.23), paymentFile.getId(), null, PaymentType.SALES_TAX.slug);
            paymentMapper.insert(payment);

            Entry paymentEntry = new Entry(balances.getId(), new Money(-1.23D), transaction.getId(), null, payment.getId(),
                    PaymentType.SALES_TAX.slug, true);
            payoutMap.put(transaction.getId(), payment);
            entryMapper.insert(paymentEntry);
        });
    }
}
