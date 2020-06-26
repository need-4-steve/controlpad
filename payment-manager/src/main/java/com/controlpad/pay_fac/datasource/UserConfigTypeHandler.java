package com.controlpad.pay_fac.datasource;

import com.controlpad.payman_common.payman_user.Privilege;
import com.google.gson.Gson;
import org.apache.ibatis.type.BaseTypeHandler;
import org.apache.ibatis.type.JdbcType;
import org.apache.ibatis.type.MappedTypes;

import java.sql.CallableStatement;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

@MappedTypes(Privilege.class)
public class UserConfigTypeHandler extends BaseTypeHandler<Privilege> {

    private Gson gson = new Gson();

    @Override
    public void setNonNullParameter(PreparedStatement preparedStatement, int i, Privilege privilege, JdbcType jdbcType) throws SQLException {
        preparedStatement.setObject(i, gson.toJson(privilege));
    }

    @Override
    public Privilege getNullableResult(ResultSet resultSet, String columnName) throws SQLException {
        return gson.fromJson(resultSet.getString(columnName), Privilege.class);
    }

    @Override
    public Privilege getNullableResult(ResultSet resultSet, int columnIndex) throws SQLException {
        return gson.fromJson(resultSet.getString(columnIndex), Privilege.class);
    }

    @Override
    public Privilege getNullableResult(CallableStatement callableStatement, int columnIndex) throws SQLException {
        return gson.fromJson(callableStatement.getString(columnIndex), Privilege.class);
    }
}
