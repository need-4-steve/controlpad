/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.consignment;

import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Options;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

import java.math.BigDecimal;

public interface ConsignmentMapper {

    @Select("SELECT * FROM consignments WHERE user_id = #{0}")
    Consignment findForUserId(String userId);

    @Select("SELECT EXISTS(SELECT user_id FROM consignments WHERE user_id = #{0})")
    boolean exists(String userId);

    @Insert("INSERT INTO consignments (user_id, is_percent, amount, balance)" +
            " VALUES (#{userId}, #{isPercent}, #{amount}, #{balance})")
    @Options(useGeneratedKeys = true, keyColumn = "id")
    int insert(Consignment consignment);

    @Update("UPDATE consignments SET is_percent = #{isPercent}, amount = #{amount}, balance = #{balance} WHERE user_id = #{userId}")
    int update(Consignment consignment);

    @Update("UPDATE consignments SET balance = (balance - #{1}) WHERE user_id = #{0} AND balance >= #{1}")
    int subtractBalance(String userId, BigDecimal amount);

    @Update("UPDATE consignments SET balance = #{balance} WHERE user_id = #{userId}")
    int updateBalance(Consignment consignment);

    @Update("UPDATE consignments SET amount = #{amount}, is_percent = #{isPercent} WHERE user_id = #{userId}")
    int updateAmount(Consignment consignment);
}
