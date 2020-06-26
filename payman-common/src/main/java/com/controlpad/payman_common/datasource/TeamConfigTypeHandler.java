package com.controlpad.payman_common.datasource;

import com.controlpad.payman_common.team.TeamConfig;
import com.google.gson.Gson;
import org.apache.ibatis.type.BaseTypeHandler;
import org.apache.ibatis.type.JdbcType;
import org.apache.ibatis.type.MappedTypes;

import java.sql.CallableStatement;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

@MappedTypes(TeamConfig.class)
public class TeamConfigTypeHandler extends BaseTypeHandler<TeamConfig> {

    private Gson gson = new Gson();

    @Override
    public void setNonNullParameter(PreparedStatement ps, int i, TeamConfig parameter, JdbcType jdbcType) throws SQLException {
        ps.setObject(i, gson.toJson(parameter));
    }

    @Override
    public TeamConfig getNullableResult(ResultSet rs, String columnName) throws SQLException {
        return gson.fromJson(rs.getString(columnName), TeamConfig.class);
    }

    @Override
    public TeamConfig getNullableResult(ResultSet rs, int columnIndex) throws SQLException {
        return gson.fromJson(rs.getString(columnIndex), TeamConfig.class);
    }

    @Override
    public TeamConfig getNullableResult(CallableStatement cs, int columnIndex) throws SQLException {
        return gson.fromJson(cs.getString(columnIndex), TeamConfig.class);
    }
}
