package com.controlpad.pay_fac.payment_batches;


import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.ParamValidations;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.pay_fac.util.TeamConverterUtil;
import com.controlpad.payman_common.payment.PaymentMapper;
import com.controlpad.payman_common.payment_batch.PaymentBatch;
import com.controlpad.payman_common.payment_batch.PaymentBatchMapper;
import com.controlpad.payman_common.payout_job.PayoutJob;
import com.controlpad.payman_common.payout_job.PayoutJobMapper;
import com.controlpad.payman_common.team.PayoutMethod;
import com.controlpad.payman_common.team.PayoutScheme;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamMapper;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import java.math.BigInteger;
import java.util.List;

@RestController
@RequestMapping(value = "payment-batches")
public class PaymentBatchController {

    @Authorization(clientSqlSession = true, readPrivilege = 7)
    @RequestMapping(value = "", method = RequestMethod.GET)
    public PaginatedResponse<PaymentBatch> searchPaymentBatches(HttpServletRequest request,
                                                            @RequestParam(value = "submitted", required = false) Boolean submitted,
                                                            @RequestParam(value = "startDate", required = false) DateTime startDate,
                                                            @RequestParam(value = "endDate", required = false) DateTime endDate,
                                                            @RequestParam(value = "status", required = false) String status,
                                                            @RequestParam(value = "page") Long page,
                                                            @RequestParam(value = "count") Integer count,
                                                            @RequestParam(value = "teamId") String teamId) {
        teamId = TeamConverterUtil.convert(teamId);

        ParamValidations.checkPageCount(count, page);

        PaymentBatchMapper paymentBatchMapper = RequestUtil.getClientSqlSession(request).getMapper(PaymentBatchMapper.class);

        List<PaymentBatch> data = paymentBatchMapper.search(teamId, startDate, endDate, submitted, status, count, (page - 1) * count);
        BigInteger totalRecords = paymentBatchMapper.searchCount(teamId, startDate, endDate, submitted, status);

        return new PaginatedResponse<>(totalRecords.longValue(), count, page, data);
    }

    @Authorization(clientSqlSession = true, readPrivilege = 7)
    @RequestMapping(value = "/{id}", method = RequestMethod.GET)
    public PaymentBatch getPaymentBatch(HttpServletRequest request,
                                      @PathVariable("id") String id) {

        return RequestUtil.getClientSqlSession(request).getMapper(PaymentBatchMapper.class).findById(id);
    }

    @Authorization(readPrivilege = 7, clientSqlSession = true)
    @RequestMapping(value = "/{id}/submit", method = RequestMethod.GET)
    public PaymentBatch submit(HttpServletRequest request,
                              @PathVariable("id") String id) {

        SqlSession sqlSession = RequestUtil.getClientSqlSession(request);
        PaymentBatchMapper paymentBatchMapper = sqlSession.getMapper(PaymentBatchMapper.class);

        PaymentBatch paymentBatch = paymentBatchMapper.findById(id);
        if (paymentBatch == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "PaymentBatch doesn't exist for id");
        }

        if (paymentBatch.getSubmittedAt() != null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Batch already submitted");
        }

        Team team = sqlSession.getMapper(TeamMapper.class).findById(paymentBatch.getTeamId());
        PayoutMethod merchantPayoutMethod = PayoutMethod.findBySlug(team.getConfig().getMerchantPayoutMethod());
        switch (merchantPayoutMethod) {
            case PAYMENT_BATCH:
                // Create a job for payquicker
                if (!paymentBatch.getStatus().equalsIgnoreCase("open") || paymentBatchMapper.markQueued(id) < 1) {
                    throw new ResponseException(HttpStatus.BAD_REQUEST, "Batch already processing");
                }
                sqlSession.getMapper(PayoutJobMapper.class).insert(
                        new PayoutJob(DateTime.now().toString("yyyy-MM-dd HH:mm:ss"), paymentBatch.getTeamId(),
                                "inactive", PayoutScheme.BATCH_TO_PROVIDER.getSlug(), paymentBatch.getId()));
                paymentBatch.setStatus("queued");
                break;
            case PAYMENT_BATCH_MANUAL:
                if (!paymentBatch.getStatus().equalsIgnoreCase("open") || paymentBatchMapper.markSubmittedForId(id) < 1) {
                    throw new ResponseException(HttpStatus.BAD_REQUEST, "Batch already processing");
                }
                paymentBatchMapper.updateStatus(paymentBatch.getId(), "closed");
                sqlSession.getMapper(PaymentMapper.class).markPaidForBatchId(paymentBatch.getId());
                paymentBatch.setStatus("closed");
                paymentBatch.setSubmittedAt(DateTime.now());
                break;
            default:
                throw new ResponseException(HttpStatus.BAD_REQUEST, "Settings disallow submitting batch. Contact admin.");
        }

        sqlSession.commit();

        return paymentBatch;
    }
}