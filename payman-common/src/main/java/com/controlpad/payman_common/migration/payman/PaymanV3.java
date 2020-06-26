package com.controlpad.payman_common.migration.payman;


import com.controlpad.payman_common.datasource.SqlConfig;
import com.controlpad.payman_common.migration.DatabaseType;
import com.controlpad.payman_common.migration.Migration;
import com.controlpad.payman_common.util.EncryptUtil;
import com.controlpad.payman_common.util.GsonUtil;

import java.nio.charset.Charset;
import java.sql.*;
import java.util.HashMap;
import java.util.Map;

public class PaymanV3 implements Migration {

    private static final byte[] key = "99#&vhIERxb>p091".getBytes(Charset.forName("UTF8"));

    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement();
             PreparedStatement preparedStatement = connection.prepareStatement("UPDATE clients SET sql_config = ? WHERE id = ?")) {

            // Bumping sql_config allowed size
            statement.execute("ALTER TABLE clients CHANGE COLUMN sql_config sql_config varbinary(512) NOT NULL");
            // Get clients
            ResultSet resultSet = statement.executeQuery("SELECT id, sql_config FROM clients");
            Map<String, SqlConfig> sqlConfigMap = new HashMap<>();
            SqlConfig sqlConfig;
            while (resultSet.next()) {
                sqlConfig = getDecryptedSqlConfig(resultSet.getBytes(2));
                sqlConfig.setDbType(DatabaseType.PAYMAN_CLIENT.getSlug());
                sqlConfigMap.put(resultSet.getString(1), sqlConfig);
            }

            for (Map.Entry<String, SqlConfig> stringSqlConfigEntry : sqlConfigMap.entrySet()) {
                preparedStatement.setBytes(1, getEncryptedSqlConfig(stringSqlConfigEntry.getValue()));
                preparedStatement.setString(2, stringSqlConfigEntry.getKey());
                preparedStatement.execute();
            }

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(3, 'payman')");
        }
    }

    private byte[] getEncryptedSqlConfig(SqlConfig sqlConfig) {
        return EncryptUtil.getInstance().encryptString(
                key,
                GsonUtil.getGson().toJson(sqlConfig)
        );
    }

    private SqlConfig getDecryptedSqlConfig(byte[] bytes) {
        return GsonUtil.getGson().fromJson(
                EncryptUtil.getInstance().decryptString(key, bytes),
                SqlConfig.class
        );
    }
}
