package com.controlpad.payman_common.transaction;

import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

import java.math.BigDecimal;
import java.sql.Timestamp;
import java.util.List;

public interface TransactionMapper {

    @Select("SELECT *, (SELECT slug FROM transaction_type WHERE id = transaction_type_id) AS transaction_type FROM transactions WHERE id = #{0}")
    Transaction findById(String transactionId);

    @Select("SELECT *, (SELECT slug FROM transaction_type WHERE id = transaction_type_id) AS transaction_type FROM transactions WHERE order_id = #{0}")
    Transaction findByOrderId(String orderId);

    @Select("SELECT EXISTS(SELECT id FROM transactions WHERE id = #{0})")
    boolean existsForId(String id);

    @Select("SELECT EXISTS(SELECT id FROM transactions WHERE gateway_connection_id = #{0} AND gateway_reference_id = #{1})")
    boolean existsForGatewayReference(Long gatewayConnectionId, String gatewayReferenceId);

    @Select("SELECT id FROM transactions WHERE gateway_connection_id = #{0} AND gateway_reference_id = #{1}")
    String findIdForGatewayReference(Long gatewayConnectionId, String gatewayReferenceId);

    @Select("SELECT COUNT(payee_user_id) from transactions AS t" +
            " INNER JOIN transaction_type AS tp ON t.transaction_type_id = tp.id" +
            " WHERE tp.slug = 'e-wallet-withdraw' AND t.payee_user_id = #{0} AND t.team_id = #{1}" +
            " AND created_at >= curdate()")
    int getWithdrawTimesForUserId(String userId, String teamId);

    @Insert("INSERT INTO transactions(id, payee_user_id, payer_user_id, team_id, transaction_type_id, amount, sales_tax, shipping," +
            " status_code, result_code, gateway_reference_id, batch_id, gateway_connection_id, description, account_holder, for_txn_id, swiped, order_id, payment_id)" +
            " VALUES (#{id}, #{payeeUserId}, #{payerUserId}, #{teamId}, (SELECT id FROM transaction_type WHERE slug = #{transactionType})," +
            " #{amount}, #{salesTax}, #{shipping}, #{statusCode}, #{resultCode}, #{gatewayReferenceId}, #{batchId}," +
            " #{gatewayConnectionId}, #{description}, #{accountHolder}, #{forTxnId}, #{swiped}, #{orderId}, #{paymentId})")
    int insert(Transaction transaction);

    @Update("UPDATE transactions SET status_code = #{statusCode} WHERE id = #{id}")
    int updateTransactionStatus(Transaction transaction);

    @Update("UPDATE transactions SET status_code = #{statusCode}, order_id = #{orderId} WHERE id = #{id}")
    int updateTransactionStatusOrderId(Transaction transaction);

    @Update("UPDATE transactions SET batch_id = #{batchId} WHERE id = #{id}")
    int updateBatchId(Transaction transaction);

    @Update("UPDATE transactions SET order_id = #{orderId} WHERE id = #{id}")
    int updateOrderId(Transaction transaction);

    @Update("UPDATE transactions SET status_code = 'R' WHERE id = #{id}")
    int setVoid(String transactionId);

    @Update("UPDATE transactions SET processed = 1 WHERE id = #{0}")
    int markProcessed(String transactionId);

    // Status stuff

    @Select("SELECT gateway_reference_id FROM transactions WHERE id = #{0}")
    String findGatewayTransactionIdForId(String transactionId);

    // Refund stuff

    @Select("SELECT SUM(amount) AS amount, SUM(sales_tax) AS salesTax FROM transactions WHERE for_txn_id = #{0}" +
            " AND transaction_type_id IN (90, 91, 94)")
    Transaction getRefundTotalsForTransactionId(String transactionId);

    // Processing stuff

    @Select("SELECT SUM(amount) FROM transactions WHERE transaction_type_id IN (60, 61, 63) AND result_code = 1" +
            " AND payer_user_id = #{0} AND processed = 0")
    BigDecimal getPendingTaxPaymentsForUserId(String userId);

    /**
     * Only returning electronic payment transactions because all others should process immediately
     */
    @Select("SELECT t.*, tt.slug AS transaction_type FROM transactions AS t" +
            " JOIN transaction_batches AS tb ON t.batch_id = tb.id" +
            " JOIN transaction_type AS tt ON tt.id = t.transaction_type_id" +
            " WHERE t.team_id = #{0} AND t.result_code = 1 AND t.status_code = 'S' AND t.processed = 0" +
            " AND tb.status >= 3 AND tb.settled_at <= #{1} AND tt.id IN (2, 3, 4, 6, 7, 8, 60, 63, 90)")
    List<Transaction> listForProcessing(String teamId, String endDate);

    @Select("SELECT *, (SELECT slug FROM transaction_type WHERE id = transaction_type_id) AS transaction_type FROM transactions WHERE" +
            " result_code = 1 AND (status_code = 'P' OR batch_id IS NULL) AND gateway_connection_id = #{0} AND transaction_type_id <> 94 LIMIT 1")
    Transaction findFirstTransactionForUpdate(Long gatewayConnectionId);

    @Select("SELECT *, (SELECT slug FROM transaction_type WHERE id = transaction_type_id) AS transaction_type FROM transactions WHERE" +
            " result_code = 1 AND (status_code = 'P' OR batch_id IS NULL) AND gateway_connection_id = #{0}" +
            " AND position > (SELECT position FROM transactions WHERE id = #{1}) AND transaction_type_id <> 94 LIMIT 1")
    Transaction findNextTransactionForUpdate(Long gatewayConnectionId, String lastTransactionId);

    @Update("UPDATE transactions SET status_code = #{statusCode}, batch_id = #{batchId} WHERE id = #{id}")
    int updateTransactionStatusAndBatch(Transaction transaction);

    @Update("UPDATE transactions SET batch_id = #{0} WHERE gateway_connection_id = #{1}" +
            " AND batch_id IS NULL AND created_at < #{2}")
    int setCustomTransactionsBatch(Long transactionBatchId, Long gatewayConnectionId, String endTime);

    @Update("<script>" +
            "UPDATE transactions SET processed = 1 WHERE id IN" +
            " <foreach item='item' index='index' collection='list'" +
            " open='(' separator=',' close=')'>" +
            " #{item.id}" +
            " </foreach>" +
            "</script>")
    int markProcessedForList(@Param("list") List<Transaction> transactions);

    // Testing

    @Select("SELECT processed FROM transactions WHERE id = #{id}")
    Boolean isProcessed(String transactionId);

    @Select("SELECT t.*, tt.slug AS transaction_type FROM transactions AS t" +
            " JOIN transaction_type AS tt ON tt.id = t.transaction_type_id" +
            " WHERE t.payee_user_id = #{0} AND result_code = 1")
    List<Transaction> listSuccessfulForUser(String userId);

}