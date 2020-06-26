package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV21 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            statement.execute("ALTER TABLE transaction_batches" +
                    " ADD COLUMN `tax_payments` decimal(24,2) DEFAULT NULL");

            // TODO calculate tax payment amount and append to transaction_batches
            statement.execute("UPDATE transaction_batches AS tb" +
                    " JOIN (SELECT batch_id, SUM(amount) AS total FROM transactions" +
                    " WHERE transaction_type_id IN (60,63) AND status_code = 'S'" +
                    " GROUP BY batch_id) AS t" +
                    " ON tb.id = t.batch_id" +
                    " SET tb.tax_payments = t.total WHERE tb.status = 3");

            statement.execute("UPDATE transaction_batches SET tax_payments = 0.00 WHERE status = 3 AND tax_payments IS NULL");

            statement.execute("ALTER TABLE merchants ADD COLUMN type VARCHAR(32) NOT NULL");

            statement.execute("UPDATE merchants SET type = 'rep'"); // Initial merchant records are rep, we will be adding in company after

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(21, 'payman_client')");
        }
    }
}
