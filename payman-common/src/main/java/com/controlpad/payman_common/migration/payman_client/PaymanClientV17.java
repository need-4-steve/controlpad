package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV17 implements Migration {


    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            statement.execute("CREATE TABLE merchants(" +
                    "`id` VARCHAR(255) NOT NULL PRIMARY KEY," +
                    " `email` VARCHAR(255)," +
                    " `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP," +
                    " `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(17, 'payman_client')");
        }
    }
}
