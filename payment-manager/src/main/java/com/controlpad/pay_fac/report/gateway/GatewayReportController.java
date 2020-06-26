package com.controlpad.pay_fac.report.gateway;

import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.gateway.GatewayUtil;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.ParamValidations;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import javax.servlet.http.HttpServletRequest;
import java.math.BigInteger;
import java.util.List;

@RestController
@RequestMapping(value = "/gateway-reports")
public class GatewayReportController {

    @Autowired
    GatewayUtil gatewayUtil;

    private DateTimeFormatter dbDateTimeFormatter;

    public GatewayReportController() {
        dbDateTimeFormatter = DateTimeFormat.forPattern("yyyy-MM-dd HH:mm:ss");
    }

    @Authorization(readPrivilege = 7, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/batch-list", method = RequestMethod.GET)
    public List<TransactionBatch> searchBatches(HttpServletRequest request,
                                                                  @RequestParam(value = "teamId", required = false) String teamId,
                                                                  @RequestParam(value = "userId", required = false) String userId,
                                                                  @RequestParam(value = "gatewayConnectionId", required = false) Long gatewayConnectionId,
                                                                  @RequestParam(value = "startDate", required = false) String startDate,
                                                                  @RequestParam(value = "endDate", required = false) String endDate,
                                                                  @RequestParam("page") BigInteger page,
                                                                  @RequestParam("count") BigInteger count) {

        ParamValidations.checkPageCount(count.intValue(), page.longValue());
        if (gatewayConnectionId == null && teamId == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "gatewayConnectionId or teamId required");
        }

        SqlSession clientSession = RequestUtil.getClientSqlSession(request);

        GatewayConnection gatewayConnection = gatewayUtil.selectGatewayConnection(clientSession, gatewayConnectionId,
                teamId, userId, null, null, null, null);

        if (gatewayConnection == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "No gateway found to complete request");
        }
        DateTime startDateTime = null;
        DateTime endDateTime = null;
        if (StringUtils.isNotBlank(startDate)) {
            startDateTime = dbDateTimeFormatter.parseDateTime(startDate);
        }
        if (StringUtils.isNotBlank(endDate)) {
            endDateTime = dbDateTimeFormatter.parseDateTime(endDate);
        }

        List<TransactionBatch> gatewayBatches = gatewayUtil.getGatewayApi(gatewayConnection)
                .searchGatewayBatches(clientSession, gatewayConnection, startDateTime, endDateTime, page, count);

        if (gatewayBatches.size() > 0) {
            List<TransactionBatch> transactionBatches = clientSession.getMapper(GatewayReportsMapper.class)
                    .getTransactionBatchesForGatewayList(gatewayConnection.getId(), gatewayBatches);
            for(int i = 0; i < gatewayBatches.size(); i++) {
                // Check to see if we have a transaction batch to swap out from database (includes processed stats)
                for (TransactionBatch transactionBatch : transactionBatches) {
                    if (transactionBatch.getExternalId().equals(gatewayBatches.get(i).getExternalId())) {
                        gatewayBatches.set(i, transactionBatch);
                        transactionBatches.remove(transactionBatch);
                        break;
                    }
                }
            }
        }

        return gatewayBatches;
    }

    @Authorization(readPrivilege = 7, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/batch-transaction-breakdown", method = RequestMethod.GET)
    public List<GatewayTransaction> reportTransactions(HttpServletRequest request,
                                                             @RequestParam(value = "gatewayConnectionId") Long gatewayConnectionId,
                                                             @RequestParam(value = "externalBatchId") String externalBatchId,
                                                             @RequestParam("page") BigInteger page,
                                                             @RequestParam("count") BigInteger count) {

        ParamValidations.checkPageCount(count.intValue(), page.longValue());

        SqlSession clientSession = RequestUtil.getClientSqlSession(request);

        GatewayConnection gatewayConnection = gatewayUtil.selectGatewayConnection(clientSession, gatewayConnectionId,
                null, null, null, null, null, null);

        if (gatewayConnection == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "No gateway found to complete request");
        }

        List<GatewayTransaction> gatewayTransactions = gatewayUtil.getGatewayApi(gatewayConnection)
                .searchTransactions(clientSession, gatewayConnection, externalBatchId, page, count);
        if (gatewayTransactions.isEmpty()) {
            return gatewayTransactions;
        }

        List<TransactionBreakdown> transactionBreakdowns = clientSession.getMapper(GatewayReportsMapper.class)
                .calculateTransactionBreakdownsForGatewayList(gatewayConnection.getId(), gatewayTransactions);

        for (GatewayTransaction gatewayTransaction : gatewayTransactions) {
            for (TransactionBreakdown transactionBreakdown : transactionBreakdowns) {
                if (StringUtils.equals(transactionBreakdown.getGatewayReferenceId(), gatewayTransaction.getId())) {
                    gatewayTransaction.setTransactionBreakdown(transactionBreakdown);
                    transactionBreakdowns.remove(transactionBreakdown);
                    break;
                }
            }
        }

        return gatewayTransactions;
    }

    @Authorization(readPrivilege = 7, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/batch-breakdown", method = RequestMethod.GET)
    public BatchBreakdown batchBreakdown(HttpServletRequest request,
                                                       @RequestParam(value = "gatewayConnectionId") Long gatewayConnectionId,
                                                       @RequestParam(value = "externalBatchId") String externalBatchId) {

        SqlSession clientSession = RequestUtil.getClientSqlSession(request);
        return clientSession.getMapper(GatewayReportsMapper.class).getBatchBreakdown(gatewayConnectionId, externalBatchId);
    }
}