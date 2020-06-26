package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV26 implements Migration {
    @Override
    public void migrate(Connection connection) throws SQLException {
        // Add user_account column to user_account_validation to track accounts that were submitted
        try (Statement statement = connection.createStatement()) {
            statement.execute("ALTER TABLE user_account_validation ADD COLUMN user_account TEXT");
            statement.execute("INSERT INTO migrations(version, db_type) VALUES(26, 'payman_client')");
        }
    }
}
