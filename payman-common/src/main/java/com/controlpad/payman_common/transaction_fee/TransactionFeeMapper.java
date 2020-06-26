package com.controlpad.payman_common.transaction_fee;


import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Options;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;

import java.math.BigDecimal;
import java.util.List;

public interface TransactionFeeMapper {

    @Select("SELECT * FROM transaction_fees WHERE transaction_id = #{0}")
    List<TransactionFee> listForTransactionId(String transactionId);

    @Select("SELECT SUM(amount) FROM transaction_fees WHERE transaction_id = #{0}")
    BigDecimal totalAmountForTransactionId(String transactionId);

    @Insert("INSERT INTO transaction_fees(transaction_id, gateway_reference_id, description, amount)" +
            " VALUES(#{transactionId}, #{gatewayReferenceId}, #{description}, #{amount})")
    @Options(useGeneratedKeys = true, keyColumn = "id")
    int insert(TransactionFee transactionFee);

    @Insert("<script>" +
            "INSERT INTO transaction_fees(transaction_id, gateway_reference_id, description, amount)" +
            " VALUES" +
            " <foreach item='item' index='index' collection='list'" +
            " open='(' separator='),(' close=')'>" +
            " #{item.transactionId}, #{item.gatewayReferenceId}, #{item.description}, #{item.amount}" +
            " </foreach>" +
            "</script>")
    int insertList(@Param("list") List<TransactionFee> transactionFees);
}
