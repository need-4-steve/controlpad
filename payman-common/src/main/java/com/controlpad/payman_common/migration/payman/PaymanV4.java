package com.controlpad.payman_common.migration.payman;


import com.controlpad.payman_common.datasource.SqlConfig;
import com.controlpad.payman_common.datasource.SqlConfigTypeHandler;
import com.controlpad.payman_common.migration.Migration;
import com.controlpad.payman_common.util.GsonUtil;

import java.sql.*;
import java.util.HashMap;
import java.util.Map;

public class PaymanV4 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        // Switching sql_config to read and write databases and no longer encrypting login info
        try (Statement statement = connection.createStatement();
             PreparedStatement preparedStatement = connection.prepareStatement("UPDATE clients SET sql_config_write = ? WHERE id = ?")) {

            SqlConfigTypeHandler sqlConfigTypeHandler = new SqlConfigTypeHandler();
            Map<String, SqlConfig> clientSqlConfigMap = new HashMap<>();

            statement.execute("ALTER TABLE clients ADD COLUMN sql_config_write text NOT NULL");
            statement.execute("ALTER TABLE clients ADD COLUMN sql_config_read text DEFAULT NULL");

            ResultSet resultSet = statement.executeQuery("SELECT id, sql_config FROM clients");
            while(resultSet.next()) {
                clientSqlConfigMap.put(
                        resultSet.getString(1),
                        sqlConfigTypeHandler.getNullableResult(resultSet, 2));
            }

            for (Map.Entry<String, SqlConfig> stringSqlConfigEntry : clientSqlConfigMap.entrySet()) {
                preparedStatement.setString(1, GsonUtil.getGson().toJson(stringSqlConfigEntry.getValue()));
                preparedStatement.setString(2, stringSqlConfigEntry.getKey());
                preparedStatement.execute();
            }

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(4, 'payman')");
        }
    }
}
