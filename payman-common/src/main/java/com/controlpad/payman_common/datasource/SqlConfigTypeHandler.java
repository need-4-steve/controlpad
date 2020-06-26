package com.controlpad.payman_common.datasource;

import com.google.gson.Gson;
import org.apache.ibatis.type.BaseTypeHandler;
import org.apache.ibatis.type.JdbcType;

import java.sql.CallableStatement;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

public class SqlConfigTypeHandler extends BaseTypeHandler<SqlConfig> {


    private Gson gson = new Gson();

    @Override
    public void setNonNullParameter(PreparedStatement ps, int i, SqlConfig parameter, JdbcType jdbcType) throws SQLException {
        ps.setString(i, gson.toJson(parameter));
    }

    @Override
    public SqlConfig getNullableResult(ResultSet rs, String columnName) throws SQLException {
        return gson.fromJson(rs.getString(columnName), SqlConfig.class);
    }

    @Override
    public SqlConfig getNullableResult(ResultSet rs, int columnIndex) throws SQLException {
        return gson.fromJson(rs.getString(columnIndex), SqlConfig.class);
    }

    @Override
    public SqlConfig getNullableResult(CallableStatement cs, int columnIndex) throws SQLException {
        return gson.fromJson(cs.getString(columnIndex), SqlConfig.class);
    }
}
