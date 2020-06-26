package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV19 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            statement.execute("ALTER TABLE payment_providers ADD COLUMN subdomain VARCHAR(255) DEFAULT NULL");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(19, 'payman_client')");
        }
    }

}
