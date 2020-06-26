package com.controlpad.payman_common.payment_provider;

import org.apache.ibatis.annotations.*;
import org.apache.ibatis.type.JdbcType;

import java.math.BigInteger;
import java.util.List;

public interface PaymentProviderMapper {

    @Results({
            @Result(column = "credentials", property = "credentials", jdbcType = JdbcType.BLOB, javaType = String.class, typeHandler = com.controlpad.payman_common.datasource.PPKeyTypeHandler.class),
    })
    @Select("SELECT * FROM payment_providers WHERE id = #{0}")
    PaymentProvider findById(BigInteger id);

    @Results({
            @Result(column = "credentials", property = "credentials", jdbcType = JdbcType.BLOB, javaType = String.class, typeHandler = com.controlpad.payman_common.datasource.PPKeyTypeHandler.class),
    })
    @Select("SELECT * FROM payment_providers LIMIT #{0} OFFSET #{1}")
    List<PaymentProvider> search(int limit, long offset);

    @Insert("INSERT INTO payment_providers (name, type, credentials, subdomain)" +
            " VALUES (#{name}, #{type}, #{credentials}, #{subdomain})")
    @Options(useGeneratedKeys = true, keyColumn = "id")
    int insert(PaymentProvider paymentProvider);

    @Update("UPDATE payment_providers" +
            " SET credentials = #{credentials,jdbcType=BLOB,javaType=java.lang.String,typeHandler=com.controlpad.payman_common.datasource.PPKeyTypeHandler}" +
            " WHERE id = #{id}")
    int updateConfig(PaymentProvider paymentProvider);
}