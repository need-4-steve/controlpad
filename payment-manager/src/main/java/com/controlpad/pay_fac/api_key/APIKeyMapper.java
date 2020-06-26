/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.api_key;

import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

import java.util.List;

public interface APIKeyMapper {

    @Select("SELECT * FROM api_keys WHERE client_id = #{0} AND deleted = False")
    List<APIKey> findAPIKeysForClientId(String clientId);

    @Select("SELECT * FROM api_keys WHERE deleted = False")
    List<APIKey> listValidatedAPIKeys();

    @Select("SELECT * FROM api_keys WHERE id = #{0} AND deleted = False")
    APIKey findAPIKeyForId(String id);

    @Select("SELECT (COUNT(id) >= #{1}) FROM api_keys WHERE client_id = #{0} AND deleted = False")
    boolean isCountMax(String clientId, Integer maxCount);

    @Select("SELECT EXISTS(SELECT id FROM api_keys WHERE id = #{0} AND deleted = False)")
    boolean existsForId(String apiKey);

    @Update("UPDATE api_keys SET config = #{config} WHERE id = #{id}")
    int updateAPIKeyConfig(APIKey apiKey);

    @Update("UPDATE api_keys SET disabled = #{disabled} WHERE id = #{id}")
    int updateAPIKeyDisable(APIKey apiKey);

    @Update("UPDATE api_keys SET deleted = true WHERE id = #{0}")
    int deleteAPIKey(String apiKeyId);

    @Insert("INSERT INTO api_keys(id, client_id, config) VALUES(#{id}, #{clientId}, #{config})")
    int insertAPIKey(APIKey apiKey);

    /*-----------------*/

    @Select("SELECT client_id FROM api_keys WHERE id = #{0}")
    String findClientIdForApiKey(String apiKey);
}
