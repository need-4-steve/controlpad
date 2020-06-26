/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.ach;

import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

import java.util.List;

public interface ACHMapper {

    @Select("SELECT * FROM ach")
    List<ACH> list();

    @Select("SELECT * FROM ach WHERE id = #{0}")
    ACH findForId(Long id);

    @Select("SELECT EXISTS(SELECT company_id FROM ach WHERE id = #{0})")
    boolean existsForId(Long id);

    @Update("UPDATE ach SET origin_route = #{originRoute}, destination_route = #{destinationRoute}, origin_name = #{originName}," +
            " company_name = #{companyName}, company_id = #{companyId}, destination_name = #{destinationName}" +
            " WHERE id = #{id}")
    int updateById(ACH ach);

    @Insert("INSERT INTO ach(id, origin_route, destination_route, origin_name, company_name, company_id, destination_name)" +
            "VALUES(#{id}, #{originRoute}, #{destinationRoute}, #{originName}, #{companyName}, #{companyId}, #{destinationName})")
    int insert(ACH ach);
}
