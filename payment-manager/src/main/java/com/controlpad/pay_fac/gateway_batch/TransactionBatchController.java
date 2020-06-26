package com.controlpad.pay_fac.gateway_batch;

import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.ParamValidations;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import org.apache.ibatis.session.SqlSession;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import java.util.List;

@RestController
@RequestMapping(value = "/transaction-batches")
public class TransactionBatchController {

    @Authorization(clientSqlSession = true, readPrivilege = 7)
    @RequestMapping(value = "", method = RequestMethod.GET)
    public PaginatedResponse<TransactionBatch> listTransactionBatches(HttpServletRequest request,
                                                                  @RequestParam(value = "page", defaultValue = "1") Long page,
                                                                  @RequestParam(value = "count", defaultValue = "25") Integer count,
                                                                  @RequestParam(value = "status", required = false) Integer status) {

        ParamValidations.checkPageCount(count, page);

        SqlSession session = RequestUtil.getClientSqlSession(request);
        TransactionBatchMapper transactionBatchMapper = session.getMapper(TransactionBatchMapper.class);
        if (status == null) {
            Long totalRecords = transactionBatchMapper.getRecordsCount();
            List<TransactionBatch> data = transactionBatchMapper.listPaginated(page - 1, count);
            return new PaginatedResponse<>(totalRecords, count, data);
        } else {
            Long totalRecords = transactionBatchMapper.getRecordsCountForStatus(status);
            List<TransactionBatch> data = transactionBatchMapper.listPaginatedForStatus(page - 1, count, status);
            return new PaginatedResponse<>(totalRecords, count, data);
        }
    }

    @Authorization(clientSqlSession = true, readPrivilege = 7)
    @RequestMapping(value = "/{id}", method = RequestMethod.GET)
    public TransactionBatch getTransactionBatch(HttpServletRequest request,
                                        @PathVariable("id") Long batchId) {

        SqlSession session = RequestUtil.getClientSqlSession(request);

        return session.getMapper(TransactionBatchMapper.class).findForId(batchId);
    }

}