package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV25 implements Migration {
    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            // Change emvio to nexio
            statement.execute("UPDATE gateway_connection_type SET name = 'Nexio', slug = 'nexiopay' WHERE id = 11");
            statement.execute("ALTER TABLE transactions ADD COLUMN payment_id VARCHAR(255)");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(25, 'payman_client')");
        }
    }
}
