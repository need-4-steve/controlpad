package com.controlpad.payman_common.datasource;

import com.controlpad.payman_common.fee.FeeIds;
import com.controlpad.payman_common.fee.TeamFeeSet;
import com.controlpad.payman_common.fee.TeamFeeSetMap;
import com.google.gson.Gson;
import org.apache.ibatis.type.BaseTypeHandler;
import org.apache.ibatis.type.JdbcType;
import org.apache.ibatis.type.MappedTypes;

import java.sql.CallableStatement;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.HashMap;

@MappedTypes(TeamFeeSetMap.class)
public class TeamFeeSetMapTypeHandler extends BaseTypeHandler<TeamFeeSetMap> {

    private Gson gson = new Gson();

    @Override
    public void setNonNullParameter(PreparedStatement ps, int i, TeamFeeSetMap parameter, JdbcType jdbcType) throws SQLException {

    }

    @Override
    public TeamFeeSetMap getNullableResult(ResultSet rs, String columnName) throws SQLException {
        return getFromResultSet(rs);
    }

    @Override
    public TeamFeeSetMap getNullableResult(ResultSet rs, int columnIndex) throws SQLException {
        return getFromResultSet(rs);
    }

    @Override
    public TeamFeeSetMap getNullableResult(CallableStatement cs, int columnIndex) throws SQLException {
        return null;
    }

    private TeamFeeSetMap getFromResultSet(ResultSet rs) throws SQLException {
        TeamFeeSetMap teamFeeSetMap = new TeamFeeSetMap();
        HashMap<String, TeamFeeSet> feeSetMap;
        String teamId;
        String transactionType;
        if (rs.first()) {
            do {
                teamId = rs.getString("team_id");
                transactionType = rs.getString("transaction_type");
                feeSetMap = teamFeeSetMap.get(teamId);
                if (feeSetMap == null) {
                    feeSetMap = new HashMap<>();
                    teamFeeSetMap.put(teamId, feeSetMap);
                }
                TeamFeeSet teamFeeSet = new TeamFeeSet(teamId, transactionType, rs.getString("description"), gson.fromJson(rs.getString("fee_ids"), FeeIds.class));
                feeSetMap.put(transactionType, teamFeeSet);
            } while(rs.next());
        }
        return teamFeeSetMap;
    }
}