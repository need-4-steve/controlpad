package com.controlpad.payman_common.transaction_batch;

import org.apache.ibatis.annotations.*;
import org.joda.time.DateTime;

import java.math.BigDecimal;
import java.math.BigInteger;
import java.util.List;
import java.util.Map;

public interface TransactionBatchMapper {

    @Select("SELECT * FROM transaction_batches WHERE id = #{0}")
    TransactionBatch findForId(Long batchId);

    @Select("SELECT * FROM transaction_batches WHERE gateway_connection_id = #{0} AND external_id = #{1} ORDER BY created_at DESC LIMIT 1")
    TransactionBatch findForExternalId(Long gatewayConnectionId, String externalId);

    @Select("SELECT id FROM transaction_batches WHERE gateway_connection_id = #{0} AND external_id = #{1}")
    Long findTransactionBatchIdForExternalId(Long gatewayConnectionId, String externalId);

    @Select("SELECT * FROM transaction_batches LIMIT #{0}, #{1}")
    List<TransactionBatch> listPaginated(long offset, int count);

    @Select("SELECT * FROM transaction_batches WHERE status = #{2} LIMIT #{0}, #{1}")
    List<TransactionBatch> listPaginatedForStatus(long offset, int count, int status);

    @Select("<script>" +
            "SELECT * FROM transaction_batches" +
            " WHERE gateway_connection_id = #{gatewayConnectionId}" +
            " <if test='startDate!=null'>AND settled_at >= #{startDate}</if>" +
            " <if test='endDate!=null'>AND #{endDate} >= settled_at</if>" +
            " <if test='status!=null'>AND status = #{status}</if>" +
            " ORDER BY settled_at DESC LIMIT #{offset}, #{count}" +
            "</script>")
    List<TransactionBatch> search(@Param("gatewayConnectionId") BigInteger gatewayConnectionId,
                                  @Param("status") Integer status, @Param("startDate") String startDate,
                                  @Param("endDate") String endDate, @Param("offset") BigInteger offset, @Param("count") BigInteger count);

    @Select("SELECT * FROM transaction_batches WHERE status <> 3 AND gateway_connection_id = #{0}")
    List<TransactionBatch> listNotSettledForConnection(long gatewayConnectionId);

    @Select("SELECT EXISTS (SELECT id FROM transaction_batches WHERE id = #{0})")
    boolean existsForId(Long id);

    @Select("SELECT COUNT(id) FROM transaction_batches")
    Long getRecordsCount();

    @Select("SELECT COUNT(id) FROM transaction_batches WHERE status = #{0}")
    Long getRecordsCountForStatus(int status);

    @Update("UPDATE transaction_batches SET payout_job_id = #{1} WHERE id = #{0}")
    int setJobId(Long transactionBatchId, Long payoutJobId);

    @Insert("INSERT INTO transaction_batches(gateway_connection_id, external_id, external_number, status, payment_file_id, settled_at)" +
            " VALUES (#{gatewayConnectionId}, #{externalId}, #{externalNumber}, #{status}, #{paymentFileId}, #{settledAt})")
    @Options(useGeneratedKeys = true, keyColumn = "id")
    int insert(TransactionBatch transactionBatch);

    // Processing

    @Select("SELECT id FROM transaction_batches WHERE payout_job_id = #{0}")
    List<Long> listIdsForPayoutJobId(Long payoutScheduleId);

    @Select("SELECT #{0} AS id, COUNT(id) AS transactionCount," +
            " SUM(CASE WHEN transaction_type_id IN (2,3,4,5) THEN amount ELSE 0 END) AS sales," +
            " SUM(CASE WHEN transaction_type_id IN (6,7,8) THEN amount ELSE 0 END) AS subscriptions," +
            " SUM(CASE WHEN transaction_type_id IN (60,63) THEN amount ELSE 0 END) AS tax_payments," +
            " SUM(CASE WHEN transaction_type_id IN (70,71,73) THEN amount ELSE 0 END) AS shipping," +
            " SUM(CASE WHEN transaction_type_id = 90 THEN amount ELSE 0 END) AS refunds," +
            " SUM(CASE WHEN status_code = 'V' THEN amount ELSE 0 END) AS voids" +
            " FROM transactions WHERE batch_id = #{0} AND result_code = 1")
    TransactionBatch calculateTransactionStats(Long batchId);

    @Update("UPDATE transaction_batches SET status = 3, settled_at = #{1} WHERE id = #{0}")
    int markSettledForId(Long transactionBatchId, DateTime settledAt);

    @Update("<script>" +
            "UPDATE transaction_batches SET payment_file_id = #{1} WHERE id IN" +
            "<foreach item='item' index='index' collection='list' open='(' separator=',' close=')'>" +
            "#{item}" +
            "</foreach>" +
            "</script>")
    int setPaymentFileIdForList(@Param("list") List<Long> gatewayIds, Long paymentFileId);

    @Update("UPDATE transaction_batches SET gateway_net_amount = #{gatewayNetAmount}, gateway_transaction_count = #{gatewayTransactionCount}," +
            " transaction_count = #{transactionCount}, sales = #{sales}, tax_payments = #{taxPayments}," +
            " subscriptions = #{subscriptions}, shipping = #{shipping}, refunds = #{refunds}, voids = #{voids}" +
            " WHERE id = #{id}")
    int updateStats(TransactionBatch transactionBatch);

    @Update("UPDATE transaction_batches AS tb JOIN gateway_connections AS gc ON tb.gateway_connection_id = gc.id" +
            " SET tb.payout_job_id = #{0}" +
            " WHERE tb.status = 3 AND gc.team_id = #{1} AND tb.created_at < #{2} AND tb.payout_job_id IS NULL")
    int setPayoutJobForReadyToProcessTeamId(Long payoutJobId, String teamId, String endDate);

    @Update("UPDATE transaction_batches SET payout_job_id = #{0}" +
            " WHERE status = 3 AND gateway_connection_id = #{1} AND created_at < #{1} AND payout_job_id IS NULL")
    int setPayoutJobForReadyToProcessConnectionId(Long payoutJobId, Long gatewayConnectionId, String endDate);

    @Select("SELECT tb.id FROM transaction_batches AS tb" +
            " JOIN gateway_connections AS gc ON tb.gateway_connection_id = gc.id" +
            " WHERE tb.payout_job_id IS NULL AND gc.team_id = #{0}")
    List<Long> listIdsNotPaidForTeamId(String teamId);

    @Select("SELECT COUNT(id) FROM transactions WHERE batch_id = #{0}")
    int transactionCountForId(Long batchId);
}