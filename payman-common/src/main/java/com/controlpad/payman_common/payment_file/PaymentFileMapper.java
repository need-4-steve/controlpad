package com.controlpad.payman_common.payment_file;

import org.apache.ibatis.annotations.*;

import java.util.List;

public interface PaymentFileMapper {

    @Select("SELECT * FROM payment_files WHERE id = #{0}")
    PaymentFile findById(Long id);

    @Select("SELECT EXISTS(SELECT id FROM payment_files WHERE id = #{0})")
    Boolean existsForId(Long id);

    @Select("SELECT (submitted_at IS NOT NULL) FROM payment_files WHERE id = #{0}")
    boolean isSubmitted(Long id);

    @Select("<script>" +
            "SELECT pf.*, a.origin_name AS bankName FROM payment_files AS pf" +
            " LEFT JOIN ach AS a ON a.id = pf.ach_id" +
            " WHERE 1=1" +
            " <if test='startDate!=null'>AND pf.created_at >= #{startDate}</if>" +
            " <if test='endDate!=null'>AND #{endDate} > pf.created_at</if>" +
            " <if test='submitted!=null'>AND #{submitted} = (pf.submitted_at IS NOT NULL)</if>" +
            " <if test='teamId!=null'>AND pf.team_id = #{teamId}</if>" +
            " <if test='sortBy!=null'>ORDER BY ${sortBy}</if>" +
            " LIMIT #{offset}, #{count}" +
            "</script>")
    List<PaymentFile> search(@Param("startDate") String startDate, @Param("endDate") String endDate,
                             @Param("teamId") String teamId, @Param("submitted") Boolean submitted,
                             @Param("sortBy") String sortBy,
                             @Param("count") int count, @Param("offset") long offset);

    @Select("<script>" +
            "SELECT COUNT(id) From payment_files" +
            " WHERE 1=1" +
            " <if test='startDate!=null'>AND created_at >= #{startDate}</if>" +
            " <if test='endDate!=null'>AND #{endDate} > created_at</if>" +
            " <if test='submitted!=null'>AND #{submitted} = (submitted_at IS NOT NULL)</if>" +
            " <if test='teamId!=null'>AND team_id = #{teamId}</if>" +
            "</script>")
    Long getSearchCount(@Param("startDate") String startDate, @Param("endDate") String endDate,
                        @Param("teamId") String teamId, @Param("submitted") Boolean submitted);

    @Update("UPDATE payment_files SET submitted_at = CURRENT_TIMESTAMP WHERE id = #{0} AND submitted_at IS NULL")
    int markSubmitted(Long id);

    @Update("UPDATE payment_files SET ach_id = #{1} WHERE id = #{0}")
    int updateAchId(Long paymentFileId, Long achId);

    @Update("UPDATE payment_files" +
            " SET credits = #{credits}, debits = #{debits}, batch_count = #{batchCount}, entry_count = #{entryCount}" +
            " WHERE id = #{id}")
    int updateAmounts(PaymentFile paymentFile);

    @Insert("INSERT INTO payment_files(file_name, description, credits, e_wallet_credits, stay_credits, debits," +
            " batch_count, entry_count, transaction_count, team_id, ach_id)" +
            " VALUES(#{fileName}, #{description}, #{credits}, #{eWalletCredits}, #{stayCredits}, #{debits}," +
            " #{batchCount}, #{entryCount}, #{transactionCount}, #{teamId}, #{achId})")
    @Options(useGeneratedKeys = true, keyColumn = "id")
    int insertPaymentFile(PaymentFile paymentFile);
}
