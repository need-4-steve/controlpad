/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.auth;

import com.controlpad.payman_common.payman_user.PayManUser;
import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

public interface AuthMapper {

    @Select("SELECT * FROM users WHERE username = #{0}")
    PayManUser findUserForUsername(String username);

    /**
     *
     * @param sessionId SessionKey from Authorization header
     * @return User id and clientId
     */
    @Select("SELECT id, client_id, privilege FROM users WHERE id = (SELECT user_id FROM sessions WHERE id = #{0} AND expires_at > (unix_timestamp() * 1000))")
    PayManUser findUserForSession(String sessionId);

    @Select("SELECT EXISTS(SELECT id FROM sessions WHERE id = #{0})")
    boolean existsSessionKey(String key);

    @Select("SELECT * FROM sessions WHERE user_id = #{0} AND expires_at > (unix_timestamp() * 1000) ORDER BY created_at DESC LIMIT 1")
    Session findOpenSessionForUserId(String userId);

    @Select("SELECT * FROM sessions WHERE id = #{0}")
    Session findSessionForId(String sessionKey);
    /**
     * Insert new session record.
     * Preferred to use {@link AuthUtil#generateNewSession(String, String)} when creating a session for insert
     */
    @Insert("INSERT INTO sessions(id, user_id, client_id, expires_at) VALUES(#{id}, #{userId}, #{clientId}, #{expiresAt})")
    int insertSession(Session session);

    @Update("UPDATE sessions SET expires_at = #{expiresAt} WHERE id = #{id}")
    int updateSessionExpiration(Session session);

    @Update("UPDATE sessions SET expires_at = #{1} WHERE id = #{0}")
    int updateSessionKeyExpiration(String sessionKey, long expiresAt);

    @Update("UPDATE sessions set client_id = #{1} WHERE id = #{0}")
    int updateSessionClientId(String sessionKey, String clientId);
}
