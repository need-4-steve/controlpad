package com.controlpad.payman_common.user_balances;


import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Options;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

import java.math.BigDecimal;
import java.util.List;

public interface UserBalancesMapper {

    @Select("SELECT * FROM user_balances WHERE id = #{0}")
    UserBalances findForId(Long id);

    @Select("SELECT * FROM user_balances WHERE user_id = #{0}")
    List<UserBalances> listForUserId(String userId);

    @Select("SELECT * FROM user_balances WHERE user_id = #{0} AND team_id = #{1}")
    UserBalances find(String userId, String teamId);

    @Select("SELECT EXISTS(SELECT user_id FROM user_balances" +
            " WHERE user_id = #{0} AND team_id = #{1})")
    boolean exists(String userId, String teamId);

    @Select("SELECT SUM(sales_tax) FROM user_balances WHERE user_id = #{0}")
    BigDecimal salesTaxBalanceTotal(String userId);

    @Select("SELECT user_id FROM user_balances WHERE team_id = #{0} AND transaction > 0.50")
    List<String> listUsersForTeamWithTransactionBalance(String teamId);

    @Update("UPDATE user_balances SET sales_tax = (sales_tax + #{1}), e_wallet = (e_wallet + #{2}), transaction = (transaction + #{3})" +
            " WHERE id = #{0}")
    int add(Long id, BigDecimal salesTax, BigDecimal eWallet, BigDecimal transaction);

    @Update("UPDATE user_balances SET sales_tax = (sales_tax + #{1}) WHERE id = #{0}")
    int addSalesTax(Long id, BigDecimal salesTax);

    @Update("UPDATE user_balances SET sales_tax = (sales_tax - #{1}) WHERE id = #{0}")
    int subtractSalesTax(Long id, BigDecimal salesTax);

    @Update("UPDATE user_balances SET transaction = (transaction + #{1}) WHERE id = #{0}")
    int addTransaction(Long id, BigDecimal transaction);

    @Update("UPDATE user_balances SET transaction = (transaction - #{1}) WHERE id = #{0}")
    int subtractTransaction(Long id, BigDecimal transaction);

    @Update("UPDATE user_balances SET e_wallet = (e_wallet + #{1}) WHERE id = #{0}")
    int addEWallet(Long id, BigDecimal eWallet);

    @Update("UPDATE user_balances SET e_wallet = (e_wallet - #{1}) WHERE id = #{0}")
    int subtractEWallet(Long id, BigDecimal eWallet);

    @Update("UPDATE user_balances SET e_wallet = (e_wallet - #{1}) WHERE id = #{0} AND e_wallet >= #{1}")
    int subtractEWalletSafe(Long id, BigDecimal eWallet);

    /**
     * Defaults balances to zero, ignores duplicate requests
     */
    @Insert("INSERT INTO user_balances(user_id, team_id) VALUES(#{userId}, #{teamId}) ON DUPLICATE KEY UPDATE user_id = user_id")
    @Options(useGeneratedKeys = true, keyColumn = "id")
    int insert(UserBalances userBalances);
}
