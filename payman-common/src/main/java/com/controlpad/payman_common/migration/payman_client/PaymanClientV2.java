package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV2 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            statement.execute("CREATE TABLE `migrations` (\n" +
                    "  `version` bigint(20) NOT NULL,\n" +
                    "  `db_type` varchar(32) DEFAULT NULL,\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  PRIMARY KEY (`version`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("INSERT INTO migrations(version) VALUES(2)");
        }
    }
}
