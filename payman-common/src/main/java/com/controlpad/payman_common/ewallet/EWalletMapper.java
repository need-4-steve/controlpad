package com.controlpad.payman_common.ewallet;

import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

import java.math.BigDecimal;
import java.util.List;

public interface EWalletMapper {

    @Select("SELECT * FROM e_wallets WHERE user_id = #{0}")
    List<EWallet> findForUserId(String userId);

    @Select("SELECT * FROM e_wallets WHERE user_id = #{0} AND team_id = #{1}")
    EWallet findForUserIdTeamId(String userId, String teamId);

    @Select("SELECT EXISTS(SELECT user_id FROM e_wallets WHERE user_id = #{0} AND team_id = #{1})")
    boolean existsForUserIdAndTeamId(String userId, String teamId);

    @Select("SELECT COUNT(payee_user_id) from transactions AS t" +
            " INNER JOIN transaction_type AS tp ON t.transaction_type_id = tp.id" +
            " WHERE tp.slug = 'e-wallet-withdraw' AND t.payee_user_id = #{0} AND t.team_id = #{1}" +
            " AND created_at >= curdate()")
    int getWithdrawTimesForUserId(String userId, String teamId);

    @Insert("INSERT INTO e_wallets (user_id, team_id, is_percent, amount, balance)" +
            " VALUES (#{userId}, #{teamId}, #{isPercent}, #{amount}, #{balance})" +
            " ON DUPLICATE KEY UPDATE amount = #{amount}")
    int insert(EWallet eWallet);

    @Insert("INSERT INTO e_wallets (user_id, team_id, is_percent, amount, balance)" +
            " VALUES (#{userId}, #{teamId}, #{isPercent}, #{amount}, #{balance})" +
            " ON DUPLICATE KEY UPDATE user_id = #{userId}")
    int tryInsert(EWallet eWallet);

    @Update("UPDATE e_wallets SET is_percent = #{isPercent}, amount = #{amount}, balance = #{balance} WHERE user_id = #{userId} AND team_id = #{teamId}")
    int update(EWallet eWallet);

    @Update("UPDATE e_wallets SET is_percent = #{isPercent}, amount = #{amount} WHERE user_id = #{userId} AND team_id = #{teamId}")
    int updateAmount(EWallet eWallet);

    @Update("UPDATE e_wallets SET balance = (balance - #{1}) WHERE user_id = #{0} AND team_id = #{1} AND balance >= #{2}")
    int subtractBalance(String userId, String teamId, BigDecimal charge);

    @Update("UPDATE e_wallets SET balance = (balance - #{2}) WHERE user_id = #{0} AND team_id = #{1}")
    int forceSubtractBalance(String userId, String teamId, BigDecimal charge);

    @Update("UPDATE e_wallets SET balance = (balance + #{2}) WHERE user_id = #{0} AND team_id = #{1}")
    int addBalance(String userId, String teamId, BigDecimal credit);

    // TESTING

    @Select("SELECT balance FROM e_wallets WHERE user_id = #{0} AND team_id = #{1}")
    BigDecimal getBalanceForUserId(String userId, String teamId);
}
