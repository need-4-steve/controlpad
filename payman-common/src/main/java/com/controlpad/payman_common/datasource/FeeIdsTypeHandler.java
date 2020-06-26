package com.controlpad.payman_common.datasource;

import com.controlpad.payman_common.fee.FeeIds;
import com.google.gson.Gson;
import org.apache.ibatis.type.BaseTypeHandler;
import org.apache.ibatis.type.JdbcType;
import org.apache.ibatis.type.MappedTypes;

import java.sql.CallableStatement;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

@MappedTypes(FeeIds.class)
public class FeeIdsTypeHandler extends BaseTypeHandler<FeeIds> {

    private Gson gson = new Gson();

    @Override
    public void setNonNullParameter(PreparedStatement ps, int i, FeeIds parameter, JdbcType jdbcType) throws SQLException {
        ps.setObject(i, gson.toJson(parameter));
    }

    @Override
    public FeeIds getNullableResult(ResultSet rs, String columnName) throws SQLException {
        return gson.fromJson(rs.getString(columnName), FeeIds.class);
    }

    @Override
    public FeeIds getNullableResult(ResultSet rs, int columnIndex) throws SQLException {
        return gson.fromJson(rs.getString(columnIndex), FeeIds.class);
    }

    @Override
    public FeeIds getNullableResult(CallableStatement cs, int columnIndex) throws SQLException {
        return gson.fromJson(cs.getString(columnIndex), FeeIds.class);
    }
}