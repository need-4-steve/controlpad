package com.controlpad.payman_common.fee;

import org.apache.ibatis.annotations.*;

import java.util.HashMap;
import java.util.List;

public interface FeeMapper {

    // Fees

    @Select("SELECT EXISTS(SELECT id FROM fees WHERE id = #{0})")
    boolean existsFeeForId(Long feeId);

    @Select("SELECT * FROM fees WHERE id = #{0}")
    Fee findFeeById(Long feeId);

    @Select("SELECT * FROM fees")
    List<Fee> listAllFees();

    @Select("SELECT * FROM fees WHERE reference_id = #{0}")
    Fee findByReferenceId(String referenceId);

    @Select("SELECT * FROM fees")
    @MapKey("id")
    HashMap<Long, Fee> mapAllFees();

    @Select("SELECT account_id FROM fees WHERE id = #{0}")
    Long findAccountIdForFeeId(Long feeId);

    @Insert("INSERT INTO fees (amount, is_percent, account_id, description, reference_id) VALUES (#{amount}, #{isPercent}, #{accountId}, #{description}, #{referenceId})")
    @Options(useGeneratedKeys = true, keyColumn = "id")
    int insertFee(Fee fee);

    @Update("UPDATE fees SET amount = #{amount}, is_percent = #{isPercent}, account_id = #{accountId}, description = #{description} WHERE id = #{id}")
    int updateFee(Fee fee);

    @Update("UPDATE fees SET amount = #{amount}, is_percent = #{isPercent} WHERE id = #{id}")
    int updateFeeAmount(Fee fee);

    @Update("UPDATE fees SET account_id = #{accountId} WHERE id = #{id}")
    int updateFeeAccount(Fee fee);

    @Update("UPDATE fees SET description = #{description} WHERE id = #{id}")
    int updateFeeDescription(Fee fee);

    @Update("UPDATE fees SET account_id = #{1} WHERE id = #{0}")
    int updateFeeAccountId(Long feeId, Long accountId);


    // Team Fee Sets

    @Select("SELECT * FROM team_feesets WHERE team_id = #{0} AND transaction_type = #{1}")
    TeamFeeSet findTeamFeeSetForType(String teamId, String transactionType);

    @Select("SELECT transaction_type, team_id, description, fee_ids FROM team_feesets WHERE team_id = #{0}")
    List<TeamFeeSet> listTeamFeeSets(String teamId);

    @Select("SELECT * FROM team_feesets")
    TeamFeeSetMap mapTeamFeeSets();

    @Select("SELECT EXISTS (SELECT team_id FROM team_feesets WHERE team_id = #{0} AND transaction_type = #{1})")
    boolean existsTeamFeeSet(String teamId, String transactionType);

    @Insert("INSERT INTO team_feesets(team_id, transaction_type, description, fee_ids) values(#{teamId}, #{transactionType}, #{description}, #{feeIds})")
    int insertTeamFeeSet(TeamFeeSet teamFeeSet);

    @Update("UPDATE team_feesets SET fee_ids = #{feeIds}, description = #{description} WHERE team_id = #{teamId} AND transaction_type = #{transactionType}")
    int updateTeamFeeSet(TeamFeeSet teamFeeSet);


    // Valid query
    @Select("SELECT ((SELECT id FROM teams WHERE id = #{0}) IS NOT NULL AND (SELECT id FROM transaction_type WHERE slug = #{1}) IS NOT NULL)")
    boolean isValidTeamAndType(String teamId, String transactionType);
}
