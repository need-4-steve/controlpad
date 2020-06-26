package com.controlpad.payman_common.datasource;


import com.controlpad.payman_common.common.Money;
import org.apache.ibatis.type.BaseTypeHandler;
import org.apache.ibatis.type.JdbcType;
import org.apache.ibatis.type.MappedTypes;

import java.sql.CallableStatement;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

@MappedTypes(Money.class)
public class MoneyTypeHandler extends BaseTypeHandler<Money> {

    @Override
    public void setNonNullParameter(PreparedStatement ps, int i, Money parameter, JdbcType jdbcType) throws SQLException {
        ps.setObject(i, parameter.doubleValue());
    }

    @Override
    public Money getNullableResult(ResultSet rs, String columnName) throws SQLException {
        return new Money(rs.getDouble(columnName));
    }

    @Override
    public Money getNullableResult(ResultSet rs, int columnIndex) throws SQLException {
        return new Money(rs.getDouble(columnIndex));
    }

    @Override
    public Money getNullableResult(CallableStatement cs, int columnIndex) throws SQLException {
        return new Money(cs.getDouble(columnIndex));
    }
}
