/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.transaction_charge;

import org.apache.ibatis.annotations.*;

import java.math.BigDecimal;
import java.util.List;

public interface TransactionChargeMapper {

    @Select("SELECT *, (SELECT slug FROM payment_type WHERE id = type_id)" +
            " AS type FROM transaction_charges WHERE id = #{0}")
    TransactionCharge findById(Long transactionChargeId);

    @Select("SELECT tc.*, pt.slug AS type FROM transaction_charges AS tc" +
            " JOIN payment_type AS pt ON pt.id = tc.type_id" +
            " WHERE tc.transaction_id = #{0}")
    List<TransactionCharge> listForTransactionId(String transactionId);

    @Insert("INSERT INTO transaction_charges(user_id, transaction_id, account_id, amount, fee_id, type_id)" +
            " VALUES(#{userId}, #{transactionId}, #{accountId}, #{amount}, #{feeId}, #{typeId})")
    @Options(useGeneratedKeys = true, keyColumn = "id")
    int insert(TransactionCharge transactionCharge);

    // Processing

    @Select("SELECT tc.*, pt.slug AS type, @tctotal:=@tctotal + tc.amount" +
            " FROM transaction_charges AS tc JOIN payment_type AS pt ON tc.type_id = pt.id" +
            " WHERE tc.user_id = #{0} AND tc.processed = 0 AND @tctotal + tc.amount <= #{1}")
    List<TransactionCharge> findUnpaidChargesForUserAndTotal(String userId, BigDecimal total);

    @Select("SELECT tc.*, pt.slug AS type, @tctotal:=@tctotal + tc.amount" +
            " FROM transaction_charges AS tc JOIN payment_type AS pt ON tc.type_id = pt.id" +
            " WHERE tc.user_id = #{0} AND tc.type_id = 9 AND tc.processed = 0 AND @tctotal + tc.amount <= #{1}")
    List<TransactionCharge> findUnpaidTaxChargesForUserAndTotal(String userId, BigDecimal total);

    @Select("SELECT SUM(amount)" +
            " FROM transaction_charges" +
            " WHERE user_id = #{0} AND type_id = 9 AND processed = 0 AND payment_id IS NULL")
    BigDecimal sumUnpaidTaxChargesForUser(String userId);

    @Select("SELECT tc.*, pt.slug AS type, @tctotal:=@tctotal + tc.amount" +
            " FROM transaction_charges AS tc JOIN payment_type AS pt ON tc.type_id = pt.id" +
            " WHERE tc.user_id = #{0} AND tc.type_id = 3 AND tc.processed = 0 AND @tctotal + tc.amount <= #{1}")
    List<TransactionCharge> findUnpaidAffiliateChargesForUserAndTotal(String userId, BigDecimal total);

    @Select("SELECT processed FROM transaction_charges WHERE id = #{0}")
    boolean isPaidForId(Long id);

    // This is intended to operate on a session variable tctotal, which happens in mysql
    @Update("SET @tctotal = 0")
    int resetTransactionChargeTotal();

    @Select("SELECT @tctotal")
    BigDecimal getTransactionChargeTotal();

    @Update("UPDATE transaction_charges SET processed = 1, payment_id = #{1} WHERE id = #{0}")
    int markPaid(Long transactionChargeId, String paymentId);

    @Insert("<script>" +
            "INSERT INTO transaction_charges(user_id, transaction_id, account_id, amount, fee_id, type_id)" +
            " VALUES" +
            " <foreach item='item' index='index' collection='list'" +
            " open='(' separator='),(' close=')'>" +
            " #{item.userId}, #{item.transactionId}, #{item.accountId}, #{item.amount}, #{item.feeId}, #{item.typeId}" +
            " </foreach>" +
            "</script>")
    int insertList(@Param("list") List<TransactionCharge> charges);

    // Processing

    @Update("<script>" +
            "UPDATE transaction_charges SET processed = 1, payment_id = #{0} WHERE id IN" +
            " <foreach item='item' index='index' collection='list'" +
            " open='(' separator=',' close=')'>" +
            " #{item}" +
            " </foreach>" +
            "</script>")
    int setPaidForList(String paymentId, @Param("list") List<Long> idList);

}