package com.controlpad.payman_common.datasource;

import com.controlpad.payman_common.client.ClientConfig;
import com.google.gson.Gson;
import org.apache.ibatis.type.BaseTypeHandler;
import org.apache.ibatis.type.JdbcType;
import org.apache.ibatis.type.MappedTypes;

import java.sql.CallableStatement;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

@MappedTypes(ClientConfig.class)
public class ClientConfigTypeHandler extends BaseTypeHandler<ClientConfig> {

    private Gson gson = new Gson();

    @Override
    public void setNonNullParameter(PreparedStatement ps, int i, ClientConfig parameter, JdbcType jdbcType) throws SQLException {
        ps.setObject(i, gson.toJson(parameter));
    }

    @Override
    public ClientConfig getNullableResult(ResultSet rs, String columnName) throws SQLException {
        return gson.fromJson(rs.getString(columnName), ClientConfig.class);
    }

    @Override
    public ClientConfig getNullableResult(ResultSet rs, int columnIndex) throws SQLException {
        return gson.fromJson(rs.getString(columnIndex), ClientConfig.class);
    }

    @Override
    public ClientConfig getNullableResult(CallableStatement cs, int columnIndex) throws SQLException {
        return gson.fromJson(cs.getString(columnIndex), ClientConfig.class);
    }
}
