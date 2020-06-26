/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.account;

import org.apache.ibatis.annotations.*;
import org.apache.ibatis.type.JdbcType;

public interface AccountMapper {

    @Results({
            @Result(column = "number", property = "number", jdbcType = JdbcType.BINARY, javaType = String.class, typeHandler = com.controlpad.payman_common.datasource.AccountNumberTypeHandler.class)
    })
    @Select("SELECT id, name, number, routing," +
            " (SELECT name FROM account_type WHERE id = type_id) AS type, bank_name FROM accounts WHERE id = #{id}")
    Account findForId(Long id);

    @Select("SELECT EXISTS(SELECT id FROM accounts WHERE id = #{0})")
    boolean existsForId(Long id);

    @Insert("INSERT INTO accounts (name, number, routing, type_id, bank_name, hash)" +
            " VALUES (#{name}, #{number,jdbcType=BINARY,javaType=java.lang.String,typeHandler=com.controlpad.payman_common.datasource.AccountNumberTypeHandler}," +
            " #{routing}, (SELECT id FROM account_type WHERE slug = #{type}), #{bankName}, #{hash})")
    @Options(useGeneratedKeys = true, keyColumn = "id")
    int insert(Account account);

    @Update("UPDATE accounts SET name = #{name}, number = #{number,jdbcType=BINARY,javaType=java.lang.String,typeHandler=com.controlpad.payman_common.datasource.AccountNumberTypeHandler}," +
            " routing = #{routing}, type_id = (SELECT id FROM account_type WHERE slug = #{type}), bank_name = #{bankName}, hash = #{hash}" +
            " WHERE id = #{id}")
    int update(Account account);
}