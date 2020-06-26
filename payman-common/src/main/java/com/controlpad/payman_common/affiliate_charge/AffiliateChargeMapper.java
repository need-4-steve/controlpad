package com.controlpad.payman_common.affiliate_charge;


import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.MapKey;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;

import java.math.BigDecimal;
import java.util.HashMap;
import java.util.List;

public interface AffiliateChargeMapper {

    @Select("SELECT * FROM affiliate_charges WHERE transaction_id = #{0}")
    List<AffiliateCharge> listForTransactionId(String transactionId);

    @Select("SELECT * FROM affiliate_charges WHERE payee_user_id = #{0} LIMIT #{offset}, #{count}")
    List<AffiliateCharge> listPaginatedForUserId(String payeeUserId, @Param("offset") Long offset, @Param("count") Integer count);

    @Select("SELECT #{0} AS transactionId, ac.payee_user_id, SUM(ac.amount) AS amount FROM affiliate_charges AS ac" +
            " LEFT JOIN transactions AS t ON t.id = ac.transaction_id" +
            " WHERE (t.id = #{0} OR t.for_txn_id = #{0})" +
            " GROUP BY ac.payee_user_id")
    @MapKey("payeeUserId")
    HashMap<String, AffiliateCharge> mapAffiliateChargeTotalsForTransaction(String transactionId);

    @Insert("INSERT INTO affiliate_charges(transaction_id, payee_user_id, amount) VALUES(#{transactionId}, #{payeeUserId}, #{amount})")
    int insert(AffiliateCharge affiliateCharge);

    @Insert("INSERT INTO affiliate_charges(transaction_id, payee_user_id, amount) VALUES(#{0}, #{1}, #{2})")
    int insertCustom(String transactionId, String payeeUserId, BigDecimal amount);

    @Insert("<script>" +
            " INSERT INTO affiliate_charges(transaction_id, payee_user_id, amount)" +
            " VALUES" +
            " <foreach item='item' index='index' collection='list'" +
            " open='(' separator='),(' close=')'>" +
            " #{item.transactionId}, #{item.payeeUserId}, #{item.amount}" +
            " </foreach>" +
            "</script>")
    int insertList(List<AffiliateCharge> affiliateChargeList);

}