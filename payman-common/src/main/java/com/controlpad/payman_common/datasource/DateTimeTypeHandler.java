package com.controlpad.payman_common.datasource;

import org.apache.ibatis.type.BaseTypeHandler;
import org.apache.ibatis.type.JdbcType;
import org.apache.ibatis.type.MappedTypes;
import org.joda.time.DateTime;

import java.sql.*;

@MappedTypes(DateTime.class)
public class DateTimeTypeHandler extends BaseTypeHandler<DateTime> {

    @Override
    public void setNonNullParameter(PreparedStatement ps, int i, DateTime dateTime, JdbcType jdbcType) throws SQLException {
        ps.setDate(i, new Date(dateTime.getMillis()));
    }

    @Override
    public DateTime getNullableResult(ResultSet rs, String columnName) throws SQLException {
        Timestamp timestamp = rs.getTimestamp(columnName);
        if (timestamp != null) {
            return new DateTime(timestamp.getTime());
        } else {
            return null;
        }
    }

    @Override
    public DateTime getNullableResult(ResultSet rs, int columnIndex) throws SQLException {
        Timestamp timestamp = rs.getTimestamp(columnIndex);
        if (timestamp != null) {
            return new DateTime(timestamp.getTime());
        } else {
            return null;
        }
    }

    @Override
    public DateTime getNullableResult(CallableStatement cs, int columnIndex) throws SQLException {
        Timestamp timestamp = cs.getTimestamp(columnIndex);
        if (timestamp != null) {
            return new DateTime(timestamp.getTime());
        } else {
            return null;
        }
    }
}