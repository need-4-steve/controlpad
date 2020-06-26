package com.controlpad.payman_common.migration.payman;


import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanV2 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try(Statement statement = connection.createStatement()) {
            statement.execute("CREATE TABLE `migrations` (" +
                    "  `version` bigint(20) NOT NULL," +
                    "  `db_type` varchar(32) DEFAULT NULL," +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP," +
                    "                    PRIMARY KEY (`version`)" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
            statement.execute("INSERT INTO migrations(version) VALUES(2)");
        }
    }
}