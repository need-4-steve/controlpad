package com.controlpad.payman_common.migration.payman_client;


import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV9 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        // Allow affiliate charges to be negative
        try (Statement statement = connection.createStatement()) {
            statement.execute("ALTER TABLE affiliate_charges CHANGE COLUMN amount amount DECIMAL(24,5) NOT NULL");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(9, 'payman_client')");
        }
    }
}