package com.controlpad.payman_common.merchant;

import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.MapKey;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

import java.util.HashMap;
import java.util.List;

public interface MerchantMapper {

    @Select("SELECT * FROM merchants WHERE id = #{0}")
    Merchant findById(String id);

    @Select("SELECT * FROM merchants WHERE type = 'company'")
    @MapKey("id")
    HashMap<String, Merchant> mapAllCompanyMerchants();

    @Select("SELECT EXISTS(SELECT id FROM merchants WHERE id = #{0})")
    Boolean existsForId(String id);

    @Select("SELECT * FROM merchants")
    @MapKey("id")
    HashMap<String, Merchant> mapAll();

    @Insert("INSERT INTO merchants(id, email, type) VALUES(#{id}, #{email}, #{type})" +
            " ON DUPLICATE KEY UPDATE email = IF(email <> #{email}, #{email}, email)")
    int insert(Merchant merchant);

    @Update("UPDATE merchants SET email = #{email} WHERE id = #{id}")
    int updateEmail(Merchant merchant);

    @Update("UPDATE merchants SET email = #{1} WHERE id = #{0} AND email <> #{1}")
    int updateEmailIfNeeded(String userId, String email);
}
