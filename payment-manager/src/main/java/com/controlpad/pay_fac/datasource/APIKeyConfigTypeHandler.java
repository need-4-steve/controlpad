package com.controlpad.pay_fac.datasource;

import com.controlpad.pay_fac.api_key.APIKeyConfig;
import com.google.gson.Gson;
import org.apache.ibatis.type.BaseTypeHandler;
import org.apache.ibatis.type.JdbcType;
import org.apache.ibatis.type.MappedTypes;

import java.sql.CallableStatement;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

@MappedTypes(APIKeyConfig.class)
public class APIKeyConfigTypeHandler extends BaseTypeHandler<APIKeyConfig> {

    private Gson gson = new Gson();

    @Override
    public void setNonNullParameter(PreparedStatement ps, int i,
                                    APIKeyConfig parameter, JdbcType jdbcType) throws SQLException {
        ps.setObject(i, gson.toJson(parameter));
    }

    @Override
    public APIKeyConfig getNullableResult(ResultSet rs, String columnName)
            throws SQLException {
        return gson.fromJson(rs.getString(columnName), APIKeyConfig.class);
    }

    @Override
    public APIKeyConfig getNullableResult(ResultSet rs, int columnIndex)
            throws SQLException {
        return gson.fromJson(rs.getString(columnIndex), APIKeyConfig.class);
    }

    @Override
    public APIKeyConfig getNullableResult(CallableStatement cs, int columnIndex)
            throws SQLException {
        return gson.fromJson(cs.getString(columnIndex), APIKeyConfig.class);
    }
}