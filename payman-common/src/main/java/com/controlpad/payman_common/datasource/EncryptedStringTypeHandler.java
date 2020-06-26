/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_common.datasource;

import com.controlpad.payman_common.util.EncryptUtil;
import org.apache.ibatis.type.BaseTypeHandler;
import org.apache.ibatis.type.JdbcType;

import java.sql.CallableStatement;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

public abstract class EncryptedStringTypeHandler extends BaseTypeHandler<String> {

    private EncryptUtil encryptUtil;

    public EncryptedStringTypeHandler() {
        encryptUtil = EncryptUtil.getInstance();
    }

    protected abstract byte[] getKey();

    @Override
    public void setNonNullParameter(PreparedStatement ps, int i, String parameter, JdbcType jdbcType) throws SQLException {
        ps.setBytes(i, encryptUtil.encryptString(getKey(), parameter));
    }

    @Override
    public String getNullableResult(ResultSet rs, String columnName) throws SQLException {
        byte[] data = rs.getBytes(columnName);
        if (data == null)
            return null;
        return encryptUtil.decryptString(getKey(), data);
    }

    @Override
    public String getNullableResult(ResultSet rs, int columnIndex) throws SQLException {
        byte[] data = rs.getBytes(columnIndex);
        if (data == null)
            return null;
        return encryptUtil.decryptString(getKey(), data);
    }

    @Override
    public String getNullableResult(CallableStatement cs, int columnIndex) throws SQLException {
        return null;
    }
}
