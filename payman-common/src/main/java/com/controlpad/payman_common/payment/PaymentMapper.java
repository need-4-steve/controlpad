package com.controlpad.payman_common.payment;


import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

import java.util.Collection;
import java.util.List;

public interface PaymentMapper {

    @Select("SELECT p.*, pt.slug AS type FROM payments AS p JOIN payment_type AS pt ON pt.id = p.type_id WHERE p.id = #{0}")
    Payment findPaymentById(String id);

    @Select("SELECT p.*, pt.slug AS type FROM payments AS p JOIN payment_type AS pt ON pt.id = p.type_id WHERE p.team_id = #{0}")
    List<Payment> findByTeamId(String teamId);

    @Select("<script>" +
            "SELECT p.*, pt.slug AS type" +
            " FROM payments AS p" +
            " JOIN payment_type AS pt ON pt.id = p.type_id" +
            " WHERE 1=1" +
            " <if test='paymentFileId!=null'>AND p.payment_file_id = #{paymentFileId}</if>" +
            " <if test='paymentBatchId!=null'>AND p.payment_batch_id = #{paymentBatchId}</if>" +
            " <if test='userId!=null'>AND p.user_id = #{userId}</if>" +
            " <if test='accountId!=null'>AND p.account_id = #{accountId}</if>" +
            " <if test='teamId!=null'>AND p.team_id = #{teamId}</if>" +
            " <if test='returned!=null'>AND p.returned = #{returned}</if>" +
            " <if test='type!=null'>AND pt.slug = #{type}</if>" +
            " <if test='startDate!=null'>AND p.created_at >= #{startDate}</if>" +
            " <if test='endDate!=null'>AND #{endDate} >= p.created_at</if>" +
            " LIMIT #{offset}, #{count}" +
            "</script>")
    List<Payment> search(@Param("paymentFileId") Long paymentFileId, @Param("userId") String userId,
                         @Param("paymentBatchId") String paymentBatchId,
                         @Param("accountId") Long accountId, @Param("teamId") String teamId,
                         @Param("returned") Boolean returned, @Param("type") String type,
                         @Param("startDate") String startDate, @Param("endDate") String endDate,
                         @Param("offset") Long offset, @Param("count") Integer count);

    @Select("<script>" +
            "SELECT COUNT(p.id)" +
            " FROM payments AS p" +
            " JOIN payment_type AS pt ON pt.id = p.type_id" +
            " WHERE 1=1" +
            " <if test='paymentFileId!=null'>AND p.payment_file_id = #{paymentFileId}</if>" +
            " <if test='paymentBatchId!=null'>AND p.payment_batch_id = #{paymentBatchId}</if>" +
            " <if test='userId!=null'>AND p.user_id = #{userId}</if>" +
            " <if test='accountId!=null'>AND p.account_id = #{accountId}</if>" +
            " <if test='teamId!=null'>AND p.team_id = #{teamId}</if>" +
            " <if test='returned!=null'>AND p.returned = #{returned}</if>" +
            " <if test='type!=null'>AND pt.slug = #{type}</if>" +
            " <if test='startDate!=null'>AND p.created_at >= #{startDate}</if>" +
            " <if test='endDate!=null'>AND #{endDate} >= p.created_at</if>" +
            "</script>")
    long searchCount(@Param("paymentFileId") Long paymentFileId, @Param("userId") String userId,
                     @Param("paymentBatchId") String paymentBatchId,
                     @Param("accountId") Long accountId, @Param("teamId") String teamId,
                     @Param("returned") Boolean returned, @Param("type") String type,
                     @Param("startDate") String startDate, @Param("endDate") String endDate);

    @Insert("INSERT INTO payments(id, team_id, user_id, account_id, amount, payment_file_id, payment_batch_id, type_id, paid_at, reference_id)" +
            " VALUES(#{id}, #{teamId}, #{userId}, #{accountId}, #{amount}, #{paymentFileId}, #{paymentBatchId}, #{typeId}, #{paidAt}, #{referenceId})")
    int insert(Payment payment);

    @Insert("<script>" +
            "INSERT INTO payments(id, team_id, user_id, account_id, amount, payment_file_id, payment_batch_id, type_id)" +
            " VALUES" +
            " <foreach item='item' index='index' collection='list'" +
            " open='(' separator='),(' close=')'>" +
            " #{item.id}, #{item.teamId}, #{item.userId}, #{item.accountId}, #{item.amount}, #{item.paymentFileId}, #{item.paymentBatchId}, #{item.typeId}" +
            " </foreach>" +
            "</script>")
    int insertList(@Param("list") Collection<Payment> payments);

    @Insert("<script>" +
            "INSERT INTO payments(id, team_id, user_id, account_id, amount, payment_file_id, payment_batch_id, type_id)" +
            " VALUES" +
            " <foreach item='item' index='index' collection='list'" +
            " open='(' separator='),(' close=')'>" +
            " #{item.id}, #{item.teamId}, #{item.userId}, #{item.accountId}, #{item.amount}, #{fileId}, #{item.paymentBatchId}, #{item.typeId}" +
            " </foreach>" +
            "</script>")
    int insertListForFile(@Param("list") Collection<Payment> payments, @Param("fileId") Long fileId);

    @Select("SELECT amount FROM payments WHERE id = #{0}")
    Double getAmountById(String batchId);

    @Update("<script>" +
            " UPDATE payments SET payment_file_id = #{1}" +
            " WHERE id IN" +
            " <foreach item='item' index='index' collection='list'" +
            " open='(' separator=',' close=')'>" +
            " #{item}" +
            " </foreach>" +
            "</script>")
    int setFileIdForList(@Param("list") List<String> payoutBatchIds, Long fileId);

    @Update("UPDATE payments SET reference_id = #{1}, paid_at = CURRENT_TIMESTAMP" +
            " WHERE id = #{0}")
    int setPaidAndReferenceId(String payoutBatchId, String referenceId);

    @Update("UPDATE payments SET paid_at = CURRENT_TIMESTAMP WHERE payment_file_id = #{0}")
    int markPaidForFileId(Long paymentFileId);

    // Only intended for use with manual batch payments
    @Update("UPDATE payments SET paid_at = CURRENT_TIMESTAMP WHERE payment_batch_id = #{0}")
    int markPaidForBatchId(String paymentBatchId);

    @Update("UPDATE payments SET returned = 1 WHERE id = #{0}")
    int markReturned(String id);

    // TESTING

    @Update("UPDATE payments SET payment_file_id = #{paymentFileId} WHERE id = #{id}")
    int updatePaymentFileId(Payment payoutBatch);
}