package com.controlpad.payman_common.transaction_debit;


import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

import java.util.List;

public interface TransactionDebitMapper {

    @Select("SELECT * FROM transaction_debits WHERE id = #{0}")
    TransactionDebit findById(String id);

    @Select("SELECT * FROM transaction_debits WHERE transaction_id = #{0}")
    List<TransactionDebit> listForTransactionid(String transactionId);

    @Insert("INSERT INTO transaction_debits(id, user_id, transaction_id, account_id, amount)" +
            " VALUES(#{id}, #{userId}, #{transactionId}, #{accountId}, #{amount})")
    int insert(TransactionDebit transactionDebit);

    @Update("UPDATE transaction_debits SET payment_file_id = #{paymentFileId} WHERE id = #{id}")
    int setPaymentFileId(TransactionDebit transactionDebit);

    @Update("UPDATE transaction_debits SET returned = 1 WHERE id = #{0}")
    int markReturned(String id);
}
