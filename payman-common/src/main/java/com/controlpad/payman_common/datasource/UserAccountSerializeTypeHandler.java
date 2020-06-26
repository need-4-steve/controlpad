package com.controlpad.payman_common.datasource;

import com.controlpad.payman_common.user_account.UserAccount;
import com.controlpad.payman_common.util.EncryptUtil;
import com.google.gson.Gson;
import org.apache.ibatis.type.BaseTypeHandler;
import org.apache.ibatis.type.JdbcType;

import java.nio.charset.Charset;
import java.sql.CallableStatement;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Base64;

public class UserAccountSerializeTypeHandler extends BaseTypeHandler<UserAccount> {

    private static final byte[] key = "Oe48VvaIq2.c$CxM".getBytes(Charset.forName("UTF8"));

    private Gson gson = new Gson();
    private EncryptUtil encryptUtil;

    public UserAccountSerializeTypeHandler() {
        encryptUtil = EncryptUtil.getInstance();
    }

    @Override
    public void setNonNullParameter(PreparedStatement ps, int i, UserAccount parameter, JdbcType jdbcType) throws SQLException {
        ps.setString(i, Base64.getEncoder().encodeToString(encryptUtil.encryptString(key, gson.toJson(parameter))));
    }

    @Override
    public UserAccount getNullableResult(ResultSet rs, String columnName) throws SQLException {
        String data = rs.getString(columnName);
        if (data == null)
            return null;

        return gson.fromJson(encryptUtil.decryptString(key, Base64.getDecoder().decode(data)), UserAccount.class);
    }

    @Override
    public UserAccount getNullableResult(ResultSet rs, int columnIndex) throws SQLException {
        String data = rs.getString(columnIndex);
        if (data == null)
            return null;

        return gson.fromJson(encryptUtil.decryptString(key, Base64.getDecoder().decode(data)), UserAccount.class);
    }

    @Override
    public UserAccount getNullableResult(CallableStatement cs, int columnIndex) throws SQLException {
        return null;
    }
}
