package com.controlpad.payman_common.payment_batch;

import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;
import org.joda.time.DateTime;

import java.math.BigInteger;
import java.util.List;

public interface PaymentBatchMapper {

    @Select("SELECT * FROM payment_batches WHERE id = #{0}")
    PaymentBatch findById(String id);

    @Select("<script>" +
            "SELECT * FROM payment_batches" +
            " WHERE team_id = #{teamId}" +
            " <if test='startDate!=null'>AND created_at >= #{startDate}</if>" +
            " <if test='endDate!=null'>AND #{endDate} > created_at</if>" +
            " <if test='submitted!=null'>AND #{submitted} = (submitted_at IS NOT NULL)</if>" +
            " <if test='status!=null'>AND status = #{status}</if>" +
            " ORDER BY created_at DESC LIMIT #{offset}, #{count}" +
            "</script>")
    List<PaymentBatch> search(@Param("teamId") String teamId, @Param("startDate")DateTime startDate,
                              @Param("endDate") DateTime endDate, @Param("submitted") Boolean submitted,
                              @Param("status") String status, @Param("count") int count, @Param("offset") long offset);

    @Select("<script>" +
            "SELECT count(id) FROM payment_batches" +
            " WHERE team_id = #{teamId}" +
            " <if test='startDate!=null'>AND created_at >= #{startDate}</if>" +
            " <if test='endDate!=null'>AND #{endDate} > created_at</if>" +
            " <if test='submitted!=null'>AND #{submitted} = (submitted_at IS NOT NULL)</if>" +
            " <if test='status!=null'>AND status = #{status}</if>" +
            "</script>")
    BigInteger searchCount(@Param("teamId") String teamId, @Param("startDate")DateTime startDate,
                           @Param("endDate") DateTime endDate, @Param("submitted") Boolean submitted, @Param("status") String status);

    @Insert("INSERT INTO payment_batches(id, description, team_id, status, net_amount, payment_count)" +
            " VALUES(#{id}, #{description}, #{teamId}, #{status}, #{netAmount}, #{paymentCount})")
    int insert(PaymentBatch paymentBatch);

    @Update("UPDATE payment_batches SET submitted_at = CURRENT_TIMESTAMP WHERE id = #{0}")
    int markSubmittedForId(String id);

    @Update("UPDATE payment_batches SET status = #{1} WHERE id = #{0}")
    int updateStatus(String id, String status);

    @Update("Update payment_batches SET status = 'queued' WHERE id = #{0} AND status = 'open'")
    int markQueued(String id);

    @Update("UPDATE payment_batches SET net_amount = #{netAmount}, payment_count = #{paymentCount} WHERE id = #{id}")
    int updateCounts(PaymentBatch paymentBatch);
}
