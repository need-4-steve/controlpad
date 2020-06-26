package com.controlpad.pay_fac.report;


import com.controlpad.pay_fac.report.custom.*;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.transaction.Transaction;
import org.apache.ibatis.annotations.MapKey;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

import java.math.BigDecimal;
import java.util.HashMap;
import java.util.List;

public interface ReportsMapper {

    @Select("SELECT SUM(CASE transaction_type_id WHEN 90 THEN -1 ELSE 1 END) AS `count`," +
            " SUM(CASE transaction_type_id WHEN 90 THEN -amount ELSE amount END) AS `total` FROM transactions" +
            " WHERE payee_user_id = #{0} AND transaction_type_id IN (2,3,4,90) AND (status_code = 'P' OR (status_code = 'S' AND processed = 0))")
    Totals calculateOpenSalesForUser(String userId);

    @Select("SELECT SUM(CASE transaction_type_id WHEN 90 THEN -1 ELSE 1 END) AS `count`," +
            " SUM(CASE transaction_type_id WHEN 90 THEN -amount ELSE amount END) AS `total` FROM transactions" +
            " WHERE team_id = #{0} AND transaction_type_id IN (2,3,4,90) AND (status_code = 'P' OR (status_code = 'S' AND processed = 0))")
    Totals calculateOpenSalesForTeam(String teamId);

    @Select("SELECT SUM(CASE transaction_type_id WHEN 90 THEN -1 ELSE 1 END) AS `count`," +
            " SUM(CASE transaction_type_id WHEN 90 THEN -amount ELSE amount END) AS `total` FROM transactions" +
            " WHERE transaction_type_id IN (2,3,4,90) AND (status_code = 'P' OR (status_code = 'S' AND processed = 0))")
    Totals calculateOpenSales();

    @Update("SET @tct=0.00")
    int resetTCT();

    @Select("SELECT @tct")
    BigDecimal getTCT();

    @Select("SELECT COUNT(tc.id) AS count, #{1} AS total FROM (SELECT *, @tct:=@tct + amount FROM transaction_charges" +
            " WHERE user_id = #{0} AND processed = 0 AND payment_id IS NULL AND type_id = 9 AND @tct + amount <= #{1}) AS tc")
    Totals calculateOpenTaxForUser(String userId, BigDecimal total);

    @Select("SELECT SUM(amount) FROM transaction_charges WHERE user_id = #{0} AND processed = 0 AND payment_id IS NULL AND type_id = 9")
    BigDecimal calculateOpenTaxAmountForUser(String userId);

    @Select("SELECT SUM(tc.amount) FROM transaction_charges AS tc JOIN transactions AS t ON t.id = tc.transaction_id" +
            " WHERE t.team_id = #{0} AND tc.processed = 0 AND tc.payment_id IS NULL AND tc.type_id = 9")
    BigDecimal calculateOpenTaxAmountForTeam(String teamId);

    @Select("SELECT COUNT(amount) AS `count`, SUM(amount) AS `total` FROM transaction_charges WHERE processed = 0 AND type_id = 9")
    Totals calculateOpenTax();

    @Select("SELECT COUNT(tc2.id) AS `count`, #{1} AS `total`" +
            " FROM (SELECT tc.id, @tct:=@tct + tc.amount FROM transaction_charges AS tc" +
            " JOIN transactions AS t ON t.id = tc.transaction_id" +
            " WHERE t.team_id = #{0} AND tc.processed = 0 AND tc.type_id = 9 AND @tct + tc.amount <= #{1}) AS tc2")
    Totals calculateOpenTaxForTeam(String teamId, BigDecimal total);

    @Select("SELECT SUM(amount) FROM transactions WHERE transaction_type_id IN (60, 61, 63) AND payer_user_id = #{0} AND result_code = 1 AND processed = 0")
    BigDecimal getPendingTaxPaymentsForUserId(String userId);

    @Select("SELECT SUM(amount) FROM transactions WHERE transaction_type_id IN (60, 61, 63) AND result_code = 1 AND processed = 0")
    BigDecimal getPendingTaxPaymentsTotal();

    @Select("<script>" +
            "SELECT t.*, tt.slug AS transaction_type" +
            " FROM transactions AS t" +
            " LEFT JOIN transaction_type AS tt ON tt.id = t.transaction_type_id" +
            " WHERE transaction_type_id IN (2,3,4) AND t.created_at >= #{startDate}" +
            " AND #{endDate} > t.created_at" +
            " <if test='payerUserId!=null'>AND t.payer_user_id = #{payerUserId}</if>" +
            " <if test='payeeUserId!=null'>AND t.payee_user_id = #{payeeUserId}</if>" +
            " <if test='teamId!=null'>AND t.team_id = #{teamId}</if>" +
            " LIMIT #{offset}, #{count}" +
            "</script>")
    List<Transaction> searchGatewaySales(@Param("startDate") String startDate, @Param("endDate") String endDate,
                                         @Param("payerUserId") String payerUserId, @Param("payeeUserId") String payeeUserId,
                                         @Param("teamId") String teamId,
                                         @Param("offset") Long offset, @Param("count") Integer count);

    @Select("<script>" +
            "SELECT COUNT(id) FROM transactions" +
            " WHERE transaction_type_id IN (2,3,4)" +
            " AND created_at >= #{startDate}" +
            " AND #{endDate} > created_at" +
            " <if test='payerUserId!=null'>AND payer_user_id = #{payerUserId}</if>" +
            " <if test='payeeUserId!=null'>AND payee_user_id = #{payeeUserId}</if>" +
            " <if test='teamId!=null'>AND team_id = #{teamId}</if>" +
            "</script>")
    Long getGatewaySalesCount(@Param("startDate") String startDate, @Param("endDate") String endDate,
                             @Param("payerUserId") String payerUserId, @Param("payeeUserId") String payeeUserId,
                             @Param("teamId") String teamId);

    @Select("<script>" +
            "SELECT id AS transactionId, created_at AS dateOfSale, amount, account_holder AS cardHolderName," +
            " sales_tax, shipping" +
            " FROM transactions" +
            " WHERE transaction_type_id IN (2,3,4) AND result_code = 1 AND created_at >= #{startDate} AND #{endDate} > created_at" +
            " <if test='userId!=null'>AND payee_user_id = #{userId}</if>" +
            " LIMIT #{offset}, #{count}" +
            "</script>")
    List<MyPayment> listMyPayments(@Param("startDate") String startDate, @Param("endDate") String endDate,
                                       @Param("userId") String userId, @Param("offset") Long offset, @Param("count") Integer count);

    @Select("<script>" +
            "SELECT COUNT(id) FROM transactions" +
            " WHERE transaction_type_id IN (2,3,4) AND result_code = 1 AND created_at >= #{startDate} AND #{endDate} > created_at" +
            " <if test='userId!=null'>AND payee_user_id = #{userId}</if>" +
            "</script>")
    Long getMyPaymentsCount(@Param("startDate") String startDate, @Param("endDate") String endDate,
                            @Param("userId") String userId);

    @Select("<script>" +
            "SELECT id AS transactionId, created_at AS date, amount, account_holder AS cardHolder, shipping" +
            " FROM transactions" +
            " WHERE transaction_type_id IN (2,3,4) AND result_code = 1" +
            " <if test='startDate!=null'>AND created_at >= #{startDate}</if>" +
            " <if test='endDate!=null'>AND #{endDate} > created_at</if>" +
            " <if test='q!=null'>AND (payee_user_id = #{q} OR amount = #{q} OR account_holder LIKE CONCAT('%',#{q},'%') OR id = #{q})</if>" +
            " <if test='payerUserId!=null'>AND payer_user_id = #{payerUserId}</if>" +
            " <if test='payeeUserId!=null'>AND payee_user_id = #{payeeUserId}</if>" +
            " LIMIT #{offset}, #{count}" +
            "</script>")
    List<ProcessingFeeInfo> listProcessingFees(@Param("startDate") String startDate, @Param("endDate") String endDate,
                                               @Param("q") String query,
                                               @Param("payerUserId") String payerUserId, @Param("payeeUserId") String payeeUserId,
                                               @Param("offset") Long offset, @Param("count") Integer count);

    @Select("<script>" +
            "SELECT COUNT(id) FROM transactions" +
            " WHERE transaction_type_id IN (2,3,4) AND result_code = 1" +
            " <if test='startDate!=null'>AND created_at >= #{startDate}</if>" +
            " <if test='endDate!=null'>AND #{endDate} > created_at</if>" +
            " <if test='q!=null'>AND (payee_user_id = #{q} OR amount = #{q} OR account_holder LIKE CONCAT('%',#{q},'%') OR id = #{q})</if>" +
            " <if test='payerUserId!=null'>AND payer_user_id = #{payerUserId}</if>" +
            " <if test='payeeUserId!=null'>AND payee_user_id = #{payeeUserId}</if>" +
            "</script>")
    Long getProcessingFeesCount(@Param("startDate") String startDate, @Param("endDate") String endDate,
                                @Param("q") String query,
                                @Param("payerUserId") String payerUserId, @Param("payeeUserId") String payeeUserId);

    @Select("<script>" +
            "SELECT e.transaction_id, -e.amount AS amount, e.payment_id AS batchId, pf.submitted_at AS dateCollected" +
            " FROM entries AS e" +
            " JOIN payments AS p ON p.id = e.payment_id" +
            " JOIN transactions AS t ON t.id = e.transaction_id" +
            " LEFT JOIN payment_files AS pf ON pf.id = p.payment_file_id" +
            " WHERE e.type_id = 9" +
            " AND (pf.submitted_at IS NULL OR (pf.submitted_at >= #{startDate} AND #{endDate} > pf.submitted_at))" +
            " <if test='q!=null'>AND (e.transaction_id = #{q} OR e.amount = #{q} OR e.payment_id = #{q})</if>" +
            " <if test='payerUserId!=null'>AND t.payer_user_id = #{payerUserId}</if>" +
            " <if test='payeeUserId!=null'>AND t.payee_user_id = #{payeeUserId}</if>" +
            " LIMIT #{offset}, #{count}" +
            "</script>")
    List<SalesTaxInfo> listSalesTax(@Param("startDate") String startDate, @Param("endDate") String endDate, @Param("q") String query,
                                    @Param("payerUserId") String payerUserId, @Param("payeeUserId") String payeeUserId,
                                    @Param("offset") Long offset, @Param("count") Integer count);

    @Select("<script>" +
            "SELECT COUNT(e.transaction_id) FROM entries AS e" +
            " JOIN payments AS p ON p.id = e.payment_id" +
            " JOIN transactions AS t ON t.id = e.transaction_id" +
            " LEFT JOIN payment_files AS pf ON pf.id = p.payment_file_id" +
            " WHERE e.type_id = 9" +
            " AND (pf.submitted_at IS NULL OR (pf.submitted_at >= #{startDate} AND #{endDate} > pf.submitted_at))" +
            " <if test='q!=null'>AND (e.transaction_id = #{q} OR e.amount = #{q} OR e.payment_id = #{q})</if>" +
            " <if test='payerUserId!=null'>AND t.payer_user_id = #{payerUserId}</if>" +
            " <if test='payeeUserId!=null'>AND t.payee_user_id = #{payeeUserId}</if>" +
            "</script>")
    Long getSalesTaxCount(@Param("startDate") String startDate, @Param("endDate") String endDate, @Param("q") String query,
                          @Param("payerUserId") String payerUserId, @Param("payeeUserId") String payeeUserId);

    @Select("SELECT e.amount, e.fee_id, pt.slug AS type, f.description AS description," +
            " (CASE f.is_percent WHEN 1 THEN ROUND(ABS(e.amount) / #{1} * 100, 2) ELSE null END) AS effective_rate" +
            " FROM entries AS e" +
            " JOIN payment_type AS pt ON e.type_id = pt.id" +
            " LEFT JOIN fees AS f ON f.id = e.fee_id" +
            " WHERE transaction_id = #{0}")
    List<Entry> listBasicForTransactionId(String transactionId, BigDecimal transactionAmount);

    @Select("SELECT SUM(e_wallet) FROM user_balances WHERE user_id <> '1' AND user_id <> 'company'")
    BigDecimal getEWalletTotal();

    @Update("SET @ledgerBalance = #{0}")
    int setLedgerBalance(BigDecimal balance);

    @Select("SELECT #{balance} - SUM(e.net) FROM" +
            " (SELECT SUM(amount) AS net" +
            " FROM entries" +
            " WHERE balance_id = #{balanceId}" +
            " GROUP BY transaction_id" +
            " ORDER BY MIN(created_at) DESC, MIN(id) DESC LIMIT #{offset}) AS e")
    BigDecimal getBalanceAtEntryOffset(@Param("balanceId") Long balanceId, @Param("balance") BigDecimal balance,
                                 @Param("offset") Long offset);

    @Select("<script>" +
            "SELECT COUNT(DISTINCT(transaction_id)) FROM entries" +
            " WHERE balance_id = #{balanceId}" +
            " <if test='startDate!=null'>AND created_at >= #{startDate}</if>" +
            " <if test='endDate!=null'>AND #{endDate} >= created_at</if>" +
            "</script>")
    Long getBalanceLedgerCount(@Param("balanceId") Long balanceId, @Param("startDate") String startDate, @Param("endDate") String endDate);

    @Select("<script>" +
            "SELECT transaction_id, MIN(created_at) AS date," +
            " SUM(CASE WHEN type_id = 8 OR type_id = 11 THEN amount ELSE 0 END) AS amount," +
            " SUM(CASE type_id WHEN 9 THEN amount ELSE 0 END) AS salesTax," +
            " SUM(CASE type_id WHEN 7 THEN amount ELSE 0 END) AS fees," +
            " SUM(CASE type_id WHEN 6 THEN amount ELSE 0 END) AS withdraw," +
            " SUM(CASE type_id WHEN 3 THEN amount ELSE 0 END) AS affiliate," +
            " SUM(amount) AS net, BIT_OR(processed) AS processed" +
            " FROM entries" +
            " WHERE balance_id = #{balanceId}" +
            " <if test='startDate!=null'>AND created_at >= #{startDate}</if>" +
            " <if test='endDate!=null'>AND #{endDate} >= created_at</if>" +
            " GROUP BY transaction_id ORDER BY date DESC, MIN(id) DESC" +
            " LIMIT #{offset}, #{count}" +
            "</script>")
    List<BalanceLedgerItem> getEntryGroups(@Param("balanceId") Long balanceId,
                                             @Param("startDate") String startDate, @Param("endDate") String endDate,
                                             @Param("offset") Long offset, @Param("count") Integer count);

    @Select("<script>" +
            "SELECT t.id, t.amount, t.order_id, t.description, tt.slug AS transactionType" +
            " FROM transactions AS t" +
            " LEFT JOIN transaction_type AS tt ON tt.id = t.transaction_type_id" +
            " WHERE t.id IN" +
            " <foreach item='item' index='index' collection='list'" +
            " open='(' separator=',' close=')'>" +
            " #{item.transactionId}" +
            " </foreach>" +
            "</script>")
    @MapKey("id")
    HashMap<String, Transaction> getTransactionsForLedger(@Param("list") List<BalanceLedgerItem> items);

    // Cash Tax

    @Select("SELECT SUM(sales_tax) FROM user_balances WHERE user_id <> '1' AND user_id <> 'company'")
    BigDecimal getSalesTaxBalanceForReps();

    @Select("SELECT SUM(sales_tax) FROM user_balances WHERE user_id = #{0}")
    BigDecimal getSalesTaxBalanceForUserId(String userId);

    @Select("SELECT SUM(amount) FROM transaction_charges where user_id = #{0} AND type_id = 9 AND processed = 0 AND payment_id IS NULL")
    BigDecimal getBalanceTCTaxForUser(String userId);

    @Select("<script>" +
            "SELECT COUNT(t.id)" +
            " FROM transactions AS t" +
            " LEFT JOIN transactions AS tr ON t.for_txn_id = tr.id" +
            " WHERE t.result_code = 1" +
            " AND ((t.payer_user_id = #{userId} AND t.transaction_type_id IN (60, 61, 62, 63, 92))" +
            " OR (t.payee_user_id = #{userId} AND ((t.transaction_type_id = 1 AND t.sales_tax > 0) OR (t.transaction_type_id IN (90, 91) AND tr.transaction_type_id = 1 AND tr.sales_tax > 0))))" +
            " <if test='startDate!=null'>AND t.created_at >= #{startDate}</if>" +
            " <if test='endDate!=null'>AND #{endDate} >= t.created_at</if>" +
            "</script>")
    Long getCashTaxLedgerCount(@Param("userId") String userId, @Param("startDate") String startDate, @Param("endDate") String endDate);

    @Select("<script>" +
            "SELECT #{balance} + SUM(result.amount)" +
            " FROM (SELECT CASE" +
            " WHEN t.transaction_type_id = 1 THEN -t.sales_tax" +
            " WHEN t.transaction_type_id = 92 THEN -t.amount" +
            " WHEN t.transaction_type_id IN (60, 61, 62, 63) AND t.status_code IN ('S','P','A') THEN t.amount" +
            " WHEN t.transaction_type_id IN (90, 91) THEN CASE tr.transaction_type_id WHEN 1 THEN t.sales_tax ELSE 0.00 END" +
            " ELSE 0.00 END AS amount" +
            " FROM transactions AS t" +
            " LEFT JOIN transactions AS tr ON t.for_txn_id = tr.id" +
            " WHERE t.result_code = 1" +
            " AND ((t.payer_user_id = #{userId} AND t.transaction_type_id IN (60, 61, 62, 63, 92))" +
            " OR (t.payee_user_id = #{userId} AND ((t.transaction_type_id = 1 AND t.sales_tax > 0) OR (t.transaction_type_id IN (90, 91) AND tr.transaction_type_id = 1 AND tr.sales_tax > 0))))" +
            " <if test='startDate!=null'>AND t.created_at >= #{startDate}</if>" +
            " <if test='endDate!=null'>AND #{endDate} >= t.created_at</if>" +
            " ORDER BY t.created_at DESC LIMIT #{offset}) AS result" +
            "</script>")
    BigDecimal getCashTaxBalanceAtTransactionOffset(@Param("userId") String userId, @Param("balance") BigDecimal balance,
                                       @Param("startDate") String startDate, @Param("endDate") String endDate,
                                       @Param("offset") Long offset);

    @Select("<script>" +
            "SELECT t.id AS transactionId, t.created_at AS date, t.description, t.gateway_reference_id, t.status_code, t.amount, t.sales_tax, tt.slug AS transactionType, CAST(@ledgerBalance AS DECIMAL(24,5)) AS balance," +
            " @net:= (CASE" +
            " WHEN t.transaction_type_id = 1 THEN t.sales_tax" +
            " WHEN t.transaction_type_id = 92 THEN t.amount" +
            " WHEN t.transaction_type_id IN (60, 61, 62, 63) AND t.status_code IN ('S','P','A') THEN -t.amount" +
            " WHEN t.transaction_type_id IN (90, 91) THEN CASE tr.transaction_type_id WHEN 1 THEN -t.sales_tax ELSE 0.00 END" +
            " ELSE 0.00 END), @net AS net, @ledgerBalance:=@ledgerBalance - @net" +
            " FROM transactions AS t" +
            " LEFT JOIN transactions AS tr ON t.for_txn_id = tr.id" +
            " LEFT JOIN transaction_type AS tt ON tt.id = t.transaction_type_id" +
            " WHERE t.result_code = 1" +
            " AND ((t.payer_user_id = #{userId} AND t.transaction_type_id IN (60, 61, 62, 63, 92))" +
            " OR (t.payee_user_id = #{userId} AND ((t.transaction_type_id = 1 AND t.sales_tax > 0) OR (t.transaction_type_id IN (90, 91) AND tr.transaction_type_id = 1 AND tr.sales_tax > 0))))" +
            " <if test='startDate!=null'>AND t.created_at >= #{startDate}</if>" +
            " <if test='endDate!=null'>AND #{endDate} >= t.created_at</if>" +
            " ORDER BY t.created_at DESC LIMIT #{offset}, #{count}" +
            "</script>")
    List<BalanceLedgerItem> getCashTaxLedger(@Param("userId") String userId,
                                             @Param("startDate") String startDate, @Param("endDate") String endDate,
                                             @Param("offset") Long offset, @Param("count") Integer count);

    @Select("<script>" +
            "SELECT SQL_CALC_FOUND_ROWS tc.user_id AS userId, (IFNULL(tc.amount,0) - IFNULL(tp.amount,0) - IFNULL(tb.sales_tax,0)) AS taxOwed" +
            " FROM (SELECT user_id, SUM(amount) AS amount FROM transaction_charges WHERE processed = 0 AND payment_id IS NULL AND type_id = 9 GROUP BY user_id) AS tc" +
            " LEFT JOIN (SELECT payer_user_id, SUM(amount) AS amount FROM transactions WHERE processed = 0 AND result_code = 1 AND transaction_type_id IN (60,61,63) GROUP BY payer_user_id) AS tp ON tp.payer_user_id = tc.user_id" +
            " LEFT JOIN (SELECT user_id, SUM(sales_tax) AS sales_tax FROM user_balances GROUP BY user_id) AS tb ON tb.user_id = tc.user_id" +
            " <if test='min!=null'>HAVING taxOwed >= #{min}</if>" +
            " <if test='sort!=null'>ORDER BY ${sort}</if>" +
            " LIMIT #{offset}, #{count}" +
            "</script>")
    List<UserBalanceTotal> getUserTaxOwedList(@Param("sort") String sort, @Param("min") BigDecimal min,
                                              @Param("offset") Long offset, @Param("count") Integer count);

    @Select("SELECT FOUND_ROWS()")
    Long getFoundRows();
}