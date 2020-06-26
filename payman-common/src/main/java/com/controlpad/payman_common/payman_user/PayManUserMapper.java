package com.controlpad.payman_common.payman_user;

import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.SelectKey;
import org.apache.ibatis.annotations.Update;

public interface PayManUserMapper {

    @Select("SELECT EXISTS(SELECT id FROM users WHERE username = #{0})")
    Boolean existsForUsername(String username);

    @Insert("INSERT INTO users(id, client_id, username, password, email, privilege) VALUES(#{id}, #{clientId}, #{username}, #{password}, #{email}, #{privilege})")
    int insertPayManUser(PayManUser payManUser);

}
