package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV24 implements Migration {
    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {

            statement.execute("INSERT INTO transaction_type(id, name, slug) VALUES(92, 'Tax Payment Refund', 'tax-payment-refund')");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(24, 'payman_client')");
        }
    }
}
