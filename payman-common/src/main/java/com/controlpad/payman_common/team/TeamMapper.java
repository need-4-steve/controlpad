package com.controlpad.payman_common.team;

import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.MapKey;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

import java.util.List;
import java.util.Map;

public interface TeamMapper {

    @Select("SELECT * FROM teams")
    List<Team> list();

    @Select("SELECT EXISTS(SELECT id FROM teams WHERE id = #{0})")
    boolean existsById(String teamId);

    @Select("SELECT * FROM teams WHERE id = #{0}")
    Team findById(String teamId);

    @Select("SELECT tax_account_id FROM teams WHERE id = #{0}")
    Long getTaxAccountId(String teamId);

    @Select("SELECT consignment_account_id FROM teams WHERE id = #{0}")
    Long getConsignmentAccountId(String teamId);

    @Select("SELECT config FROM teams WHERE id = #{0}")
    TeamConfig configByTeam(String teamId);

    @Insert("INSERT INTO teams (id, name, account_id, tax_account_id, consignment_account_id, payout_schedule, payment_provider_id, config)" +
            " VALUES (#{id}, #{name}, #{accountId}, #{taxAccountId}, #{consignmentAccountId}, #{payoutSchedule}, #{paymentProviderId}, #{config})")
    int insert(Team team);

    @Update("UPDATE teams SET name = #{name} WHERE id = #{id}")
    int updateName(Team team);

    @Update("UPDATE teams SET account_id = #{accountId} WHERE id = #{id}")
    int updateAccountId(Team team);

    @Update("UPDATE teams SET consignment_account_id = #{consignmentAccountId} WHERE id = #{id}")
    int updateConsignmentAccountId(Team team);

    @Update("UPDATE teams SET tax_account_id = #{taxAccountId} WHERE id = #{id}")
    int updateTaxAccountId(Team team);

    @Update("UPDATE teams SET payout_schedule = #{0} WHERE id = #{1}")
    int updatePayoutSchedule(PayoutSchedule payoutSchedule, String teamId);

    @Update("UPDATE teams SET config = #{config} WHERE id = #{id}")
    int updateTeamConfig(Team team);

    @Update("UPDATE teams SET payment_provider_id = #{paymentProviderId} WHERE id = #{id}")
    int updatePaymentProviderId(Team team);

    // Processing

    @Select("SELECT * FROM teams")
    @MapKey("id")
    Map<String, Team> mapTeams();


}