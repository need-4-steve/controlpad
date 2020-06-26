package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV18 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            statement.execute("ALTER TABLE transaction_batches" +
                    " ADD COLUMN gateway_net_amount DECIMAL(24,2) DEFAULT NULL," +
                    " ADD COLUMN gateway_transaction_count BIGINT DEFAULT NULL," +
                    " ADD COLUMN transaction_count BIGINT DEFAULT NULL," +
                    " ADD COLUMN sales DECIMAL(24,2) DEFAULT NULL," +
                    " ADD COLUMN subscriptions DECIMAL(24,2) DEFAULT NULL," +
                    " ADD COLUMN shipping DECIMAL(24,2) DEFAULT NULL," +
                    " ADD COLUMN refunds DECIMAL(24,2) DEFAULT NULL," +
                    " ADD COLUMN voids DECIMAL(24,2) DEFAULT NULL");

            // Bump batch status down so that it will check status and add stats
            statement.execute("UPDATE transaction_batches SET status = 2 WHERE status = 3");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(18, 'payman_client')");
        }
    }
}
