package com.controlpad.payman_processor.payout_processing;

import com.controlpad.payman_common.merchant.MerchantMapper;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.payment.PaymentMapper;
import com.controlpad.payman_common.payment_batch.PaymentBatchMapper;
import com.controlpad.payman_common.payment_provider.PaymentProvider;
import com.controlpad.payman_common.payment_provider.PaymentProviderMapper;
import com.controlpad.payman_common.payout_job.PayoutJob;
import com.controlpad.payman_common.payout_job.PayoutJobMapper;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import com.controlpad.payman_processor.payment_provider.MockProvider;
import com.controlpad.payman_processor.payment_provider.PayQuicker;
import com.controlpad.payman_processor.payment_provider.PaymentProviderInterface;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;


import java.util.List;


public class PaymentBatchProcessingTask implements Runnable {

    private static final Logger logger = LoggerFactory.getLogger(PaymentBatchProcessingTask.class);

    private SqlSessionUtil sqlSessionUtil;
    private String clientId;
    private Long payoutJobId;

    public PaymentBatchProcessingTask(SqlSessionUtil sqlSessionUtil, String clientId, Long payoutJobId) {
        this.sqlSessionUtil = sqlSessionUtil;
        this.clientId = clientId;
        this.payoutJobId = payoutJobId;
    }

    @Override
    public void run() {
        SqlSession clientSqlSession = sqlSessionUtil.openSession(clientId, false);

        PayoutJobMapper payoutJobMapper = clientSqlSession.getMapper(PayoutJobMapper.class);
        PaymentMapper paymentMapper = clientSqlSession.getMapper(PaymentMapper.class);

        PayoutJob payoutJob = payoutJobMapper.findById(payoutJobId);
        // Check that job is in correct state
        if (!StringUtils.equals(payoutJob.getStatus(), "queued")) {
            logger.error("Job status not queued: " + payoutJobId + " | Client: " + clientId);
            payoutJobMapper.markErorr(payoutJobId);
            clientSqlSession.commit();
            return;
        }

        // Mark job processing
        payoutJobMapper.markProcessing(payoutJobId);
        clientSqlSession.commit();

        Team team = clientSqlSession.getMapper(TeamMapper.class).findById(payoutJob.getTeamId());
        PaymentProvider paymentProvider = clientSqlSession.getMapper(PaymentProviderMapper.class).findById(team.getPaymentProviderId());
        if (paymentProvider == null) {
            logger.error("No provider found for job: " + payoutJobId + " | Client: " + clientId);
            payoutJobMapper.markErorr(payoutJobId);
            clientSqlSession.commit();
            return;
        }

        long count = paymentMapper.searchCount(null, null, payoutJob.getPaymentBatchId(), null,
                null, null, null, null, null);
        if (count == 0L) {
            logger.error("No payments for job: " + payoutJobId + " | Client: " + clientId);
            payoutJobMapper.markSkipped(payoutJobId);
            clientSqlSession.commit();
            return;
        }

        int pages = (int) ((count / 1000 + ((count % 1000) == 0 ? 0 : 1)));

        PaymentProviderInterface paymentProviderInterface = getPaymentProviderInterface(paymentProvider);
        if (paymentProviderInterface == null) {
            payoutJobMapper.markErorr(payoutJobId);
            clientSqlSession.commit();
            return;
        }

        List<Payment> payments;
        for(int i = 0; i < pages; i++) {
            payments = paymentMapper.search(null, null, payoutJob.getPaymentBatchId(), null,
                    null, null, null, null, null, (long) (i * 1000), 1000);
            for (Payment payment : payments) {
                if (payment.getReferenceId() != null) {
                    // Already paid
                    continue;
                }
                // Pay
                paymentProviderInterface.createPayment(paymentProvider, payment,
                        clientSqlSession.getMapper(MerchantMapper.class).findById(payment.getUserId()));
                if (payment.getReferenceId() != null) {
                    paymentMapper.setPaidAndReferenceId(payment.getId(), payment.getReferenceId());
                } else {
                    // TODO mark failed and refund instead of allowing the process to resume after a fix?
                    continue;
                }
                clientSqlSession.commit();
            }
        }

        payoutJobMapper.markProcessed(payoutJobId);
        clientSqlSession.getMapper(PaymentBatchMapper.class).markSubmittedForId(payoutJob.getPaymentBatchId());
        clientSqlSession.getMapper(PaymentBatchMapper.class).updateStatus(payoutJob.getPaymentBatchId(), "closed");
        clientSqlSession.commit();
        // TODO notify finished?
    }

    private PaymentProviderInterface getPaymentProviderInterface(PaymentProvider paymentProvider) {
        switch (paymentProvider.getType()) {
            case "mock":
                return new MockProvider();
            case "payquicker":
                return new PayQuicker();
        }
        logger.error("Payment provider type not supported: " + paymentProvider.getType() + " | Client: "  + clientId);
        return null;
    }
}
