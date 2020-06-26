package com.controlpad.pay_fac.report;


import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.report.custom.*;
import com.controlpad.pay_fac.util.ParamValidations;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.pay_fac.util.TeamConverterUtil;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import org.apache.ibatis.session.SqlSession;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import java.math.BigDecimal;
import java.math.RoundingMode;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;

@RestController
@CrossOrigin(
        methods = {RequestMethod.GET, RequestMethod.OPTIONS},
        maxAge = 86400,
        origins = "*",
        allowedHeaders = "*"
)
@RequestMapping(value = "/reports")
public class ReportController {

    @Authorization(readPrivilege = 8, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/open-sales", method = RequestMethod.GET)
    public Totals getOpenSales(HttpServletRequest request,
                               @RequestParam("userId") String userId) {

        return RequestUtil.getClientSqlSession(request).getMapper(ReportsMapper.class).calculateOpenSalesForUser(userId);
    }

    @Authorization(readPrivilege = 8, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/open-tax", method = RequestMethod.GET)
    public Totals getOpenTax(HttpServletRequest request,
                             @RequestParam(value = "userId", required = false) String userId,
                             @RequestParam(value = "teamId", required = false) String teamId) {

        ReportsMapper reportsMapper = RequestUtil.getClientSqlSession(request).getMapper(ReportsMapper.class);
        if (userId != null) {
            // TODO filter by team?
            List<UserBalances> balances = RequestUtil.getClientSqlSession(request).getMapper(UserBalancesMapper.class).listForUserId(userId);
            BigDecimal salesTaxBalance = BigDecimal.ZERO;
            for (UserBalances balance : balances) {
                salesTaxBalance = salesTaxBalance.add(balance.getSalesTax());
            }

            // Add pending payments to balance total
            BigDecimal pendingAmount = reportsMapper.getPendingTaxPaymentsForUserId(userId);
            if (pendingAmount != null) {
                salesTaxBalance = salesTaxBalance.add(pendingAmount);
            }

            // Calculate the remaining amount owed and pull report using that amount
            BigDecimal taxAmount = reportsMapper.calculateOpenTaxAmountForUser(userId);
            if (taxAmount == null) {
                taxAmount = salesTaxBalance.negate();
            } else {
                taxAmount = taxAmount.subtract(salesTaxBalance);
            }

            reportsMapper.resetTCT();
            Totals taxTotals = reportsMapper.calculateOpenTaxForUser(userId, taxAmount);
            // If there is a remainder then add +1 to the count to show a partial record
            if (reportsMapper.getTCT().compareTo(taxAmount) < 0) {
                taxTotals.setCount(taxTotals.getCount() + 1);
            }
            return taxTotals;
        } else {
            if (teamId == null) {
                teamId = "rep"; // Default to rep team
            }
            BigDecimal openTaxAmount = reportsMapper.calculateOpenTaxAmountForTeam(teamId);
            if (openTaxAmount == null) {
                openTaxAmount = BigDecimal.ZERO;
            }
            BigDecimal salesTaxBalance = reportsMapper.getSalesTaxBalanceForReps();
            if (salesTaxBalance != null) {
                openTaxAmount = openTaxAmount.subtract(salesTaxBalance);
            }
            BigDecimal pendingPayments = reportsMapper.getPendingTaxPaymentsTotal();
            if (pendingPayments != null) {
                openTaxAmount = openTaxAmount.subtract(pendingPayments);
            }
            reportsMapper.resetTCT();
            return reportsMapper.calculateOpenTaxForTeam(teamId, openTaxAmount);
        }
    }

    // TODO this endpoint isn't being used and still needs refactored
    @Authorization(readPrivilege = 7, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/transactions", method = RequestMethod.GET)
    public PaginatedResponse<Transaction> listTransactionsReport(HttpServletRequest request,
                                                                 @RequestParam(value = "startDate") String startDate,
                                                                 @RequestParam(value = "endDate") String endDate,
                                                                 @RequestParam(value = "payerUserId", required = false) String payerUserId,
                                                                 @RequestParam(value = "payeeUserId", required = false) String payeeUserId,
                                                                 @RequestParam(value = "teamId", required = false) String teamId,
                                                                 @RequestParam(value = "page") Long page,
                                                                 @RequestParam(value = "count") Integer count) {
        teamId = TeamConverterUtil.convert(teamId);

        SqlSession sqlSession = RequestUtil.getClientSqlSession(request);
        ReportsMapper reportsMapper = sqlSession.getMapper(ReportsMapper.class);

        List<Transaction> data = reportsMapper.searchGatewaySales(startDate, endDate, payerUserId, payeeUserId, teamId, (page - 1)*count, count);
        for (Transaction transaction: data) {
            transaction.setEntries(reportsMapper.listBasicForTransactionId(transaction.getId(), transaction.getAmount()));
        }
        Long totalRecords = reportsMapper.getGatewaySalesCount(startDate, endDate, payerUserId, payeeUserId, teamId);

        return new PaginatedResponse<>(totalRecords, count, data);
    }

    @Authorization(readPrivilege = 7, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/transactions/{transactionId}", method = RequestMethod.GET)
    public Transaction getTransactionReport(HttpServletRequest request,
                                     @PathVariable("transactionId") String transactionId) {

        SqlSession sqlSession = RequestUtil.getClientSqlSession(request);
        ReportsMapper reportsMapper = sqlSession.getMapper(ReportsMapper.class);

        Transaction transaction = sqlSession.getMapper(TransactionMapper.class).findById(transactionId);
        if (transaction == null) {
            return null;
        }

        transaction.setEntries(reportsMapper.listBasicForTransactionId(transactionId, transaction.getAmount()));

        return transaction;
    }

    @Authorization(readPrivilege = 8, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/cash-tax-ledger", method = RequestMethod.GET)
    public PaginatedResponse<BalanceLedgerItem> cashTaxLedger(HttpServletRequest request,
                                @RequestParam(value = "userId") String userId,
                                @RequestParam(value = "startDate", required = false) String startDate,
                                @RequestParam(value = "endDate", required =  false) String endDate,
                                @RequestParam(value = "page") Long page,
                                @RequestParam(value = "count") Integer count) {

        ParamValidations.checkPageCount(count, page);

        SqlSession clientSession = RequestUtil.getClientSqlSession(request);
        ReportsMapper reportsMapper = clientSession.getMapper(ReportsMapper.class);
        // Get sales tax balance
        BigDecimal salesTaxBalance = reportsMapper.getSalesTaxBalanceForUserId(userId);
        if (salesTaxBalance == null) {
            salesTaxBalance = BigDecimal.ZERO;
        }
        // Add pending payments to balance total
        BigDecimal pendingAmount = reportsMapper.getPendingTaxPaymentsForUserId(userId);
        if (pendingAmount != null) {
            salesTaxBalance = salesTaxBalance.add(pendingAmount);
        }

        long offset = (page - 1) * count;
        long total = reportsMapper.getCashTaxLedgerCount(userId, startDate, endDate);

        if (total == 0) {
            return new PaginatedResponse<>(0L, 0, new ArrayList<>());
        }

        BigDecimal currentBalance = reportsMapper.getBalanceTCTaxForUser(userId);
        if (currentBalance == null) {
            currentBalance = BigDecimal.ZERO;
        }
        currentBalance = currentBalance.subtract(salesTaxBalance);

        if (offset == 0) {
            reportsMapper.setLedgerBalance(currentBalance);  // User balance sales tax reduces amount owed('balance')
        } else {
            reportsMapper.setLedgerBalance(
                    reportsMapper.getCashTaxBalanceAtTransactionOffset(userId, currentBalance, startDate, endDate, offset));
        }

        List<BalanceLedgerItem> items = reportsMapper.getCashTaxLedger(userId, startDate, endDate, offset, count);

        return new PaginatedResponse<>(total, count, items);
    }

    @Authorization(readPrivilege = 8, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/my-payments", method = RequestMethod.GET)
    public PaginatedResponse<MyPayment> listMyPayments(HttpServletRequest request,
                                          @RequestParam(value = "startDate") String startDate,
                                          @RequestParam(value = "endDate") String endDate,
                                          @RequestParam(value = "userId") String userId,
                                          @RequestParam(value = "page") Long page,
                                          @RequestParam(value = "count") Integer count) {

        ParamValidations.checkPageCount(count, page);

        SqlSession sqlSession = RequestUtil.getClientSqlSession(request);
        ReportsMapper reportsMapper = sqlSession.getMapper(ReportsMapper.class);

        List<MyPayment> payments = reportsMapper.listMyPayments(startDate, endDate, userId, (page - 1) * count, count);
        for (MyPayment payment: payments) {
            payment.setPaymentInfo(reportsMapper.listBasicForTransactionId(payment.getTransactionId(), payment.getAmount()));
        }
        Long totalRecord = reportsMapper.getMyPaymentsCount(startDate, endDate, userId);

        return new PaginatedResponse<>(totalRecord, count, payments);
    }

    @Authorization(readPrivilege = 8, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/processing-fees", method = RequestMethod.GET)
        public PaginatedResponse<ProcessingFeeInfo> listProcessingFees(HttpServletRequest request,
                                                      @RequestParam(value = "startDate", required = false) String startDate,
                                                      @RequestParam(value = "endDate", required = false) String endDate,
                                                      @RequestParam(value = "q", required = false) String q,
                                                      @RequestParam(value = "payoutFileId", required = false) Long payoutFileId,
                                                      @RequestParam(value = "paymentFileId", required = false) Long paymentFileId,
                                                      @RequestParam(value = "payerUserId", required = false) String payerUserId,
                                                      @RequestParam(value = "payeeUserId", required = false) String payeeUserId,
                                                      @RequestParam(value = "page") Long page,
                                                      @RequestParam(value = "count") Integer count) {

        if (paymentFileId == null) {
            paymentFileId = payoutFileId; // TODO remove payout file id once no longer used
        }

        ParamValidations.checkPageCount(count, page);

        SqlSession sqlSession = RequestUtil.getClientSqlSession(request);
        ReportsMapper reportsMapper = sqlSession.getMapper(ReportsMapper.class);

        List<ProcessingFeeInfo> processingFeeInfoList = reportsMapper.listProcessingFees(startDate, endDate, q, payerUserId,
                payeeUserId, (page - 1) * count, count);
        for (ProcessingFeeInfo info : processingFeeInfoList) {
            info.setPayoutInfo(reportsMapper.listBasicForTransactionId(info.getTransactionId(), info.getAmount()));
        }
        Long totalRecord = reportsMapper.getProcessingFeesCount(startDate, endDate, q, payerUserId, payeeUserId);
        return new PaginatedResponse<>(totalRecord, count, processingFeeInfoList);
    }

    // TODO create payment file report

    @Authorization(readPrivilege = 8, clientSqlSession = true, clientSqlAutoCommit = true)
    @RequestMapping(value = "/sales-tax", method = RequestMethod.GET)
    public PaginatedResponse<SalesTaxInfo> listSalesTax(HttpServletRequest request,
                                           @RequestParam(value = "startDate") String startDate,
                                           @RequestParam(value = "endDate") String endDate,
                                           @RequestParam(value = "q", required = false) String q,
                                           @RequestParam(value = "payerUserId", required = false) String payerUserId,
                                           @RequestParam(value = "payeeUserId", required = false) String payeeUserId,
                                           @RequestParam(value = "page") Long page,
                                           @RequestParam(value = "count") Integer count) {

        ParamValidations.checkPageCount(count, page);

        SqlSession sqlSession = RequestUtil.getClientSqlSession(request);
        ReportsMapper reportsMapper = sqlSession.getMapper(ReportsMapper.class);

        List<SalesTaxInfo> data = reportsMapper.listSalesTax(startDate, endDate, q, payerUserId, payeeUserId, (page - 1) * count, count);
        Long totalRecord = reportsMapper.getSalesTaxCount(startDate, endDate, q, payerUserId, payeeUserId);
        return new PaginatedResponse<>(totalRecord, count, data);
    }

    @Authorization(clientSqlSession = true, readPrivilege = 8, clientSqlAutoCommit = true)
    @RequestMapping(value = "/e-wallet-dashboard", method = RequestMethod.GET)
    public EWalletReport getEWalletReport(HttpServletRequest request,
                                          @RequestParam(value = "userId", required = false) String userId,
                                          @RequestParam(value = "teamId", required = false) String teamId) {

        if (teamId == null) {
            teamId = "rep";
        }

        Totals openSales;
        Totals openTax;
        BigDecimal eWalletBalance;
        SqlSession clientSession = RequestUtil.getClientSqlSession(request);
        ReportsMapper reportsMapper = clientSession.getMapper(ReportsMapper.class);
        UserBalancesMapper userBalancesMapper = clientSession.getMapper(UserBalancesMapper.class);
        if (userId != null) {
            openSales = getOpenSales(request, userId);
            openTax = getOpenTax(request, userId, null);
            UserBalances userBalances = userBalancesMapper.find(userId, teamId);
            eWalletBalance = (userBalances != null ? userBalances.getEWallet() : BigDecimal.ZERO);
        } else {
            openSales = reportsMapper.calculateOpenSalesForTeam(teamId);
            openTax = getOpenTax(request, null, teamId);

            eWalletBalance = reportsMapper.getEWalletTotal();
        }

        return new EWalletReport(openSales, openTax, eWalletBalance);
    }

    @Authorization(clientSqlSession = true, readPrivilege = 8, clientSqlAutoCommit = true)
    @RequestMapping(value = "/balance-ledger", method = RequestMethod.GET)
    public PaginatedResponse<BalanceLedgerItem> getBalanceReport(HttpServletRequest request,
                                                                 @RequestParam(value = "userId") String userId,
                                                                 @RequestParam(value = "teamId") String teamId,
                                                                 @RequestParam(value = "startDate", required = false) String startDate,
                                                                 @RequestParam(value = "endDate", required =  false) String endDate,
                                                                 @RequestParam(value = "page") Long page,
                                                                 @RequestParam(value = "count") Integer count) {

        ParamValidations.checkPageCount(count, page);

        SqlSession clientSession = RequestUtil.getClientSqlSession(request);
        ReportsMapper reportsMapper = clientSession.getMapper(ReportsMapper.class);
        UserBalancesMapper userBalancesMapper = clientSession.getMapper(UserBalancesMapper.class);

        UserBalances userBalances = userBalancesMapper.find(userId, teamId);
        if (userBalances == null) {
            return new PaginatedResponse<>(0L, 0, new ArrayList<>());
        }
        long offset = (page - 1) * count;
        long total = reportsMapper.getBalanceLedgerCount(userBalances.getId(), startDate, endDate);

        if (total == 0) {
            return new PaginatedResponse<>(0L, 0, new ArrayList<>());
        }

        BigDecimal balance = userBalances.getEWallet().setScale(5, RoundingMode.HALF_UP);
        if (offset > 0) {
            balance = reportsMapper.getBalanceAtEntryOffset(userBalances.getId(), userBalances.getEWallet(), offset)
                    .setScale(5, RoundingMode.HALF_UP);
        }

        List<BalanceLedgerItem> items = reportsMapper.getEntryGroups(userBalances.getId(), startDate, endDate, offset, count);
        if (items.size() == 0) {
            return new PaginatedResponse<>(total, 0, page, new ArrayList<>());
        }
        Map<String, Transaction> transactionMap = reportsMapper.getTransactionsForLedger(items);

        for (BalanceLedgerItem item: items) {
            item.setBalance(balance);
            if (transactionMap.containsKey(item.getTransactionId())) {
                item.setTransactionInfo(transactionMap.get(item.getTransactionId()));
            }
            balance = balance.subtract(item.getNet());
        }

        return new PaginatedResponse<>(total, count, page, items);
    }

    @Authorization(clientSqlSession = true, readPrivilege = 8, clientSqlAutoCommit = true)
    @RequestMapping(value = "/tax-user-balances", method = RequestMethod.GET)
    public PaginatedResponse<UserBalanceTotal> getTaxUserBalances(HttpServletRequest request,
                                                                  @RequestParam(value = "sort", required = false) String sort,
                                                                  @RequestParam(value = "min", required = false) BigDecimal min,
                                                                  @RequestParam(value = "page") Long page,
                                                                  @RequestParam(value = "count") Integer count) {

        if (sort != null) {
            switch (sort) {
                // Workaround to attempt a more natural sort
                // Sort needs validated to prevent injection as well
                case "userId":
                    sort = "LENGTH(userId), userId";
                    break;
                case "-userId":
                    sort = "-LENGTH(userId), -userId";
                    break;
                case "taxOwed":
                case "-taxOwed":
                    break;
                default:
                    throw new ResponseException(HttpStatus.BAD_REQUEST, "sort only on userId or taxOwed");
            }
        }

        ReportsMapper reportsMapper = RequestUtil.getClientSqlSession(request).getMapper(ReportsMapper.class);

        List<UserBalanceTotal> userTaxOwed = reportsMapper.getUserTaxOwedList(sort, min, (page- 1) * count, count);
        Long foundRows = reportsMapper.getFoundRows();

        return new PaginatedResponse<>(foundRows, count, userTaxOwed);
    }
}