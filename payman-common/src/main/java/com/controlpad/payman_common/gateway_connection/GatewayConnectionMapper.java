package com.controlpad.payman_common.gateway_connection;

import org.apache.ibatis.annotations.*;
import org.apache.ibatis.type.JdbcType;

import java.util.HashMap;
import java.util.List;

public interface GatewayConnectionMapper {

    @Results({
            @Result(column = "private_key", property = "privateKey", jdbcType = JdbcType.BINARY, javaType = String.class, typeHandler = com.controlpad.payman_common.datasource.GCKeyTypeHandler.class),
            @Result(column = "pin", property = "pin", jdbcType = JdbcType.BINARY, javaType = String.class, typeHandler = com.controlpad.payman_common.datasource.GCKeyTypeHandler.class)
    })
    @Select("SELECT id, team_id, user_id, name, username, merchant_id, entity_id, private_key, public_key, pin, funds_company, funds_master, process_cards," +
            " process_checks, process_internal, master_connection_id, fee_group_id, is_sandbox, active," +
            " (SELECT slug FROM gateway_connection_type WHERE id = type_id) AS type" +
            " FROM gateway_connections WHERE id = #{0}")
    GatewayConnection findById(Long id);

    @Select("SELECT id, team_id, user_id, name, username, merchant_id, entity_id, funds_company, funds_master, process_cards," +
            " process_checks, process_internal, is_sandbox, active," +
            " (SELECT slug FROM gateway_connection_type WHERE id = type_id) AS type" +
            " FROM gateway_connections WHERE id = #{0}")
    GatewayConnection findByIdSecure(Long id);

    @Results({
            @Result(column = "private_key", property = "privateKey", jdbcType = JdbcType.BINARY, javaType = String.class, typeHandler = com.controlpad.payman_common.datasource.GCKeyTypeHandler.class),
            @Result(column = "pin", property = "pin", jdbcType = JdbcType.BINARY, javaType = String.class, typeHandler = com.controlpad.payman_common.datasource.GCKeyTypeHandler.class)
    })
    @Select("<script>" +
            "SELECT gc.id, gc.team_id, gc.user_id, gc.name, gc.username, gc.merchant_id, gc.entity_id, gc.private_key, gc.public_key, gc.pin, gc.active," +
            " gc.funds_company, gc.funds_master, gc.process_cards, gc.process_checks, gc.process_internal, gc.master_connection_id, gc.fee_group_id, gc.is_sandbox, ct.slug AS type" +
            " FROM gateway_connections AS gc LEFT JOIN gateway_connection_type AS ct ON ct.id = gc.type_id" +
            " WHERE 1=1" +
            " <if test='teamId!=null'>AND gc.team_id = #{teamId}</if>" +
            " <if test='userId!=null'>AND gc.user_id = #{userId}</if>" +
            " <if test='userId==null'>AND gc.user_id IS NULL</if>" +
            " <if test='processCards!=null'>AND gc.process_cards = #{processCards}</if>" +
            " <if test='processChecks!=null'>AND gc.process_checks = #{processChecks}</if>" +
            " <if test='processInternal!=null'>AND gc.process_internal = #{processInternal}</if>" +
            " <if test='type!=null'>AND ct.slug = #{type}</if>" +
            " <if test='active!=null'>AND active = #{active}</if>" +
            " LIMIT #{limit} OFFSET #{offset}" +
            "</script>")
    List<GatewayConnection> search(@Param(value = "teamId") String teamId, @Param(value = "userId") String userId,
                                   @Param(value = "processCards") Boolean processCards,
                                   @Param(value = "processChecks") Boolean processChecks,
                                   @Param(value = "processInternal") Boolean processInternal,
                                   @Param(value = "type") String type,
                                   @Param(value = "active") Boolean active,
                                   @Param(value = "limit") int limit,
                                   @Param(value = "offset") long offset);

    @Select("<script>" +
            "SELECT gc.id, gc.team_id, gc.user_id, gc.name, gc.username, gc.merchant_id, gc.entity_id, gc.public_key, gc.pin," +
            " gc.active, gc.master_connection_id, gc.fee_group_id," +
            " IF((gc.private_key IS NULL), null, '****') AS privateKey," +
            " IF((gc.pin IS NULL), null, '****') AS pin," +
            " gc.funds_company, gc.funds_master, gc.process_cards, gc.process_checks, gc.process_internal, gc.is_sandbox, ct.slug AS type" +
            " FROM gateway_connections AS gc LEFT JOIN gateway_connection_type AS ct ON ct.id = gc.type_id" +
            " WHERE 1=1" +
            " <if test='teamId!=null'>AND gc.team_id = #{teamId}</if>" +
            " <if test='userId!=null'>AND gc.user_id = #{userId}</if>" +
            " <if test='userId==null'>AND gc.user_id IS NULL</if>" +
            " <if test='processCards!=null'>AND gc.process_cards = #{processCards}</if>" +
            " <if test='processChecks!=null'>AND gc.process_checks = #{processChecks}</if>" +
            " <if test='processInternal!=null'>AND gc.process_internal = #{processInternal}</if>" +
            " <if test='type!=null'>AND ct.slug = #{type}</if>" +
            " <if test='active!=null'>AND active = #{active}</if>" +
            " LIMIT #{limit} OFFSET #{offset}" +
            "</script>")
    List<GatewayConnection> searchSecure(@Param(value = "teamId") String teamId, @Param(value = "userId") String userId,
                                         @Param(value = "processCards") Boolean processCards,
                                         @Param(value = "processChecks") Boolean processChecks,
                                         @Param(value = "processInternal") Boolean processInternal,
                                         @Param(value = "type") String type,
                                         @Param(value = "active") Boolean active,
                                         @Param(value = "limit") int limit,
                                         @Param(value = "offset") long offset);

    @Select("<script>" +
            "SELECT gc.id, gc.is_sandbox, ct.slug AS type, gc.active, gc.master_connection_id, gc.fee_group_id," +
            " FROM gateway_connections AS gc LEFT JOIN gateway_connection_type AS ct ON ct.id = gc.type_id" +
            " WHERE 1=1" +
            " <if test='teamId!=null'>AND gc.team_id = #{teamId}</if>" +
            " <if test='userId!=null'>AND gc.user_id = #{userId}</if>" +
            " <if test='userId==null'>AND gc.user_id IS NULL</if>" +
            " <if test='processCards!=null'>AND process_cards = #{processCards}</if>" +
            " <if test='processChecks!=null'>AND process_checks = #{processChecks}</if>" +
            " <if test='processInternal!=null'>AND process_internal = #{processInternal}</if>" +
            " <if test='type!=null'>AND ct.slug = #{type}</if>" +
            " <if test='active!=null'>AND active = #{active}</if>" +
            " LIMIT #{limit} OFFSET #{offset}" +
            "</script>")
    List<GatewayConnection> searchBasic(@Param(value = "teamId") String teamId, @Param(value = "userId") String userId,
                                        @Param(value = "processCards") Boolean processCards,
                                        @Param(value = "processChecks") Boolean processChecks,
                                        @Param(value = "processInternal") Boolean processInternal,
                                        @Param(value = "type") String type,
                                        @Param(value = "active") Boolean active,
                                        @Param(value = "limit") int limit,
                                        @Param(value = "offset") long offset);

    @Select("<script>" +
            "SELECT COUNT(gc.id)" +
            " FROM gateway_connections AS gc JOIN gateway_connection_type AS ct ON ct.id = gc.type_id" +
            " WHERE 1=1" +
            " <if test='teamId!=null'>AND gc.team_id = #{teamId}</if>" +
            " <if test='userId!=null'>AND gc.user_id = #{userId}</if>" +
            " <if test='userId==null'>AND gc.user_id IS NULL</if>" +
            " <if test='processCards!=null'>AND process_cards = #{processCards}</if>" +
            " <if test='processChecks!=null'>AND process_checks = #{processChecks}</if>" +
            " <if test='processInternal!=null'>AND process_internal = #{processInternal}</if>" +
            " <if test='type!=null'>AND ct.slug = #{type}</if>" +
            " <if test='active!=null'>AND active = #{active}</if>" +
            "</script>")
    Long searchCount(@Param(value = "teamId") String teamId, @Param(value = "userId") String userId,
                     @Param(value = "processCards") Boolean processCards,
                     @Param(value = "processChecks") Boolean processChecks,
                     @Param(value = "processInternal") Boolean processInternal,
                     @Param(value = "type") String type,
                     @Param(value = "active") Boolean active);

    @Select("<script>" +
            "SELECT gc.id" +
            " FROM gateway_connections AS gc LEFT JOIN gateway_connection_type AS ct ON ct.id = gc.type_id" +
            " WHERE 1=1" +
            " <if test='teamId!=null'>AND gc.team_id = #{teamId}</if>" +
            " <if test='userId!=null'>AND gc.user_id = #{userId}</if>" +
            " <if test='userId==null'>AND gc.user_id IS NULL</if>" +
            " <if test='processCards!=null'>AND process_cards = #{processCards}</if>" +
            " <if test='processChecks!=null'>AND process_checks = #{processChecks}</if>" +
            " <if test='processInternal!=null'>AND process_internal = #{processInternal}</if>" +
            " <if test='type!=null'>AND ct.slug = #{type}</if>" +
            " <if test='active!=null'>AND active = #{active}</if>" +
            "</script>")
    List<Long> searchId(@Param(value = "teamId") String teamId, @Param(value = "userId") String userId,
                                        @Param(value = "processCards") Boolean processCards,
                                        @Param(value = "processChecks") Boolean processChecks,
                                        @Param(value = "processInternal") Boolean processInternal,
                                        @Param(value = "type") String type,
                                        @Param(value = "active") Boolean active);

    @Select("SELECT id FROM gateway_connections WHERE team_id = #{0} AND user_id IS NOT NULL")
    List<Long> listUserConnectionIdsForTeamId(String teamId);

    @Select("SELECT id FROM gateway_connections")
    List<Long> listAllIds();

    @Select("SELECT id, team_id, user_id, funds_company, funds_master FROM gateway_connections")
    @MapKey("id")
    HashMap<Long, GatewayConnection> mapAllFundingStatus();

    @Results({
            @Result(column = "private_key", property = "privateKey", jdbcType = JdbcType.BINARY, javaType = String.class, typeHandler = com.controlpad.payman_common.datasource.GCKeyTypeHandler.class),
            @Result(column = "pin", property = "pin", jdbcType = JdbcType.BINARY, javaType = String.class, typeHandler = com.controlpad.payman_common.datasource.GCKeyTypeHandler.class)
    })
    @Select("SELECT id, team_id, user_id, name, username, merchant_id, entity_id, private_key, public_key, pin, funds_company, funds_master," +
            " process_cards, process_checks, process_internal, master_connection_id, fee_group_id, active, is_sandbox," +
            " (SELECT slug FROM gateway_connection_type WHERE id = type_id) AS type" +
            " FROM gateway_connections WHERE id = (SELECT gateway_connection_id FROM transactions WHERE id = #{0})")
    GatewayConnection findForTransactionId(String transactionId);

    @Results({
            @Result(column = "private_key", property = "privateKey", jdbcType = JdbcType.BINARY, javaType = String.class, typeHandler = com.controlpad.payman_common.datasource.GCKeyTypeHandler.class),
            @Result(column = "pin", property = "pin", jdbcType = JdbcType.BINARY, javaType = String.class, typeHandler = com.controlpad.payman_common.datasource.GCKeyTypeHandler.class)
    })
    @Select("SELECT id, team_id, user_id, name, username, merchant_id, entity_id, private_key, public_key, pin, funds_company, funds_master," +
            " process_cards, process_checks, process_internal, master_connection_id, fee_group_id, active, is_sandbox," +
            " (SELECT slug FROM gateway_connection_type WHERE id = type_id) AS type" +
            " FROM gateway_connections WHERE id = (SELECT gateway_connection_id FROM transaction_batches WHERE id = #{0})")
    GatewayConnection findForTransactionBatchId(Long transactionBatchId);

    @Insert("INSERT INTO gateway_connections (name, team_id, user_id, username, merchant_id, entity_id, is_sandbox, funds_company, funds_master," +
            " process_cards, process_checks, process_internal, master_connection_id, fee_group_id, active, public_key, private_key, pin, type_id)" +
            " VALUES (" +
            " #{name}, #{teamId}, #{userId}, #{username}, #{merchantId}, #{entityId}, #{isSandbox}, #{fundsCompany}, #{fundsMaster}," +
            " #{processCards}, #{processChecks}, #{processInternal}, #{masterConnectionId}, #{feeGroupId}, #{active}, #{publicKey}," +
            " #{privateKey,jdbcType=BINARY,javaType=java.lang.String,typeHandler=com.controlpad.payman_common.datasource.GCKeyTypeHandler}," +
            " #{pin,jdbcType=BINARY,javaType=java.lang.String,typeHandler=com.controlpad.payman_common.datasource.GCKeyTypeHandler}," +
            " (SELECT id FROM gateway_connection_type WHERE slug = #{type}))")
    @Options(useGeneratedKeys = true, keyColumn = "id")
    int insert(GatewayConnection gatewayConnection);

    /**
     * NOT UPDATING fundsCompany, teamId, userId, type, isSandbox because a connection reference should never be a different account
     * Only changing credentials and settings
     * @param gatewayConnection
     */
    @Update("UPDATE gateway_connections" +
            " SET name = #{name}, username = #{username}," +
            " public_key = #{publicKey}, process_cards = #{processCards}, process_checks = #{processChecks}," +
            " process_internal = #{processInternal}, fee_group_id = #{feeGroupId}, active = #{active}," +
            " private_key = #{privateKey,jdbcType=BINARY,javaType=java.lang.String,typeHandler=com.controlpad.payman_common.datasource.GCKeyTypeHandler}," +
            " pin = #{pin,jdbcType=BINARY,javaType=java.lang.String,typeHandler=com.controlpad.payman_common.datasource.GCKeyTypeHandler}" +
            " WHERE id = #{id}")
    int update(GatewayConnection gatewayConnection);

    /**
     * NOT UPDATING fundsCompany, teamId, userId, type, isSandbox because a connection reference should never be a different account
     * Only changing credentials and settings
     * @param gatewayConnection
     */
    @Update("<script>" +
            " <if test='name!=null'>SET name = #{name}</if>" +
            " <if test='username!=null'>SET username = #{username}</if>" +
            " <if test='privateKey!=null'>SET private_key = #{privateKey,jdbcType=BINARY,javaType=java.lang.String,typeHandler=com.controlpad.payman_common.datasource.GCKeyTypeHandler}</if>" +
            " <if test='publicKey!=null'>SET public_key = #{publicKey}</if>" +
            " <if test='pin!=null'>SET pin = #{pin,jdbcType=BINARY,javaType=java.lang.String,typeHandler=com.controlpad.payman_common.datasource.GCKeyTypeHandler}</if>" +
            " <if test='processCards!=null'>SET process_cards = #{processCards}</if>" +
            " <if test='processChecks!=null'>SET process_checks = #{processChecks}</if>" +
            " <if test='processInternal!=null'>SET process_internal = #{processInternal}</if>" +
            " <if test='masterConnectionId!=null'>SET master_connection_id = #{masterConnectionId}</if>" +
            " <if test='feeGroupId!=null'>SET fee_group_id = #{feeGroupId}</if>" +
            " <if test='active!=null'>SET active = #{active}</if>" +
            "</script>")
    int patch(GatewayConnection gatewayConnection);

    @Select("SELECT EXISTS (SELECT id FROM gateway_connections WHERE id = #{0})")
    boolean existsForId(Long id);
}