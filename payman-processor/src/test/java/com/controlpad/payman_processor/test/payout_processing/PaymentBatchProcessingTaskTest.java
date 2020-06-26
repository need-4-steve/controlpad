package com.controlpad.payman_processor.test.payout_processing;

import com.controlpad.payman_common.merchant.Merchant;
import com.controlpad.payman_common.merchant.MerchantMapper;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.payment.PaymentMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.payment_batch.PaymentBatch;
import com.controlpad.payman_common.payment_batch.PaymentBatchMapper;
import com.controlpad.payman_common.payment_provider.PaymentProvider;
import com.controlpad.payman_common.payment_provider.PaymentProviderCredentials;
import com.controlpad.payman_common.payment_provider.PaymentProviderMapper;
import com.controlpad.payman_common.payout_job.PayoutJob;
import com.controlpad.payman_common.payout_job.PayoutJobMapper;
import com.controlpad.payman_common.team.*;
import com.controlpad.payman_processor.CronTest;
import com.controlpad.payman_processor.client.ClientConfigUtil;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import com.controlpad.payman_processor.payout_processing.PaymentBatchProcessingTask;
import org.joda.time.DateTime;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

public class PaymentBatchProcessingTaskTest extends CronTest {

    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    ClientConfigUtil clientConfigUtil;

    @Test
    public void paymentBatchProcessingTaskTest() {
        PaymentBatchMapper paymentBatchMapper = getClientSqlSession().getMapper(PaymentBatchMapper.class);
        PaymentMapper paymentMapper = getClientSqlSession().getMapper(PaymentMapper.class);

        PaymentProvider paymentProvider = new PaymentProvider("Mock Provider", "mock",
                new PaymentProviderCredentials());
        getClientSqlSession().getMapper(PaymentProviderMapper.class).insert(paymentProvider);

        Team team = new Team("pbpayouts", "Payment Batch Payouts",
                new TeamConfig(true, true, true, false,
                        false, BigDecimal.valueOf(3000D), PayoutScheme.MANUAL_SCHEDULE.getSlug(),
                        PayoutMethod.FILE.getSlug(), PayoutMethod.PAYMENT_BATCH.getSlug(), "none"));
        team.setPaymentProviderId(paymentProvider.getId());
        getClientSqlSession().getMapper(TeamMapper.class).insert(team);

        PaymentBatch paymentBatch = new PaymentBatch(getIdUtil().generateId(), "Fake batch for processing test",
                team.getId(), "open", BigDecimal.ZERO, 0);
        paymentBatchMapper.insert(paymentBatch);

        List<Merchant> merchants = new ArrayList<>();
        merchants.add(new Merchant("pbpayout1", "fake@example.com", "rep"));
        merchants.add(new Merchant("pbpayout2", "fake@example.com", "rep"));
        merchants.add(new Merchant("pbpayout3", "fake@example.com", "rep"));
        for (Merchant merchant : merchants) {
            getClientSqlSession().getMapper(MerchantMapper.class).insert(merchant);
        }

        List<Payment> payments = new ArrayList<>();
        payments.add(new Payment(getIdUtil().generateId(), team.getId(), "pbpayout1", null,
                BigDecimal.valueOf(101.52D), paymentBatch.getId(), PaymentType.TRANSFER.slug));
        payments.add(new Payment(getIdUtil().generateId(), team.getId(), "pbpayout2", null,
                BigDecimal.valueOf(99.44D), paymentBatch.getId(), PaymentType.TRANSFER.slug));
        payments.add(new Payment(getIdUtil().generateId(), team.getId(), "pbpayout3", null,
                BigDecimal.valueOf(81.94D), paymentBatch.getId(), PaymentType.TRANSFER.slug));
        paymentMapper.insertList(payments);

        PayoutJob payoutJob = new PayoutJob(DateTime.now().toString("yyyy-MM-dd HH:mm:ss"),
                team.getId(), "queued", PayoutScheme.BATCH_TO_PROVIDER.getSlug(), paymentBatch.getId());
        getClientSqlSession().getMapper(PayoutJobMapper.class).insert(payoutJob);
        getClientSqlSession().commit();

        new PaymentBatchProcessingTask(sqlSessionUtil, getTestUtil().getMockData().getTestClient().getId(), payoutJob.getId()).run();

        for (Payment payment : payments) {
            assert paymentMapper.findPaymentById(payment.getId()).getReferenceId() != null;
        }

        assert getClientSqlSession().getMapper(PayoutJobMapper.class).findById(payoutJob.getId()).getStatus().equals("processed");
        PaymentBatch paymentBatchResult = paymentBatchMapper.findById(paymentBatch.getId());
        assert paymentBatchResult.getStatus().equals("closed");
        assert paymentBatchResult.getSubmittedAt() != null;
    }
}