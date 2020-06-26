package com.controlpad.payman_common.datasource;

import com.controlpad.payman_common.team.PayoutSchedule;
import com.google.gson.Gson;
import org.apache.ibatis.type.BaseTypeHandler;
import org.apache.ibatis.type.JdbcType;
import org.apache.ibatis.type.MappedTypes;

import java.sql.CallableStatement;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

@MappedTypes(PayoutSchedule.class)
public class PayoutScheduleTypeHandler extends BaseTypeHandler<PayoutSchedule> {

    private Gson gson = new Gson();

    @Override
    public void setNonNullParameter(PreparedStatement ps, int i, PayoutSchedule parameter, JdbcType jdbcType) throws SQLException {
        ps.setObject(i, gson.toJson(parameter));
    }

    @Override
    public PayoutSchedule getNullableResult(ResultSet rs, String columnName) throws SQLException {
        return gson.fromJson(rs.getString(columnName), PayoutSchedule.class);
    }

    @Override
    public PayoutSchedule getNullableResult(ResultSet rs, int columnIndex) throws SQLException {
        return gson.fromJson(rs.getString(columnIndex), PayoutSchedule.class);
    }

    @Override
    public PayoutSchedule getNullableResult(CallableStatement cs, int columnIndex) throws SQLException {
        return gson.fromJson(cs.getString(columnIndex), PayoutSchedule.class);
    }
}
