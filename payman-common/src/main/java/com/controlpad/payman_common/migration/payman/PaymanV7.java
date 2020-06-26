package com.controlpad.payman_common.migration.payman;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanV7 implements Migration {
    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            statement.execute("ALTER TABLE `clients` ADD COLUMN `org_id` VARCHAR(255) UNIQUE AFTER `id`");
            statement.execute("ALTER TABLE `clients` ADD COLUMN `jwt_key` VARCHAR(255) AFTER `sql_config_read`");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(7, 'payman')");
        }
    }
}
