package com.controlpad.payman_common.entry;


import org.apache.ibatis.annotations.*;

import java.math.BigDecimal;
import java.util.List;

public interface EntryMapper {

    @Select("SELECT e.*, pt.slug AS type FROM entries AS e JOIN payment_type AS pt ON e.type_id = pt.id WHERE e.id = #{0}")
    Entry findById(Long entryId);

    @Select("SELECT e.*, pt.slug AS type FROM entries AS e" +
            " JOIN payment_type AS pt ON e.type_id = pt.id" +
            " WHERE e.transaction_id = #{0}")
    List<Entry> listByTransactionId(String transactionId);

    @Insert("INSERT INTO entries(balance_id, transaction_id, amount, fee_id, payment_id, processed, type_id)" +
            " VALUES(#{balanceId}, #{transactionId}, #{amount}, #{feeId}, #{paymentId}, #{processed}," +
            "(SELECT id FROM payment_type WHERE slug = #{type}))")
    @Options(useGeneratedKeys = true, keyColumn = "id")
    int insert(Entry entry);

    @Insert("<script>" +
            "INSERT INTO entries(balance_id, transaction_id, amount, fee_id, payment_id, processed, type_id)" +
            " VALUES" +
            " <foreach item='item' index='index' collection='list'" +
            " open='(' separator='),(' close=')'>" +
            " #{item.balanceId}, #{item.transactionId}, #{item.amount}, #{item.feeId}, #{item.paymentId}, #{item.processed}, #{item.typeId} " +
            " </foreach>" +
            "</script>")
    int insertList(@Param("list") List<Entry> entries);

    @Update("UPDATE entries SET processed = 1, payment_id = #{paymentId} WHERE id = #{id} AND processed = 0")
    int setProcessedAndBatchId(Entry entry);

    // Processing

    // This is intended to operate on a session variable etotal, which happens in mysql
    @Update("SET @etotal = 0")
    int resetEntryTotal();

    @Select("<script>" +
            "SELECT t.payee_user_id, t.payer_user_id, e.id, -e.amount AS amount, e.fee_id, e.balance_id, pt.slug AS type, @etotal:=(@etotal - e.amount)" +
            " FROM entries AS e" +
            " JOIN payment_type AS pt ON e.type_id = pt.id" +
            " LEFT JOIN transactions AS t ON t.id = e.transaction_id" +
            " WHERE e.processed = 0 AND #{total} >= (@etotal - e.amount)" +
            " <if test='balanceId!=null'>AND e.balance_id = #{balanceId}</if>" +
            " <if test='gatewayConnectionId!=null'>AND t.gateway_connection_id = #{gatewayConnectionId}</if>" +
            " <if test='typeId!=null'>AND e.type_id = #{typeId}</if>" +
            " ORDER BY e.created_at" +
            "</script>")
    List<Entry> searchUnpaidEntries(@Param(value = "balanceId") Long balanceId, @Param(value = "gatewayConnectionId") Long gatewayConnectionId,
                                    @Param(value = "typeId") Integer typeId, @Param(value = "total") BigDecimal total);

    @Select("<script>" +
            "SELECT SUM(e.amount)" +
            " FROM entries AS e" +
            " LEFT JOIN transactions AS t ON t.id = e.transaction_id" +
            " WHERE e.processed = 0" +
            " <if test='balanceId!=null'>AND e.balance_id = #{balanceId}</if>" +
            " <if test='gatewayConnectionId!=null'>AND t.gateway_connection_id = #{gatewayConnectionId}</if>" +
            " <if test='typeId!=null'>AND e.type_id = #{typeId}</if>" +
            "</script>")
    BigDecimal sumUnpaidEntries(@Param(value = "balanceId") Long balanceId, @Param(value = "gatewayConnectionId") Long gatewayConnectionId,
                                @Param(value = "typeId") Integer typeId);

    @Select("<script>" +
            "SELECT t.payee_user_id, t.payer_user_id, e.*, pt.slug AS type FROM entries AS e" +
            " JOIN payment_type AS pt ON e.type_id = pt.id" +
            " LEFT JOIN transactions AS t ON t.id = e.transaction_id" +
            " WHERE e.processed = 0 AND t.team_id = #{teamId} AND t.batch_id IN" +
            " <foreach item='item' index='index' collection='list' open='(' separator=',' close=')'>" +
            "#{item}" +
            "</foreach>" +
            "</script>")
    List<Entry> listNotProcessedForTransactionBatchesAndTeam(@Param("list") List<Long> transactionBatchIds, @Param("teamId") String teamId);

    @Select("SELECT t.payee_user_id, t.payer_user_id, e.*, pt.slug AS type FROM entries AS e" +
            " JOIN payment_type AS pt ON e.payout_type_id = pt.id" +
            " LEFT JOIN transactions AS t ON t.id = e.transaction_id" +
            " WHERE t.batch_id = #{0} AND e.processed = 0 AND t.team_id = #{1}")
    List<Entry> listNotProcessedForTransactionBatchAndTeam(Long transactionBatchId, String teamId);

    @Select("SELECT e.*, pt.slug AS type FROM entries AS e" +
            " INNER JOIN payment_type AS tpt ON e.payout_type_id = pt.id" +
            " INNER JOIN transactions AS t ON t.id = e.transaction_id" +
            " INNER JOIN transaction_type AS tt ON tt.id = t.transaction_type_id" +
            " WHERE e.processed = 0 AND (tt.slug='e-wallet-withdraw' OR tt.slug = 'e-wallet-sale' OR tt.slug = 'e-wallet-sub')" +
            " AND t.team_id = #{0}")
    List<Entry> getNotProcessedEWalletTransactionPayouts(String teamId);

    @Update("<script>" +
            "UPDATE entries SET processed = 1, payment_id = #{1} WHERE id IN" +
            " <foreach item='item' index='index' collection='list'" +
            " open='(' separator=',' close=')'>" +
            " #{item}" +
            " </foreach>" +
            "</script>")
    int setProcessedAndBatchIdForList(@Param("list") List<Long> idList, String batchId);
}
