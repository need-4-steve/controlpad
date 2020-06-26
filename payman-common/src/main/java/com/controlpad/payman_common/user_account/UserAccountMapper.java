package com.controlpad.payman_common.user_account;

import org.apache.ibatis.annotations.*;
import org.apache.ibatis.type.JdbcType;

import java.util.HashMap;
import java.util.List;

public interface UserAccountMapper {

    @Results(value = {
            @Result(column = "number", property = "number", jdbcType = JdbcType.BINARY, javaType = String.class, typeHandler = com.controlpad.payman_common.datasource.AccountNumberTypeHandler.class)
    })
    @Select("SELECT user_id, name, routing, number, hash, created_at, updated_at," +
            " (SELECT slug FROM account_type WHERE id = type_id) AS type, bank_name, validated" +
            " FROM user_accounts WHERE user_id = #{0}")
    UserAccount findAccountForUserId(String userId);

    @Select("SELECT EXISTS(SELECT user_id FROM user_accounts WHERE user_id = #{0})")
    boolean existsUserAccount(String userId);

    @Select("SELECT validated FROM user_accounts where user_id = #{0}")
    Boolean isAccountValidatedForUserId(String userId);

    @Results(value = {
            @Result(column = "number", property = "number", jdbcType = JdbcType.BINARY, javaType = String.class, typeHandler = com.controlpad.payman_common.datasource.AccountNumberTypeHandler.class)
    })
    @Select("SELECT user_id, name, number, routing, (SELECT slug FROM account_type WHERE id = type_id) AS type, validated" +
            " FROM user_accounts")
    @MapKey("userId")
    HashMap<String, UserAccount> mapUserAccounts();

    @Insert("INSERT INTO user_accounts (user_id, name, number, routing, type_id, bank_name, validated, hash)" +
            " VALUES (#{userId}, #{name}, #{number,jdbcType=BINARY,javaType=java.lang.String,typeHandler=com.controlpad.payman_common.datasource.AccountNumberTypeHandler}," +
            " #{routing}, (SELECT id FROM account_type WHERE slug = #{type}), #{bankName}, #{validated}, #{hash})" +
            " ON DUPLICATE KEY UPDATE name = #{name}, number = VALUES(number), routing = #{routing}," +
            " type_id = VALUES(type_id), bank_name = #{bankName}, validated = #{validated}")
    int insert(UserAccount account);

    @Update("UPDATE user_accounts SET name = #{name}, number = #{number,jdbcType=BINARY,javaType=java.lang.String,typeHandler=com.controlpad.payman_common.datasource.AccountNumberTypeHandler}," +
            " routing = #{routing}, type_id = (SELECT id FROM account_type WHERE slug = #{type}), bank_Name = #{bankName}, validated = #{validated}, hash = #{hash}" +
            " WHERE user_id = #{userId}")
    int updateUserAccount(UserAccount account);

    @Update("UPDATE user_accounts SET validated = true WHERE user_id = #{userId}")
    int markAccountValid(UserAccount userAccount);

    @Update("UPDATE user_accounts SET validated = 0 WHERE user_id = #{0}")
    int markAccountInvalid(String userId);

    @Select("SELECT user_id FROM user_accounts WHERE validated = 1")
    List<Long> listUserAccountsValidated();

    @Select("SELECT count(user_id) FROM user_accounts WHERE validated = 1 AND user_id IN #{0}")
    int getValidatedAccountForUsers(List<String> users);

    //Account Validations

    @Select("SELECT created_at FROM user_account_validation WHERE user_id = #{0} ORDER BY created_at DESC LIMIT 1")
    String findCurrentValidationDateForUser(String userId);

    @Select("SELECT EXISTS(SELECT id FROM user_account_validation WHERE user_id = #{0} AND deleted = 0 AND submitted_at IS NULL)")
    boolean isValidationOpenForAccount(String userId);

    @Select("SELECT user_id FROM user_account_validation WHERE id = #{0}")
    String findUserIdForValidationId(Long id);

    @Results({
            @Result(column = "user_account", property = "userAccount", jdbcType = JdbcType.VARCHAR, javaType = UserAccount.class,
                    typeHandler = com.controlpad.payman_common.datasource.UserAccountSerializeTypeHandler.class
            )
    })
    @Select("<script>" +
            "SELECT * FROM user_account_validation" +
            " WHERE 1=1" +
            " <if test='paymentFileId!=null'>AND payment_file_id = #{paymentFileId}</if>" +
            " <if test='userId!=null'>AND user_id = #{userId}</if>" +
            " <if test='sortBy!=null'>ORDER BY ${sortBy}</if>" +
            " LIMIT #{offset}, #{count}" +
            "</script>")
    List<UserAccountValidation> searchValidations(@Param("paymentFileId") Long paymentFileId, @Param("userId") String userId,
                                                  @Param("sortBy") String sortBy, @Param("count") int count, @Param("offset") long offset);

    @Select("<script>" +
            "SELECT COUNT(user_id) FROM user_account_validation" +
            " WHERE 1=1" +
            " <if test='paymentFileId!=null'>AND payment_file_id = #{paymentFileId}</if>" +
            " <if test='userId!=null'>AND user_id = #{userId}</if>" +
            "</script>")
    int getValidationCountForSearch(@Param("paymentFileId") Long paymentFileId, @Param("userId") String userId);

    @Update("UPDATE user_account_validation SET submitted_at = CURRENT_TIMESTAMP, deleted = 0 WHERE id = #{0}")
    int markValidationSubmittedForId(Long id);

    @Update("UPDATE user_account_validation SET submitted_at = CURRENT_TIMESTAMP WHERE payment_file_id = #{0}")
    int markValidationSubmittedForPaymentFileId(Long paymentFileId);

    @Results(value = {
            @Result(column = "number", property = "number", jdbcType = JdbcType.BINARY, javaType = String.class, typeHandler = com.controlpad.payman_common.datasource.AccountNumberTypeHandler.class)
    })
    @Select("SELECT user_id, name, routing, number," +
            " (SELECT slug FROM account_type WHERE id = type_id) AS type, bank_name, validated" +
            " FROM user_accounts LIMIT #{0} OFFSET #{1}")
    List<UserAccount> getAllUserAccountPaginate(int limit, long offset);

    @Select("SELECT COUNT(user_id) FROM user_accounts")
    Long getUserAccountCount();

    @Results(value = {
            @Result(column = "number", property = "number", jdbcType = JdbcType.BINARY, javaType = String.class, typeHandler = com.controlpad.payman_common.datasource.AccountNumberTypeHandler.class)
    })
    @Select("SELECT ua.user_id, ua.name, ua.number, ua.routing, at.slug AS type" +
            " FROM user_accounts AS ua" +
            " JOIN account_type AS at ON at.id = ua.type_id" +
            " LEFT JOIN (select id, user_id, amount1, amount2, account_hash, submitted_at, payment_file_id, created_at from user_account_validation where id in (select max(id) from user_account_validation group by user_id)) AS uav ON uav.user_id = ua.user_id" +
            " WHERE ua.validated = 0 AND (uav.account_hash is null OR uav.account_hash <> ua.hash)" +
            " ORDER BY ua.user_id")
    List<UserAccount> listForValidationNeeded();

    @Results({
            @Result(column = "user_account", property = "userAccount", jdbcType = JdbcType.VARCHAR, javaType = UserAccount.class,
                    typeHandler = com.controlpad.payman_common.datasource.UserAccountSerializeTypeHandler.class
            )
    })
    @Select("SELECT * FROM user_account_validation WHERE user_id = #{0} ORDER BY id DESC LIMIT 1")
    UserAccountValidation findCurrentUserAccountValidation(String userId);

    @Insert("INSERT INTO user_account_validation(user_id, amount1, amount2, account_hash, user_account)" +
            " VALUES(#{userId}, #{amount1}, #{amount2}, #{accountHash}," +
            " #{userAccount,jdbcType=VARCHAR,javaType=com.controlpad.payman_common.user_account.UserAccount," +
            "typeHandler=com.controlpad.payman_common.datasource.UserAccountSerializeTypeHandler})")
    @Options(useGeneratedKeys = true, keyColumn = "id")
    int insertUserAccountValidation(UserAccountValidation userAccountValidation);

    @Insert("<script>" +
            "INSERT INTO user_account_validation(user_id, amount1, amount2, account_hash, user_account)" +
            " VALUES" +
            " <foreach item='item' index='index' collection='list'" +
            " open='(' separator='),(' close=')'>" +
            " #{item.userId}, #{item.amount1}, #{item.amount2}, #{item.accountHash}," +
            " #{item.userAccount,jdbcType=VARCHAR,javaType=com.controlpad.payman_common.user_account.UserAccount," +
            "   typeHandler=com.controlpad.payman_common.datasource.UserAccountSerializeTypeHandler" +
            " }" +
            " </foreach>" +
            "</script>")
    int insertUserAccountValidations(@Param("list") List<UserAccountValidation> validations);

    @Update("<script>" +
            "UPDATE user_account_validation SET payment_file_id = #{paymentFileId} WHERE id IN " +
            " <foreach item='item' index='index' collection='list' open='(' separator=',' close=')'>" +
            " #{item.id}" +
            " </foreach>" +
            "</script>")
    int updateValidationPaymentFile(@Param("list") List<UserAccountValidation> validations, @Param("paymentFileId") Long paymentFileId);


    //DEV and testing

    /**
     * Only for use with sandbox accounts and dev testing
     */
    @Insert("INSERT INTO user_account_validation(user_id, amount1, amount2, account_hash, payment_file_id, user_account)" +
            " VALUES (#{userId}, #{amount1}, #{amount2}, #{accountHash}, #{paymentFileId}," +
            " #{userAccount,jdbcType=VARCHAR,javaType=com.controlpad.payman_common.user_account.UserAccount," +
            "   typeHandler=com.controlpad.payman_common.datasource.UserAccountSerializeTypeHandler" +
            " }" +
            ")")
    @Options(useGeneratedKeys = true, keyColumn = "id")
    int insertDevAccountValidation(UserAccountValidation accountValidation);

    @Update("UPDATE user_account_validation SET account_hash = #{accountHash} WHERE id = #{id}")
    int updateDevAccountValidationHash(UserAccountValidation userAccountValidation);

}