/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.reports;

import com.controlpad.pay_fac.ControllerTest;
import com.controlpad.pay_fac.report.Totals;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import org.apache.commons.lang3.StringUtils;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;

import java.math.BigDecimal;

import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

public class TotalsReportTest extends ControllerTest {

    @Autowired
    ReportDummyDataUtil reportDummyDataUtil;

    @Test
    public void openSalesReportTest() {
        Totals totals = getReport("/reports/open-sales?userId=" + reportDummyDataUtil.getPayeeUserId());
        System.out.println("Totals: " + totals);

        BigDecimal transactionTotal = BigDecimal.ZERO;
        for (Transaction transaction : reportDummyDataUtil.getUnpaidTransactions()) {
            transactionTotal = transactionTotal.add(transaction.getAmount());
        }

        assert totals.getCount() == reportDummyDataUtil.getUnpaidTransactions().size();
        assert totals.getTotal().equals(transactionTotal);
    }

    @Test
    public void openTaxReportTest() {
        Totals totals = getReport("/reports/open-tax?userId=" + reportDummyDataUtil.getPayeeUserId());
        System.out.println("Totals: " + totals);

        BigDecimal unpaidTaxTotal = BigDecimal.ZERO;
        int unpaidTaxCount = 0;
        for(TransactionCharge transactionCharge : reportDummyDataUtil.getUnpaidTransactionCharges()) {
            if (StringUtils.equals(transactionCharge.getType(), "sales-tax") && !transactionCharge.getProcessed()) {
                System.out.println("Adding unpaid transaction charge: " + transactionCharge);
                unpaidTaxTotal = unpaidTaxTotal.add(transactionCharge.getAmount());
                unpaidTaxCount++;
            }
        }

        System.out.println("calculated dummy totals: " + unpaidTaxCount + ", " + unpaidTaxTotal);
        assert totals.getCount() == unpaidTaxCount;
        assert totals.getTotal().compareTo(unpaidTaxTotal) == 0;
    }

    private Totals getReport(String path) {
        try {
            return getGson().fromJson(getMockMvc().perform(get(path)
                    .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId()))
                    .andExpect(status().isOk())
                    .andReturn().getResponse().getContentAsString(), Totals.class);
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }
}