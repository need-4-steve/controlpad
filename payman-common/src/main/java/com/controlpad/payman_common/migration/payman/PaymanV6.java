package com.controlpad.payman_common.migration.payman;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanV6 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {

            statement.execute("DROP FUNCTION IF EXISTS `custom_id`");
            statement.execute("DROP FUNCTION IF EXISTS `last_insert_custom_id`");
            statement.execute("DROP FUNCTION IF EXISTS `base36_char`");
            statement.execute("DROP FUNCTION IF EXISTS `base36_random`");
            statement.execute("DROP FUNCTION IF EXISTS `base36_unix_time`");
            statement.execute("DROP FUNCTION IF EXISTS `base36_index_truncate`");

            statement.execute("ALTER TABLE users DROP COLUMN `position`");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(6, 'payman')");
        }
    }
}
