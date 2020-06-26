/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.client;

import com.controlpad.payman_common.datasource.SqlConfig;
import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

import java.util.List;

public interface ClientMapper {

    @Select("SELECT * FROM clients WHERE id = #{0}")
    ControlPadClient findClientForId(String clientId);

    @Select("SELECT id, name, config, created_at FROM clients WHERE id = #{0}")
    ControlPadClient findClientNoSqlForId(String clientId);

    @Select("SELECT EXISTS (SELECT id FROM clients WHERE id = #{0})")
    boolean existsForClientId(String clientId);

    @Select("SELECT * FROM clients")
    List<ControlPadClient> findAllClients();

    @Select("SELECT id, org_id, name, config, is_sandbox, created_at, updated_at FROM clients")
    List<ControlPadClient> findAllClientsSecure();

    @Select("SELECT * FROM clients LIMIT #{offset}, #{count}")
    List<ControlPadClient> listClients(@Param("offset") Long offset, @Param("count") Integer count);

    @Select("SELECT id, org_id, name, config, is_sandbox, created_at, updated_at FROM clients LIMIT #{offset}, #{count}")
    List<ControlPadClient> listClientsSecure(@Param("offset") Long offset, @Param("count") Integer count);

    @Select("SELECT name FROM clients WHERE id = #{0}")
    String getClientNameForId(String clientId);

    @Update("UPDATE clients SET sql_config_write = #{1} WHERE id = #{0}")
    int updateClientSqlConfigWrite(String clientId, SqlConfig sqlConfig);

    @Update("UPDATE clients SET sql_config_read = #{1} WHERE id = #{0}")
    int updateClientSqlConfigRead(String clientId, SqlConfig sqlConfig);

    @Update("UPDATE clients SET config = #{1} WHERE id = #{0}")
    int updateClientConfig(String clientId, ClientConfig clientConfig);

    @Insert("INSERT INTO clients(id, name, config, sql_config_write, sql_config_read, is_sandbox)" +
            " VALUES(#{id}, #{name}, #{config}, #{sqlConfigWrite}, #{sqlConfigRead}, #{sandbox})")
    int insertClient(ControlPadClient controlPadClient);
}