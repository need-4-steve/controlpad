package com.controlpad.payman_common.credits;

import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

import java.math.BigDecimal;
import java.util.List;

public interface CreditsMapper {

    // Company Credits

    @Select("SELECT * FROM company_credits WHERE user_id = #{0}")
    CompanyCredit findCompanyCreditForUserId(String userId);

    @Insert("INSERT INTO company_credits (user_id, balance) VALUES (#{userId}, #{balance})")
    int insertCompanyCredit(CompanyCredit companyCredit);

    @Update("UPDATE company_credits SET balance = (balance - #{1}) WHERE user_id = #{0}")
    int subtractCompanyCreditsBalance(String userId, BigDecimal amount);

    @Update("UPDATE company_credits SET balance = (balance + #{1}) WHERE user_id = #{0}")
    int addCompanyCreditsBalance(String userId, BigDecimal amount);

    @Select("SELECT EXISTS (SELECT user_id FROM company_credits WHERE user_id = #{0})")
    boolean existsCompanyCreditsForUserId(String userId);

    // Team Credits

    @Insert("INSERT INTO team_credits (user_id, team_id, balance) VALUES (#{userId}, #{teamId}, #{balance})")
    int insertTeamCredit(TeamCredit teamCredit);

    @Update("UPDATE team_credits SET balance = (balance - #{2}) WHERE user_id = #{0} AND team_id = #{1}")
    int subtractTeamCreditsBalance(String userid, String teamId, BigDecimal amount);

    @Update("UPDATE team_credits SET balance = (balance + #{2}) WHERE user_id = #{0} AND team_id = #{1}")
    int addTeamCreditsBalance(String userid, String teamId, BigDecimal amount);

    @Select("SELECT * FROM team_credits WHERE user_id = #{0}")
    List<TeamCredit> listTeamCreditForuserId(String userId);

    @Select("SELECT * FROM team_credits WHERE user_id = #{0} AND team_id = #{1}")
    TeamCredit findTeamCreditForUserAndTeam(String userId, String teamId);

    @Select("SELECT EXISTS (SELECT * FROM team_credits WHERE user_id = #{userId} AND team_id = #{teamId})")
    boolean exists(TeamCredit teamCredit);

    @Select("SELECT EXISTS (SELECT balance FROM team_credits WHERE user_id = #{0} AND team_id = #{1})")
    boolean existsTeamCreditForUserAndTeam(String userId, String teamId);

    // Testing
    @Select("SELECT balance FROM company_credits WHERE user_id = #{0}")
    BigDecimal getCompanyCreditsBalance(String userId);

    @Select("SELECT balance FROM team_credits WHERE user_id = #{0} AND team_id = #{1}")
    BigDecimal getTeamCreditsBalance(String userId, String teamId);
}